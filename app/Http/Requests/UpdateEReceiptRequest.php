<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_instructions' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'required', 'string', 'in:Pending,Completed'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.medicine_name' => ['required_with:items', 'string', 'max:255'],
                'items.*.dosage' => ['required_with:items', 'string', 'max:100'],
                'items.*.frequency' => ['required_with:items', 'string', 'max:100'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.min' => 'Minimal 1 obat harus ada.',
            'status.in' => 'Status harus Pending atau Completed.',
            'items.*.medicine_name.required_with' => 'Nama obat wajib diisi.',
            'items.*.dosage.required_with' => 'Dosis wajib diisi.',
            'items.*.frequency.required_with' => 'Frekuensi wajib diisi.',
            'items.*.quantity.required_with' => 'Jumlah wajib diisi.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
        ];
    }
}
