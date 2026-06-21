<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'weight' => ['sometimes', 'required', 'numeric', 'min:0.01', 'max:999.99'],
            'owner_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'subjective_complaint' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama anabul wajib diisi.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'weight.required' => 'Berat badan wajib diisi.',
            'weight.numeric' => 'Berat badan harus angka.',
            'weight.min' => 'Berat badan harus lebih dari 0.',
            'owner_id.exists' => 'Pemilik yang dipilih tidak ditemukan.',
            'subjective_complaint.max' => 'Keluhan subjektif tidak boleh lebih dari 2000 karakter.',
        ];
    }
}
