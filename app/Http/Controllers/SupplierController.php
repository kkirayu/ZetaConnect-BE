<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    // GET /api/suppliers
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search')) {
            $query->where('company_name', 'like', '%' . $request->search . '%')
                ->orWhere('sales_name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    // POST /api/suppliers
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'sales_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'image' => 'nullable|image|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supplierData = $request->except('image');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('suppliers', 'cloudinary');
            $supplierData['image_url'] = Storage::disk('cloudinary')->url($path);
            $supplierData['image_public_id'] = $path;
        }

        $supplier = Supplier::create($supplierData);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier
        ], 201);
    }

    // GET /api/suppliers/{id}
    public function show($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $supplier
        ]);
    }

    // PUT /api/suppliers/{id}
    public function update(Request $request, $id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|required|string|max:255',
            'sales_name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20',
            'image' => 'nullable|image|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $supplierData = $request->except('image');

        if ($request->hasFile('image')) {
            if ($supplier->image_public_id) {
                Storage::disk('cloudinary')->delete($supplier->image_public_id);
            }
            $path = $request->file('image')->store('suppliers', 'cloudinary');
            $supplierData['image_url'] = Storage::disk('cloudinary')->url($path);
            $supplierData['image_public_id'] = $path;
        }

        $supplier->update($supplierData);

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diperbarui',
            'data' => $supplier
        ]);
    }

    // DELETE /api/suppliers/{id}
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak ditemukan'
            ], 404);
        }

        if ($supplier->image_public_id) {
            Storage::disk('cloudinary')->delete($supplier->image_public_id);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}
