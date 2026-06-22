<?php

namespace App\Http\Controllers;

use App\Http\Resources\PharmacyPrescriptionResource;
use App\Services\PharmacyPrescriptionService;
use Illuminate\Http\Request;

class PharmacyPrescriptionController extends Controller
{
    public function __construct(
        protected PharmacyPrescriptionService $service
    ) {
    }

    public function index(Request $request)
    {
        $prescriptions = $this->service->getQueue(
            $request->search
        );

        return response()->json([
            'success' => true,
            'message' => 'Daftar resep berhasil diambil',
            'data' => PharmacyPrescriptionResource::collection($prescriptions),
        ]);
    }

    public function updateStatus($medicalRecordId)
    {
        $this->service->updateStatus($medicalRecordId);

        return response()->json([
            'success' => true,
            'message' => 'Status resep berhasil diperbarui',
        ]);
    }
}
