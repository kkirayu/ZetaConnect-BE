<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'doctor_instructions' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_name' => ['required', 'string', 'max:255'],
                'items.*.dosage' => ['required', 'string', 'max:100'],
                'items.*.frequency' => ['required', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet yang dipilih tidak ditemukan.',
            'items.required' => 'Minimal 1 obat harus ditambahkan.',
            'items.min' => 'Minimal 1 obat harus ditambahkan.',
            'items.*.medicine_name.required' => 'Nama obat wajib diisi.',
            'items.*.medicine_name.max' => 'Nama obat tidak boleh lebih dari 255 karakter.',
            'items.*.dosage.required' => 'Dosis wajib diisi.',
                'items.*.dosage.max' => 'Dosis tidak boleh lebih dari 100 karakter.',
            'items.*.frequency.required' => 'Frekuensi wajib diisi.',
                'items.*.frequency.max' => 'Frekuensi tidak boleh lebih dari 100 karakter.',
            'items.*.quantity.required' => 'Jumlah (Qty) wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah harus angka bulat.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
        ];
    }
}
