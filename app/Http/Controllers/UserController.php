<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function index(Request $request)
        {
            $query = User::query();

            if ($request->has('search')) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            }

            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            $users = $query->latest()->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Daftar user berhasil diambil',
                'data'    => $users
            ], 200);
        }

    public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name'         => 'required|string|max:255',
                'email'        => 'required|string|email|max:255|unique:users',
                'password'     => 'required|string|min:8',
                'phone_number' => 'required|string',
                'role'         => 'required|in:Admin,Dokter,Resepsionis,Apoteker,Kasir,Owner',
                'status'       => 'required|in:Aktif,Tidak Aktif',
                'address'      => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = User::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'password'     => Hash::make($request->password), 
                'phone_number' => $request->phone_number,
                'role'         => $request->role,
                'status'       => $request->status,
                'address'      => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data'    => $user
            ], 201);
        }

    public function show($id)
        {
            $user = User::with('pets')->find($id); 

            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan'], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $user
            ], 200);
        }

    public function update(Request $request, $id)
        {
            $user = User::find($id);
            if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

            $validator = Validator::make($request->all(), [
                'name'         => 'sometimes|string|max:255',
                'email'        => 'sometimes|string|email|unique:users,email,' . $id,
                'phone_number' => 'sometimes|string',
                'role'         => 'sometimes|in:Admin,Dokter,Resepsionis,Apoteker,Kasir,Owner',
                'status'       => 'sometimes|in:Aktif,Tidak Aktif',
                'address'      => 'sometimes|string',
                'photo'        => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }

            $data = $request->except(['password', 'photo']);

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('zetaconnect/users', 'cloudinary');
                $data['photo'] = Storage::disk('cloudinary')->url($path);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'data'    => $user
            ], 200);
        }

    public function destroy($id)
        {
            $user = User::find($id);
            if (!$user) return response()->json(['message' => 'User tidak ditemukan'], 404);

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ], 200);
        }

    public function updateProfile(Request $request)
        {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name'         => 'sometimes|string|max:255',
                'phone_number' => 'sometimes|string',
                'address'      => 'sometimes|string',
                'photo'        => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
                'current_password' => 'sometimes|string',
                'new_password'     => 'sometimes|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('current_password') && $request->has('new_password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password saat ini tidak cocok'
                    ], 400);
                }
                $user->password = Hash::make($request->new_password);
            }

            $data = $request->only(['name', 'phone_number', 'address']);

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('zetaconnect/users', 'cloudinary');
                $data['photo'] = Storage::disk('cloudinary')->url($path);
            }

            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diupdate',
                'data'    => $user
            ], 200);
        }
}

