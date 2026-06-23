<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiagnosisDictionaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disease_name' => ['sometimes', 'required', 'string', 'max:255', 'unique:diagnosis_dictionary,disease_name,' . $this->route('diagnosis_dictionary')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'disease_name.required' => 'Nama penyakit wajib diisi.',
            'disease_name.unique' => 'Nama penyakit sudah terdaftar di sistem.',
            'disease_name.max' => 'Nama penyakit tidak boleh lebih dari 255 karakter.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 1000 karakter.',
        ];
    }
}
