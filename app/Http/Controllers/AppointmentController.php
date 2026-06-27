<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
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

       
        $appointments = $query->with(['owner', 'pet', 'service'])->orderBy('schedule_date', 'desc')->orderBy('schedule_time', 'asc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar janji temu berhasil diambil',
            'data'    => $appointments
        ], 200);
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
            'doctor_id'         => 'nullable|exists:users,id',
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
