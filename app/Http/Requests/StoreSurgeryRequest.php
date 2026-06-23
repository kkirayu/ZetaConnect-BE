<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSurgeryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'surgery_type' => ['required', 'string', 'max:255'],
            'anesthesia_notes' => ['nullable', 'string', 'max:1000'],
            'post_op_instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet yang dipilih tidak ditemukan.',
            'surgery_type.required' => 'Tipe operasi wajib diisi.',
            'surgery_type.max' => 'Tipe operasi tidak boleh lebih dari 255 karakter.',
            'anesthesia_notes.max' => 'Catatan anestesi tidak boleh lebih dari 1000 karakter.',
            'post_op_instructions.max' => 'Instruksi pasca operasi tidak boleh lebih dari 1000 karakter.',
        ];
    }
}
