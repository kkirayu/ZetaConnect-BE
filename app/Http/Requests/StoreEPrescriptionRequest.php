<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEPrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_record_id' => ['required', 'integer', 'exists:medical_records,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'instructions' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'medical_record_id.required' => 'Medical Record ID wajib diisi.',
            'medical_record_id.exists' => 'Medical Record tidak ditemukan.',
            'product_id.required' => 'Product ID wajib diisi.',
            'product_id.exists' => 'Product tidak ditemukan.',
            'quantity.required' => 'Quantity wajib diisi.',
            'quantity.integer' => 'Quantity harus angka.',
            'quantity.min' => 'Quantity minimal 1.',
            'instructions.required' => 'Instruksi penggunaan wajib diisi.',
            'instructions.max' => 'Instruksi tidak boleh lebih dari 500 karakter.',
        ];
    }
}
