<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MedicalCertificateResource extends JsonResource
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
            'rest_duration' => $this->rest_duration,
            'start_date' => $this->start_date,
            'end_date' => $this->start_date ? Carbon::parse($this->start_date)->addDays((int) $this->rest_duration)->toDateString() : null,
            'additional_notes' => $this->additional_notes,
            'certificate_file' => $this->certificate_file,
            'certificate_url' => $this->certificate_file ? Storage::disk('public')->url($this->certificate_file) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
