<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Invoice;

class OwnerDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Total hewan peliharaan
        $totalPets = Pet::where('owner_id', $userId)->count();

        // 2. Janji temu aktif
        $activeAppointments = Appointment::where('owner_id', $userId)
            ->whereIn('status', ['Menunggu', 'Disetujui', 'Diperiksa'])
            ->count();

        // 3. Riwayat rekam medis
        $totalMedicalRecords = MedicalRecord::whereHas('pet', function($q) use ($userId) {
            $q->where('owner_id', $userId);
        })->count();

        // 4. Tagihan tertunda
        $pendingBillingAmount = Invoice::where('owner_id', $userId)
            ->where('status', 'Unpaid')
            ->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_pets' => $totalPets,
                'active_appointments' => $activeAppointments,
                'total_medical_records' => $totalMedicalRecords,
                'pending_billing_amount' => $pendingBillingAmount
            ]
        ]);
    }
}
