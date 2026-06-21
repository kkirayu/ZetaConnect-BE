<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurgeryResource extends JsonResource
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
            'surgery_type' => $this->surgery_type,
            'anesthesia_notes' => $this->anesthesia_notes,
            'post_op_instructions' => $this->post_op_instructions,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
