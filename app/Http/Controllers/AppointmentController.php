<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Appointment::query();


        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

       
        if ($request->has('date')) {
            $query->whereDate('schedule_date', $request->date);
        }

        
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

       
        $appointments = $query->with(['owner', 'pet', 'service', 'doctor'])->orderBy('schedule_date', 'desc')->orderBy('schedule_time', 'asc')->paginate(100);

        return response()->json([
            'success' => true,
            'message' => 'Daftar janji temu berhasil diambil',
            'data'    => $appointments
        ], 200);
    }

    public function getAvailableSessions(Request $request)
    {
        $date = $request->query('date');
        $doctorId = $request->query('doctor_id');
        $serviceId = $request->query('service_id');

        if (!$date) {  
            return response()->json(['message' => 'Date is required'], 400);
        }

        $estimatedSessions = 1;
        if ($serviceId) {
            $service = \App\Models\Service::find($serviceId);
            if ($service) {
                $estimatedSessions = $service->estimated_sessions;
            }
        }

        $dayOfWeek = date('l', strtotime($date));
        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $hariPraktik = $dayMap[$dayOfWeek];

        $sessionTimes = [
            'Sesi 1' => '08:00',
            'Sesi 2' => '09:00',
            'Sesi 3' => '10:00',
            'Sesi 4' => '11:00',
            'Sesi 5' => '12:00',
            'Sesi 6' => '13:00',
            'Sesi 7' => '14:00',
            'Sesi 8' => '15:00',
        ];
        $sessionKeys = array_keys($sessionTimes);

        $scheduledDoctorsQuery = DoctorSchedule::where('hari_praktik', $hariPraktik);
        if ($doctorId) {
            $scheduledDoctorsQuery->where('doctor_id', $doctorId);
        }
        $doctorSchedules = $scheduledDoctorsQuery->get();
        
        $doctorAvailableSessions = [];
        foreach ($doctorSchedules as $ds) {
            $doctorAvailableSessions[$ds->doctor_id][] = $ds->sesi_praktik;
        }

        $appointmentsQuery = Appointment::with('service')->whereDate('schedule_date', $date)->whereNotIn('status', ['Batal']);
        if ($doctorId) {
            $appointmentsQuery->where('doctor_id', $doctorId);
        }
        $appointments = $appointmentsQuery->get();

        $doctorOccupiedSessions = [];
        foreach ($appointments as $appt) {
            $docId = $appt->doctor_id;
            if (!$docId) continue;
            
            $startTime = substr($appt->schedule_time, 0, 5);
            $startSessionIndex = array_search($startTime, array_values($sessionTimes));

            if ($startSessionIndex !== false) {
                $duration = $appt->service ? $appt->service->estimated_sessions : 1;
                for ($i = 0; $i < $duration; $i++) {
                    if (isset($sessionKeys[$startSessionIndex + $i])) {
                        $doctorOccupiedSessions[$docId][] = $sessionKeys[$startSessionIndex + $i];
                    }
                }
            }
        }

        $availableTimes = [];
        foreach ($sessionKeys as $startIndex => $sessionName) {
            $time = $sessionTimes[$sessionName];
            $canAccommodate = false;

            foreach ($doctorAvailableSessions as $docId => $workingSessions) {
                $doctorCanTakeIt = true;
                for ($i = 0; $i < $estimatedSessions; $i++) {
                    if (!isset($sessionKeys[$startIndex + $i])) {
                        $doctorCanTakeIt = false;
                        break;
                    }
                    $checkSession = $sessionKeys[$startIndex + $i];
                    
                    if (!in_array($checkSession, $workingSessions)) {
                        $doctorCanTakeIt = false;
                        break;
                    }

                    if (isset($doctorOccupiedSessions[$docId]) && in_array($checkSession, $doctorOccupiedSessions[$docId])) {
                        $doctorCanTakeIt = false;
                        break;
                    }
                }

                if ($doctorCanTakeIt) {
                    $canAccommodate = true;
                    break;
                }
            }

            if ($canAccommodate) {
                $availableTimes[] = [
                    'session' => $sessionName,
                    'time' => $time,
                    'label' => "$sessionName ($time WIB)"
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $availableTimes
        ]);
    }

  
    public function create()
    {
        
    }

    
    public function store(Request $request)
    {
        $ownerId = $request->input('owner_id');

        $validator = Validator::make($request->all(), [
            'owner_id'          => 'required|exists:users,id',
            'pet_id'            => [
                'required',
                Rule::exists('pets', 'id')->where('owner_id', $ownerId),
            ],
            'service_id'        => 'required|exists:services,id',
            'doctor_id'         => 'nullable|exists:doctors,doctor_id',
            'booking_type'      => 'required|in:Online,Walk-in',
            'schedule_date'     => 'required|date',
            'schedule_time'     => 'required|date_format:H:i',
            'initial_complaint' => 'required|string',
            'queue_number'      => 'nullable|string', 
            'status'            => 'nullable|in:Menunggu,Disetujui,Dalam Periksa,Selesai,Batal'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        
        if (empty($data['doctor_id'])) {
            $dayOfWeek = date('l', strtotime($data['schedule_date']));
            $dayMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $hariPraktik = $dayMap[$dayOfWeek];
            
            $sessionTimes = [
                '08:00' => 'Sesi 1', '09:00' => 'Sesi 2', '10:00' => 'Sesi 3',
                '11:00' => 'Sesi 4', '12:00' => 'Sesi 5', '13:00' => 'Sesi 6',
                '14:00' => 'Sesi 7', '15:00' => 'Sesi 8'
            ];
            $sessionKeys = array_values($sessionTimes);
            
            $timeKey = substr($data['schedule_time'], 0, 5);
            $sessionName = $sessionTimes[$timeKey] ?? null;

            if ($sessionName) {
                $service = \App\Models\Service::find($data['service_id']);
                $estimatedSessions = $service ? $service->estimated_sessions : 1;
                $startIndex = array_search($sessionName, $sessionKeys);

                $doctorSchedules = \App\Models\DoctorSchedule::where('hari_praktik', $hariPraktik)->get();
                $doctorAvailableSessions = [];
                foreach ($doctorSchedules as $ds) {
                    $doctorAvailableSessions[$ds->doctor_id][] = $ds->sesi_praktik;
                }

                $appointments = Appointment::with('service')->whereDate('schedule_date', $data['schedule_date'])->whereNotIn('status', ['Batal'])->get();
                $doctorOccupiedSessions = [];
                foreach ($appointments as $appt) {
                    $docId = $appt->doctor_id;
                    if (!$docId) continue;
                    $startTime = substr($appt->schedule_time, 0, 5);
                    $sName = $sessionTimes[$startTime] ?? null;
                    $sIndex = array_search($sName, $sessionKeys);
                    if ($sIndex !== false) {
                        $dur = $appt->service ? $appt->service->estimated_sessions : 1;
                        for ($i = 0; $i < $dur; $i++) {
                            if (isset($sessionKeys[$sIndex + $i])) {
                                $doctorOccupiedSessions[$docId][] = $sessionKeys[$sIndex + $i];
                            }
                        }
                    }
                }

                $assignedDocId = null;
                foreach ($doctorAvailableSessions as $docId => $workingSessions) {
                    $doctorCanTakeIt = true;
                    for ($i = 0; $i < $estimatedSessions; $i++) {
                        if (!isset($sessionKeys[$startIndex + $i])) {
                            $doctorCanTakeIt = false;
                            break;
                        }
                        $checkSession = $sessionKeys[$startIndex + $i];
                        
                        if (!in_array($checkSession, $workingSessions)) {
                            $doctorCanTakeIt = false;
                            break;
                        }

                        if (isset($doctorOccupiedSessions[$docId]) && in_array($checkSession, $doctorOccupiedSessions[$docId])) {
                            $doctorCanTakeIt = false;
                            break;
                        }
                    }

                    if ($doctorCanTakeIt) {
                        $assignedDocId = $docId;
                        break;
                    }
                }

                if ($assignedDocId) {
                    $data['doctor_id'] = $assignedDocId;
                } else {
                    return response()->json(['message' => 'Tidak ada dokter yang tersedia untuk jadwal ini.'], 422);
                }
            }
        }
        if (empty($data['queue_number'])) {
            $countToday = Appointment::whereDate('schedule_date', $data['schedule_date'])->count() + 1;
            $data['queue_number'] = 'Q-' . date('Ymd', strtotime($data['schedule_date'])) . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);
        }

        if (empty($data['status'])) {
            $data['status'] = 'Menunggu';
        }

        $appointment = Appointment::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Janji temu berhasil dibuat',
            'data'    => $appointment->load(['owner', 'pet', 'service', 'doctor'])
        ], 201);
    }

    
    public function show($id)
    {
        $appointment = Appointment::with(['owner', 'pet', 'service', 'doctor'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $appointment
        ], 200);
    }

   
    public function edit(Appointment $appointment)
    {
        
    }

   
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'owner_id'          => 'sometimes|exists:users,id',
            'pet_id'            => 'sometimes|exists:pets,id',
            'service_id'        => 'sometimes|exists:services,id',
            'doctor_id'         => 'sometimes|nullable|exists:users,id',
            'booking_type'      => 'sometimes|in:Online,Walk-in',
            'schedule_date'     => 'sometimes|date',
            'schedule_time'     => 'sometimes|date_format:H:i',
            'initial_complaint' => 'sometimes|string',
            'queue_number'      => 'nullable|string',
            'status'            => 'sometimes|in:Menunggu,Disetujui,Dalam Periksa,Selesai,Batal'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $appointment->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Janji temu berhasil diupdate',
            'data'    => $appointment->fresh()->load(['owner', 'pet', 'service', 'doctor'])
        ], 200);
    }

    
    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        
        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Janji temu berhasil dihapus'
        ], 200);
    }
}
