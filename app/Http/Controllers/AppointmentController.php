<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    /**
     * Tampilkan daftar janji temu.
     */
    public function index(Request $request)
    {
        $query = Appointment::query();

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tanggal
        if ($request->has('date')) {
            $query->whereDate('schedule_date', $request->date);
        }

        // Filter berdasarkan owner
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Include relasi
        $appointments = $query->with(['owner', 'pet', 'service'])->orderBy('schedule_date', 'desc')->orderBy('schedule_time', 'asc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar janji temu berhasil diambil',
            'data'    => $appointments
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Simpan janji temu baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id'          => 'required|exists:users,id',
            'pet_id'            => 'required|exists:pets,id',
            'service_id'        => 'required|exists:services,id',
            'booking_type'      => 'required|in:Online,Walk-in',
            'schedule_date'     => 'required|date',
            'schedule_time'     => 'required|date_format:H:i',
            'initial_complaint' => 'required|string',
            'queue_number'      => 'nullable|string', // Bisa diisi front-end atau di-generate otomatis
            'status'            => 'nullable|in:Menunggu,Disetujui,Dalam Periksa,Selesai,Batal'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        
        // Generate nomor antrean jika tidak ada
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
            'data'    => $appointment->load(['owner', 'pet', 'service'])
        ], 201);
    }

    /**
     * Detail janji temu.
     */
    public function show($id)
    {
        $appointment = Appointment::with(['owner', 'pet', 'service'])->find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Janji temu tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $appointment
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update janji temu.
     */
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
            'data'    => $appointment->fresh()->load(['owner', 'pet', 'service'])
        ], 200);
    }

    /**
     * Hapus janji temu.
     */
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
