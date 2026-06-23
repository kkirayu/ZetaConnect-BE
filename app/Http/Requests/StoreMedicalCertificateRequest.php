<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'rest_duration' => ['required', 'integer', 'min:1', 'max:365'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'additional_notes' => ['nullable', 'string', 'max:2000'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet tidak ditemukan.',
            'rest_duration.required' => 'Lama istirahat wajib diisi.',
            'rest_duration.integer' => 'Lama istirahat harus angka bulat.',
            'rest_duration.min' => 'Lama istirahat minimal 1 hari.',
            'rest_duration.max' => 'Lama istirahat maksimal 365 hari.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal tidak valid.',
            'start_date.date_format' => 'Format tanggal harus Y-m-d.',
            'additional_notes.max' => 'Keterangan tambahan tidak boleh lebih dari 2000 karakter.',
            'certificate_file.file' => 'File sertifikat harus berupa file.',
            'certificate_file.mimes' => 'File sertifikat harus berformat PDF.',
            'certificate_file.max' => 'Ukuran file sertifikat tidak boleh lebih dari 10MB.',
        ];
    }
}
