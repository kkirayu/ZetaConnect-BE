<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['user', 'schedules'])->get();
        return response()->json($doctors);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'spesialisasi' => 'required|string|max:255',
            'image' => 'nullable|string',
            'schedules' => 'nullable|array',
            'schedules.*.hari_praktik' => 'required_with:schedules|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'schedules.*.sesi_praktik' => 'required_with:schedules|string|in:Sesi 1,Sesi 2,Sesi 3,Sesi 4,Sesi 5',
        ]);

        $doctor = Doctor::create($request->except('schedules'));

        if ($request->has('schedules')) {
            $doctor->schedules()->createMany($request->schedules);
        }

        return response()->json([
            'message' => 'Doctor created successfully',
            'data' => $doctor->load('schedules')
        ], 201);
    }

    public function show($id)
    {
        $doctor = Doctor::with(['user', 'schedules'])->find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json($doctor);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
            'spesialisasi' => 'sometimes|required|string|max:255',
            'image' => 'nullable|string',
            'schedules' => 'nullable|array',
            'schedules.*.hari_praktik' => 'required_with:schedules|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'schedules.*.sesi_praktik' => 'required_with:schedules|string|in:Sesi 1,Sesi 2,Sesi 3,Sesi 4,Sesi 5',
        ]);

        $doctor->update($request->except('schedules'));

        if ($request->has('schedules')) {
            $doctor->schedules()->delete();
            $doctor->schedules()->createMany($request->schedules);
        }

        return response()->json([
            'message' => 'Doctor updated successfully',
            'data' => $doctor->load('schedules')
        ]);
    }

    public function destroy($id)
    {
        $doctor = Doctor::find($id);

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        $doctor->delete();

        return response()->json([
            'message' => 'Doctor deleted successfully'
        ]);
    }
}
