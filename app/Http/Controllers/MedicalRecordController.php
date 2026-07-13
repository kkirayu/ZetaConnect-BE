<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Http\Requests\UpdateMedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Services\MedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class MedicalRecordController extends Controller
{
    public function __construct(private MedicalRecordService $service)
    {
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $petId = $request->query('pet_id');
        $petId = $petId !== null ? (int) $petId : null;
        
        $ownerId = $request->query('owner_id');
        $ownerId = $ownerId !== null ? (int) $ownerId : null;
        
        $records = $this->service->getAll(10, $petId, $ownerId);

        return response()->json([
            'success' => true,
            'message' => 'Daftar rekam medis berhasil diambil',
            'data' => MedicalRecordResource::collection($records),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'last_page' => $records->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/medical-records',
        tags: ['Medical Record'],
        security: [['bearerAuth' => []]],
        summary: 'Create SOAP medical record',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pet_id', 'weight', 'subjective', 'objective', 'diagnosis_dictionary_id', 'plan'],
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'weight', type: 'number', format: 'float', example: 4.25),
                    new OA\Property(property: 'subjective', type: 'string', example: 'Nafsu makan menurun'),
                    new OA\Property(property: 'objective', type: 'string', example: 'Suhu tubuh 39.2 C'),
                    new OA\Property(property: 'diagnosis_dictionary_id', type: 'integer', example: 3),
                    new OA\Property(property: 'plan', type: 'string', example: 'Berikan antibiotik 5 hari dan kontrol ulang'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreMedicalRecordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['doctor_id'] = Auth::id();

        $record = $this->service->create($data);

        if (isset($data['appointment_id'])) {
            $appointment = \App\Models\Appointment::find($data['appointment_id']);
            if ($appointment) {
                $appointment->status = 'Selesai';
                $appointment->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil dibuat',
            'data' => new MedicalRecordResource($record),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $record = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail rekam medis berhasil diambil',
            'data' => new MedicalRecordResource($record),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/medical-records/{id}',
        tags: ['Medical Record'],
        security: [['bearerAuth' => []]],
        summary: 'Update SOAP medical record',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'weight', type: 'number', format: 'float', example: 4.25),
                    new OA\Property(property: 'subjective', type: 'string', example: 'Nafsu makan menurun'),
                    new OA\Property(property: 'objective', type: 'string', example: 'Suhu tubuh 39.2 C'),
                    new OA\Property(property: 'diagnosis_dictionary_id', type: 'integer', example: 3),
                    new OA\Property(property: 'plan', type: 'string', example: 'Berikan antibiotik 5 hari dan kontrol ulang'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateMedicalRecordRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil diperbarui',
            'data' => new MedicalRecordResource($record),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Rekam medis berhasil dihapus',
        ], 200);
    }
}
