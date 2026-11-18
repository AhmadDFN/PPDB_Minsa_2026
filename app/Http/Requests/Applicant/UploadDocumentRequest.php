<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isApplicant();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $globalLimit = min(5120, max(1, (int) config('ppdb.documents.global_max_size_kb', 5120)));

        return [
            'file' => [
                'required',
                'file',
                'max:'.$globalLimit,
                'mimetypes:application/pdf,image/jpeg,image/png',
                'extensions:pdf,jpg,jpeg,png',
            ],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Berkas dokumen wajib dipilih.',
            'file.file' => 'Berkas yang dipilih tidak valid.',
            'file.max' => 'Ukuran berkas tidak boleh melebihi 5120 KB (5 MB).',
            'file.mimetypes' => 'Isi berkas harus berupa PDF, JPG, JPEG, atau PNG yang valid.',
            'file.extensions' => 'Ekstensi berkas harus PDF, JPG, JPEG, atau PNG.',
            'reason.max' => 'Alasan penggantian maksimal 255 karakter.',
        ];
    }
}
