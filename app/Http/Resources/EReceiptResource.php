<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EReceiptResource extends JsonResource
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
            'doctor_instructions' => $this->doctor_instructions,
            'status' => $this->status,
            'items' => EReceiptItemResource::collection($this->items),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
