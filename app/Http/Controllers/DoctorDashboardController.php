<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $doctorId = $request->user()->id; 

        $doctor = \App\Models\Doctor::where('user_id', $doctorId)->first();
        $docId = $doctor ? $doctor->doctor_id : null;

        $queryBase = Appointment::whereDate('schedule_date', $today);
        if ($docId) {
            $queryBase->where('doctor_id', $docId);
        }

        $totalPasien = (clone $queryBase)->whereNotIn('status', ['Batal'])->count();
        $antreanMenunggu = (clone $queryBase)->whereIn('status', ['Menunggu', 'Disetujui', 'Dalam Periksa'])->count();
        $selesaiDiperiksa = (clone $queryBase)->where('status', 'Selesai')->count();

        $surgeriesQuery = (clone $queryBase)->whereHas('service', function($q) {
                $q->where('name', 'like', '%Operasi%')
                  ->orWhere('name', 'like', '%Steril%');
            });
        
        $tindakanOperasi = $surgeriesQuery->count();

        $surgerySchedule = $surgeriesQuery->with(['pet.owner', 'service'])->get()->map(function($appt) {
            return [
                'petName' => $appt->pet->name ?? '-',
                'species' => $appt->pet->species ?? '-',
                'owner' => $appt->pet->owner->name ?? '-',
                'procedure' => $appt->service->name ?? '-',
                'time' => substr($appt->schedule_time, 0, 5) . ' WIB',
                'status' => $appt->status,
                'statusColor' => $appt->status === 'Selesai' ? 'text-emerald-600 bg-emerald-50 border-emerald-200' : 'text-amber-600 bg-amber-50 border-amber-200'
            ];
        });

        $topDiagnoses = MedicalRecord::select('diagnosis', DB::raw('count(*) as count'))
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->groupBy('diagnosis')
            ->orderByDesc('count')
            ->take(4)
            ->get()
            ->map(function($record, $index) {
                $colors = ['bg-blue-600', 'bg-emerald-500', 'bg-orange-500', 'bg-rose-500'];
                return [
                    'label' => collect(explode(',', $record->diagnosis))->first() ?: 'Unknown',
                    'count' => $record->count,
                    'color' => $colors[$index % 4]
                ];
            });
        
        $totalDiag = $topDiagnoses->sum('count') ?: 1;
        $topDiagnoses = $topDiagnoses->map(function($item) use ($totalDiag) {
            $item['percentage'] = round(($item['count'] / $totalDiag) * 100);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'totalPasien' => $totalPasien,
                    'antreanMenunggu' => $antreanMenunggu,
                    'selesaiDiperiksa' => $selesaiDiperiksa,
                    'tindakanOperasi' => $tindakanOperasi
                ],
                'surgerySchedule' => $surgerySchedule,
                'topDiagnoses' => $topDiagnoses
            ]
        ]);
    }
}
