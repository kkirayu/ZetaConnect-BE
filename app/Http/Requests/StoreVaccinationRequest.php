<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaccinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'vaccine_name' => ['required', 'string', 'max:255'],
            'batch_number' => ['nullable', 'string', 'max:100'],
            'next_due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet yang dipilih tidak ditemukan.',
            'vaccine_name.required' => 'Nama vaksin wajib diisi.',
            'vaccine_name.max' => 'Nama vaksin tidak boleh lebih dari 255 karakter.',
            'batch_number.max' => 'Nomor batch tidak boleh lebih dari 100 karakter.',
            'next_due_date.required' => 'Tanggal vaksinasi berikutnya wajib diisi.',
            'next_due_date.date' => 'Tanggal vaksinasi harus format tanggal yang valid.',
            'next_due_date.after_or_equal' => 'Tanggal vaksinasi tidak boleh di masa lalu.',
        ];
    }
}
