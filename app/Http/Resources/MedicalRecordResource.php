<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pet_id' => $this->pet_id,
            'pet' => [
                'id' => $this->pet?->id,
                'name' => $this->pet?->name,
            ],
            'doctor_id' => $this->doctor_id,
            'doctor' => [
                'id' => $this->doctor?->id,
                'name' => $this->doctor?->name,
            ],
            'diagnosis_dictionary_id' => $this->diagnosis_dictionary_id,
            'diagnosis' => [
                'id' => $this->diagnosis?->id,
                'disease_name' => $this->diagnosis?->disease_name,
            ],
            'weight' => (string) $this->weight,
            'subjective' => $this->subjective,
            'objective' => $this->objective,
            'plan' => $this->plan,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
