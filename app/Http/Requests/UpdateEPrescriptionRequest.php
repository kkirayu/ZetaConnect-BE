<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['sometimes', 'required', 'integer', 'min:1', 'max:9999'],
            'instructions' => ['sometimes', 'required', 'string', 'max:500'],
            'status' => ['sometimes', 'required', 'string', 'in:Pending,Ditebus'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Quantity wajib diisi.',
            'quantity.integer' => 'Quantity harus angka.',
            'quantity.min' => 'Quantity minimal 1.',
            'instructions.required' => 'Instruksi penggunaan wajib diisi.',
            'instructions.max' => 'Instruksi tidak boleh lebih dari 500 karakter.',
            'status.required' => 'Status wajib diisi.',
            'status.in' => 'Status harus Pending atau Ditebus.',
        ];
    }
}
