<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'document_type' => ['required', 'string', 'max:255'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID wajib diisi.',
            'pet_id.exists' => 'Pet yang dipilih tidak ditemukan.',
            'document_type.required' => 'Tipe dokumen wajib diisi.',
            'document_type.max' => 'Tipe dokumen tidak boleh lebih dari 255 karakter.',
            'document_file.required' => 'File dokumen wajib diupload.',
            'document_file.file' => 'Dokumen harus berupa file.',
            'document_file.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file tidak boleh lebih dari 10MB.',
        ];
    }
}
