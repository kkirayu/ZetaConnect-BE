<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEReceiptRequest;
use App\Http\Requests\UpdateEReceiptRequest;
use App\Http\Resources\EReceiptResource;
use App\Services\EReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class EReceiptController extends Controller
{
    public function __construct(private EReceiptService $service)
    {
    }

    public function index(): JsonResponse
    {
        $receipts = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar e-receipt berhasil diambil',
            'data' => EReceiptResource::collection($receipts),
            'pagination' => [
                'current_page' => $receipts->currentPage(),
                'per_page' => $receipts->perPage(),
                'total' => $receipts->total(),
                'last_page' => $receipts->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/e-receipts',
        tags: ['E Receipt'],
        security: [['bearerAuth' => []]],
        summary: 'Create e-receipt with medicines',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pet_id', 'items'],
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'doctor_instructions', type: 'string', example: 'Minum sesudah makan'),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['medicine_name', 'dosage', 'frequency', 'quantity'],
                            properties: [
                                new OA\Property(property: 'medicine_name', type: 'string', example: 'Amoxicillin'),
                                new OA\Property(property: 'dosage', type: 'string', example: '1 kapsul'),
                                new OA\Property(property: 'frequency', type: 'string', example: '2x sehari'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 10),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreEReceiptRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['doctor_id'] = Auth::id();

        $receipt = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'E-receipt berhasil dibuat',
            'data' => new EReceiptResource($receipt),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $receipt = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail e-receipt berhasil diambil',
            'data' => new EReceiptResource($receipt),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/e-receipts/{id}',
        tags: ['E Receipt'],
        security: [['bearerAuth' => []]],
        summary: 'Update e-receipt with medicines',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'doctor_instructions', type: 'string', example: 'Minum sesudah makan'),
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['medicine_name', 'dosage', 'frequency', 'quantity'],
                            properties: [
                                new OA\Property(property: 'medicine_name', type: 'string', example: 'Amoxicillin'),
                                new OA\Property(property: 'dosage', type: 'string', example: '1 kapsul'),
                                new OA\Property(property: 'frequency', type: 'string', example: '2x sehari'),
                                new OA\Property(property: 'quantity', type: 'integer', example: 10),
                            ]
                        )
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateEReceiptRequest $request, int $id): JsonResponse
    {
        $receipt = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'E-receipt berhasil diperbarui',
            'data' => new EReceiptResource($receipt),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'E-receipt berhasil dihapus',
        ], 200);
    }
}
