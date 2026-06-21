<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurgeryRequest;
use App\Http\Requests\UpdateSurgeryRequest;
use App\Http\Resources\SurgeryResource;
use App\Services\SurgeryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class SurgeryController extends Controller
{
    public function __construct(private SurgeryService $service)
    {
    }

    public function index(): JsonResponse
    {
        $surgeries = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar operasi berhasil diambil',
            'data' => SurgeryResource::collection($surgeries),
            'pagination' => [
                'current_page' => $surgeries->currentPage(),
                'per_page' => $surgeries->perPage(),
                'total' => $surgeries->total(),
                'last_page' => $surgeries->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/surgeries',
        tags: ['Surgery'],
        security: [['bearerAuth' => []]],
        summary: 'Create surgery report',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pet_id', 'surgery_type'],
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'surgery_type', type: 'string', example: 'Sterilisasi'),
                    new OA\Property(property: 'anesthesia_notes', type: 'string', example: 'Anestesi inhalasi'),
                    new OA\Property(property: 'post_op_instructions', type: 'string', example: 'Istirahat 7 hari dan kontrol ulang'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreSurgeryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['doctor_id'] = Auth::id();

        $surgery = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Operasi berhasil dibuat',
            'data' => new SurgeryResource($surgery),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $surgery = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail operasi berhasil diambil',
            'data' => new SurgeryResource($surgery),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/surgeries/{id}',
        tags: ['Surgery'],
        security: [['bearerAuth' => []]],
        summary: 'Update surgery report',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'surgery_type', type: 'string', example: 'Sterilisasi'),
                    new OA\Property(property: 'anesthesia_notes', type: 'string', example: 'Anestesi inhalasi'),
                    new OA\Property(property: 'post_op_instructions', type: 'string', example: 'Istirahat 7 hari dan kontrol ulang'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateSurgeryRequest $request, int $id): JsonResponse
    {
        $surgery = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Operasi berhasil diperbarui',
            'data' => new SurgeryResource($surgery),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Operasi berhasil dihapus',
        ], 200);
    }
}
