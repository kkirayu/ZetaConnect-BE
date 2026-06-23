<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiagnosisDictionaryRequest;
use App\Http\Requests\UpdateDiagnosisDictionaryRequest;
use App\Http\Resources\DiagnosisDictionaryResource;
use App\Services\DiagnosisDictionaryService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class DiagnosisDictionaryController extends Controller
{
    public function __construct(private DiagnosisDictionaryService $service)
    {
    }

    public function index(): JsonResponse
    {
        $diagnoses = $this->service->getAll();
        
        return response()->json([
            'success' => true,
            'message' => 'Daftar diagnosis berhasil diambil',
            'data' => DiagnosisDictionaryResource::collection($diagnoses),
            'pagination' => [
                'current_page' => $diagnoses->currentPage(),
                'per_page' => $diagnoses->perPage(),
                'total' => $diagnoses->total(),
                'last_page' => $diagnoses->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/diagnoses',
        tags: ['Diagnosis'],
        security: [['bearerAuth' => []]],
        summary: 'Create diagnosis',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['disease_name'],
                properties: [
                    new OA\Property(property: 'disease_name', type: 'string', example: 'Parvovirus'),
                    new OA\Property(property: 'description', type: 'string', example: 'Infeksi virus yang menyerang saluran pencernaan'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreDiagnosisDictionaryRequest $request): JsonResponse
    {
        $diagnosis = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis berhasil dibuat',
            'data' => new DiagnosisDictionaryResource($diagnosis),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $diagnosis = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail diagnosis berhasil diambil',
            'data' => new DiagnosisDictionaryResource($diagnosis),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/diagnoses/{id}',
        tags: ['Diagnosis'],
        security: [['bearerAuth' => []]],
        summary: 'Update diagnosis',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'disease_name', type: 'string', example: 'Parvovirus'),
                    new OA\Property(property: 'description', type: 'string', example: 'Infeksi virus yang menyerang saluran pencernaan'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateDiagnosisDictionaryRequest $request, int $id): JsonResponse
    {
        $diagnosis = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis berhasil diperbarui',
            'data' => new DiagnosisDictionaryResource($diagnosis),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis berhasil dihapus',
        ], 200);
    }
}
