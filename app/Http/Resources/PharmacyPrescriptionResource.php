<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyPrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $prescription = $this->resource->first();

        return [

            'id' => $prescription->medical_record_id,

            'medical_record_id' => $prescription->medical_record_id,

            'prescription_code' =>
                'RX-' .
                str_pad(
                    $prescription->medical_record_id,
                    5,
                    '0',
                    STR_PAD_LEFT
                ),

            'patient_name' =>
                $prescription->medicalRecord?->pet?->name,

            'owner_name' =>
                $prescription->medicalRecord?->pet?->owner?->name,

            'doctor_name' =>
                $prescription->medicalRecord?->doctor?->name,

            'date' =>
                optional($prescription->created_at)
                    ->format('d M Y'),

            'time' =>
                optional($prescription->created_at)
                    ->format('H:i'),

            'status' =>
                $this->resource->every(function ($item) {
                    return $item->status === 'Ditebus';
                })
                    ? 'Selesai'
                    : 'Pending',

            'items' => $this->resource->map(function ($item) {

                return [

                    'id' => $item->id,

                    'product_name' =>
                        $item->product?->name,

                    'quantity' =>
                        $item->quantity,

                    'instructions' =>
                        $item->instructions,

                    'status' =>
                        $item->status,

                ];

            })->values(),

        ];
    }
}
