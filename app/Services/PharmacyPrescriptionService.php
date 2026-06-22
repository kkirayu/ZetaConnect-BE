<?php

namespace App\Services;

use App\Models\EPrescription;

class PharmacyPrescriptionService
{
    public function getQueue($search = null)
    {
        return EPrescription::with([
            'product',
            'medicalRecord.pet.owner',
            'medicalRecord.doctor',
        ])
        ->when($search, function ($query) use ($search) {

            $query->whereHas(
                'medicalRecord.pet',
                function ($pet) use ($search) {

                    $pet->where('name', 'like', "%{$search}%")
                        ->orWhereHas('owner', function ($owner) use ($search) {

                            $owner->where('name', 'like', "%{$search}%");

                        });

                }
            );

        })
        ->orderBy('medical_record_id')
        ->get()
        ->groupBy('medical_record_id')
        ->values();
    }

    public function updateStatus($medicalRecordId)
    {
        EPrescription::where(
            'medical_record_id',
            $medicalRecordId
        )->update([
            'status' => 'Ditebus',
        ]);
    }
}
