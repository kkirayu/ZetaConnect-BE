<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    /**
     * Tampilkan daftar layanan.
     */
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->orderBy('name')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar layanan berhasil diambil',
            'data'    => $services,
        ], 200);
    }

    /**
     * Simpan layanan baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255|unique:services,name',
            'category' => 'required|in:Medis,Vaksin,Grooming,Fasilitas',
            'price'    => 'required|numeric|min:0',
            'status'   => 'nullable|in:Tersedia,Tidak Tersedia',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data           = $validator->validated();
        $data['status'] = $data['status'] ?? 'Tersedia';

        $service = Service::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil ditambahkan',
            'data'    => $service,
        ], 201);
    }

    /**
     * Detail layanan.
     */
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $service,
        ], 200);
    }

    /**
     * Update layanan.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'sometimes|string|max:255|unique:services,name,' . $id,
            'category' => 'sometimes|in:Medis,Vaksin,Grooming,Fasilitas',
            'price'    => 'sometimes|numeric|min:0',
            'status'   => 'sometimes|in:Tersedia,Tidak Tersedia',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $service->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil diupdate',
            'data'    => $service->fresh(),
        ], 200);
    }

    /**
     * Hapus layanan.
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak ditemukan',
            ], 404);
        }

        // Cek apakah layanan sedang digunakan di appointment
        if ($service->appointments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Layanan tidak dapat dihapus karena sudah digunakan pada appointment',
            ], 409);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Layanan berhasil dihapus',
        ], 200);
    }
}
