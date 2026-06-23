<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalCertificateRequest;
use App\Http\Requests\UpdateMedicalCertificateRequest;
use App\Http\Resources\MedicalCertificateResource;
use App\Services\MedicalCertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class MedicalCertificateController extends Controller
{
    public function __construct(private MedicalCertificateService $service)
    {
    }

    public function index(): JsonResponse
    {
        $certificates = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar surat keterangan medis berhasil diambil',
            'data' => MedicalCertificateResource::collection($certificates),
            'pagination' => [
                'current_page' => $certificates->currentPage(),
                'per_page' => $certificates->perPage(),
                'total' => $certificates->total(),
                'last_page' => $certificates->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/medical-certificates',
        tags: ['Medical Certificate'],
        security: [['bearerAuth' => []]],
        summary: 'Create medical certificate',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['pet_id', 'rest_duration', 'start_date'],
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rest_duration', type: 'integer', example: 7),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-06-12'),
                    new OA\Property(property: 'additional_notes', type: 'string', example: 'Kontrol ulang setelah masa istirahat'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreMedicalCertificateRequest $request): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')->store('medical-certificates', 'public');
        }
        $data['doctor_id'] = Auth::id();

        $certificate = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan medis berhasil dibuat',
            'data' => new MedicalCertificateResource($certificate),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $certificate = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail surat keterangan medis berhasil diambil',
            'data' => new MedicalCertificateResource($certificate),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/medical-certificates/{id}',
        tags: ['Medical Certificate'],
        security: [['bearerAuth' => []]],
        summary: 'Update medical certificate',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                    new OA\Property(property: 'rest_duration', type: 'integer', example: 7),
                    new OA\Property(property: 'start_date', type: 'string', format: 'date', example: '2026-06-12'),
                    new OA\Property(property: 'additional_notes', type: 'string', example: 'Kontrol ulang setelah masa istirahat'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateMedicalCertificateRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();
        if ($request->hasFile('certificate_file')) {
            $data['certificate_file'] = $request->file('certificate_file')->store('medical-certificates', 'public');
        }

        $certificate = $this->service->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan medis berhasil diperbarui',
            'data' => new MedicalCertificateResource($certificate),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Surat keterangan medis berhasil dihapus',
        ], 200);
    }
}
