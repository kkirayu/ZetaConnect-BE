<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabResultRequest;
use App\Http\Requests\UpdateLabResultRequest;
use App\Http\Resources\LabResultResource;
use App\Services\LabResultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class LabResultController extends Controller
{
    public function __construct(private LabResultService $service)
    {
    }

    public function index(): JsonResponse
    {
        $labResults = $this->service->getAll();

        return response()->json([
            'success' => true,
            'message' => 'Daftar hasil lab berhasil diambil',
            'data' => LabResultResource::collection($labResults),
            'pagination' => [
                'current_page' => $labResults->currentPage(),
                'per_page' => $labResults->perPage(),
                'total' => $labResults->total(),
                'last_page' => $labResults->lastPage(),
            ],
        ], 200);
    }

    #[OA\Post(
        path: '/doctor/lab-results',
        tags: ['Lab Result'],
        security: [['bearerAuth' => []]],
        summary: 'Upload lab result document',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['pet_id', 'document_type', 'document_file'],
                    properties: [
                        new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                        new OA\Property(property: 'document_type', type: 'string', example: 'Hematology'),
                        new OA\Property(property: 'document_file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreLabResultRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Store file if provided
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $path = $file->store('lab-results', 'public');
            $data['document_file'] = $path;

            // Calculate file size in MB
            $data['file_size'] = round($file->getSize() / (1024 * 1024), 2);
        }

        // Auto-inject doctor_id
        $data['doctor_id'] = Auth::id();

        $labResult = $this->service->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Hasil lab berhasil dibuat',
            'data' => new LabResultResource($labResult),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $labResult = $this->service->getById($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail hasil lab berhasil diambil',
            'data' => new LabResultResource($labResult),
        ], 200);
    }

    #[OA\Put(
        path: '/doctor/lab-results/{id}',
        tags: ['Lab Result'],
        security: [['bearerAuth' => []]],
        summary: 'Update lab result document',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'pet_id', type: 'integer', example: 1),
                        new OA\Property(property: 'document_type', type: 'string', example: 'Hematology'),
                        new OA\Property(property: 'document_file', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateLabResultRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        // Store file if provided
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $path = $file->store('lab-results', 'public');
            $data['document_file'] = $path;

            // Calculate file size in MB
            $data['file_size'] = round($file->getSize() / (1024 * 1024), 2);
        }

        $labResult = $this->service->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Hasil lab berhasil diperbarui',
            'data' => new LabResultResource($labResult),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Hasil lab berhasil dihapus',
        ], 200);
    }
}
