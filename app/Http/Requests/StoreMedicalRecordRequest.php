<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'diagnosis_dictionary_id' => ['required', 'integer', 'exists:diagnosis_dictionary,id'],
            'weight' => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'subjective' => ['required', 'string', 'max:5000'],
            'objective' => ['required', 'string', 'max:5000'],
            'plan' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet yang dipilih tidak ditemukan.',
            'diagnosis_dictionary_id.required' => 'Diagnosis wajib dipilih.',
            'diagnosis_dictionary_id.exists' => 'Diagnosis yang dipilih tidak ditemukan.',
            'weight.required' => 'Berat badan wajib diisi.',
            'weight.numeric' => 'Berat badan harus angka.',
            'weight.min' => 'Berat badan harus lebih dari 0.',
            'subjective.required' => 'Catatan keluhan (Subjective) wajib diisi.',
            'objective.required' => 'Hasil pemeriksaan (Objective) wajib diisi.',
            'plan.required' => 'Rencana pengobatan (Plan) wajib diisi.',
        ];
    }
}
