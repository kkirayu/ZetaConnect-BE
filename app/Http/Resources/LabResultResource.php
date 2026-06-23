<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LabResultResource extends JsonResource
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
            'document_type' => $this->document_type,
            'document_file' => $this->document_file,
            'document_url' => $this->document_file ? Storage::disk('public')->url($this->document_file) : null,
            'file_size' => $this->file_size,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
