<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['sometimes', 'required', 'string', 'max:255'],
            'document_file' => ['sometimes', 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => 'Tipe dokumen wajib diisi.',
            'document_type.max' => 'Tipe dokumen tidak boleh lebih dari 255 karakter.',
            'document_file.required' => 'File dokumen wajib diupload.',
            'document_file.file' => 'Dokumen harus berupa file.',
            'document_file.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'document_file.max' => 'Ukuran file tidak boleh lebih dari 10MB.',
        ];
    }
}
