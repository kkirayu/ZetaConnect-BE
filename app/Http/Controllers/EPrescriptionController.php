<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEPrescriptionRequest;
use App\Http\Requests\UpdateEPrescriptionRequest;
use App\Http\Resources\EPrescriptionResource;
use App\Services\EPrescriptionService;
use Illuminate\Http\JsonResponse;

class EPrescriptionController extends Controller
{
    public function __construct(private EPrescriptionService $service)
    {
    }

    public function index(): JsonResponse
    {
        $prescriptions = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar e-resep berhasil diambil',
            'data' => EPrescriptionResource::collection($prescriptions),
            'pagination' => [
                'current_page' => $prescriptions->currentPage(),
                'per_page' => $prescriptions->perPage(),
                'total' => $prescriptions->total(),
                'last_page' => $prescriptions->lastPage(),
            ],
        ], 200);
    }

    public function store(StoreEPrescriptionRequest $request): JsonResponse
    {
        $prescription = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'E-resep berhasil dibuat',
            'data' => new EPrescriptionResource($prescription),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $prescription = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail e-resep berhasil diambil',
            'data' => new EPrescriptionResource($prescription),
        ], 200);
    }

    public function update(UpdateEPrescriptionRequest $request, int $id): JsonResponse
    {
        $prescription = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'E-resep berhasil diperbarui',
            'data' => new EPrescriptionResource($prescription),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'E-resep berhasil dihapus',
        ], 200);
    }
}
