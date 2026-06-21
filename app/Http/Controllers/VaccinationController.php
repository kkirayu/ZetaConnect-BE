<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreVaccinationRequest;
use App\Http\Requests\UpdateVaccinationRequest;
use App\Http\Resources\VaccinationResource;
use App\Services\VaccinationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class VaccinationController extends Controller
{
    public function __construct(private VaccinationService $service)
    {
    }

    public function index(): JsonResponse
    {
        $vaccinations = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar vaksin berhasil diambil',
            'data' => VaccinationResource::collection($vaccinations),
            'pagination' => [
                'current_page' => $vaccinations->currentPage(),
                'per_page' => $vaccinations->perPage(),
                'total' => $vaccinations->total(),
                'last_page' => $vaccinations->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/vaccinations',
        tags: ['Vaccination'],
        security: [['bearerAuth' => []]],
        summary: 'Create vaccination record',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pet_id', 'vaccine_name'],
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'vaccine_name', type: 'string', example: 'Rabies'),
                    new OA\Property(property: 'batch_number', type: 'string', example: 'RB-2026-001'),
                    new OA\Property(property: 'next_due_date', type: 'string', format: 'date', example: '2026-12-12'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreVaccinationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['doctor_id'] = Auth::id();

        $vaccination = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Vaksin berhasil dibuat',
            'data' => new VaccinationResource($vaccination),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $vaccination = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail vaksin berhasil diambil',
            'data' => new VaccinationResource($vaccination),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/vaccinations/{id}',
        tags: ['Vaccination'],
        security: [['bearerAuth' => []]],
        summary: 'Update vaccination record',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'vaccine_name', type: 'string', example: 'Rabies'),
                    new OA\Property(property: 'batch_number', type: 'string', example: 'RB-2026-001'),
                    new OA\Property(property: 'next_due_date', type: 'string', format: 'date', example: '2026-12-12'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateVaccinationRequest $request, int $id): JsonResponse
    {
        $vaccination = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Vaksin berhasil diperbarui',
            'data' => new VaccinationResource($vaccination),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Vaksin berhasil dihapus',
        ], 200);
    }
}
