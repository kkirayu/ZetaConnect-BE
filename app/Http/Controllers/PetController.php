<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    /**
     * Tampilkan daftar hewan peliharaan (bisa difilter & dipaginasi)
     */
    public function index(Request $request)
    {
        $query = Pet::query();

        // Pencarian berdasarkan nama hewan
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan owner
        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        // Filter berdasarkan spesies
        if ($request->has('species')) {
            $query->where('species', $request->species);
        }

        $pets = $query->with('owner')->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar hewan peliharaan berhasil diambil',
            'data'    => $pets
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
     * Simpan hewan peliharaan baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'owner_id'           => 'required|exists:users,id',
            'name'               => 'required|string|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'species'            => 'required|in:Kucing,Anjing,Burung,Lainnya',
            'breed'              => 'nullable|string|max:255',
            'gender'             => 'required|in:Jantan,Betina',
            'dob'                => 'nullable|date',
            'color'              => 'nullable|string|max:255',
            'distinctive_traits' => 'nullable|string',
            'allergies'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('photo')) {
            $data['photo_url'] = $request->file('photo')->storeOnCloudinary('zetaconnect/pets')->getSecurePath();
        }

        $pet = Pet::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Hewan peliharaan berhasil ditambahkan',
            'data'    => $pet
        ], 201);
    }

    /**
     * Detail hewan peliharaan tunggal
     */
    public function show($id)
    {
        $pet = Pet::with('owner')->find($id);

        if (!$pet) {
            return response()->json(['message' => 'Hewan peliharaan tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $pet
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pet $pet)
    {
        //
    }

    /**
     * Update data hewan peliharaan
     */
    public function update(Request $request, $id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Hewan peliharaan tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'owner_id'           => 'sometimes|exists:users,id',
            'name'               => 'sometimes|string|max:255',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'species'            => 'sometimes|in:Kucing,Anjing,Burung,Lainnya',
            'breed'              => 'nullable|string|max:255',
            'gender'             => 'sometimes|in:Jantan,Betina',
            'dob'                => 'nullable|date',
            'color'              => 'nullable|string|max:255',
            'distinctive_traits' => 'nullable|string',
            'allergies'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('photo')) {
            // Kita tidak perlu menghapus foto lama secara eksplisit dari Cloudinary
            // karena mengelola file zombie tidak masalah di paket gratis ini,
            // atau bisa menambahkan logika destroy jika perlu.
            $data['photo_url'] = $request->file('photo')->storeOnCloudinary('zetaconnect/pets')->getSecurePath();
        }

        $pet->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Hewan peliharaan berhasil diupdate',
            'data'    => $pet
        ], 200);
    }

    /**
     * Hapus hewan peliharaan
     */
    public function destroy($id)
    {
        $pet = Pet::find($id);
        if (!$pet) return response()->json(['message' => 'Hewan peliharaan tidak ditemukan'], 404);

        $pet->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hewan peliharaan berhasil dihapus'
        ], 200);
    }
}
