<?php

declare(strict_types=1);

namespace App\Services\Document;

use App\Models\DocumentRequirement;
use App\Models\Registration;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DocumentUploadService
{
    private const ABSOLUTE_MAX_SIZE_KB = 5120;

    /**
     * Store a validated document under a generated private path.
     *
     * @return array{
     *     file_path: string,
     *     original_name: string,
     *     mime_type: string,
     *     file_size_bytes: int,
     *     file_hash: string
     * }
     */
    public function store(
        UploadedFile $file,
        Registration $registration,
        DocumentRequirement $requirement
    ): array {
        $effectiveLimit = $this->effectiveLimit($requirement);
        $this->validateFile($file, $requirement, $effectiveLimit);

        $size = $file->getSize();
        $hash = hash_file('sha256', $file->getRealPath());

        if (! is_int($size) || ! is_string($hash)) {
            throw new RuntimeException('Metadata integritas dokumen tidak dapat dibaca.');
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        if (! is_string($mimeType)) {
            throw new RuntimeException('Tipe MIME dokumen tidak dapat dibaca.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $safeName = $this->safeOriginalName($file->getClientOriginalName());
        $directory = trim((string) config('ppdb.documents.base_path'), '/')
            .'/'.$registration->id
            .'/'.$requirement->id;
        $path = Storage::disk((string) config('ppdb.documents.disk'))
            ->putFileAs($directory, $file, Str::uuid().'.'.$extension);

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Dokumen gagal disimpan pada penyimpanan privat.');
        }

        return [
            'file_path' => $path,
            'original_name' => $safeName,
            'mime_type' => $mimeType,
            'file_size_bytes' => $size,
            'file_hash' => $hash,
        ];
    }

    public function deleteStoredFile(string $path): void
    {
        $disk = Storage::disk((string) config('ppdb.documents.disk'));

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException('Berkas yatim gagal dibersihkan dari penyimpanan privat.');
        }
    }

    private function effectiveLimit(DocumentRequirement $requirement): int
    {
        $configuredGlobal = (int) config('ppdb.documents.global_max_size_kb', self::ABSOLUTE_MAX_SIZE_KB);
        if ($configuredGlobal < 1) {
            throw new RuntimeException('Konfigurasi batas ukuran dokumen global tidak valid.');
        }

        $globalLimit = min($configuredGlobal, self::ABSOLUTE_MAX_SIZE_KB);
        $requirementLimit = (int) $requirement->max_size_kb;

        if ($requirementLimit < 1 || $requirementLimit > self::ABSOLUTE_MAX_SIZE_KB) {
            throw ValidationException::withMessages([
                'file' => 'Konfigurasi batas ukuran persyaratan dokumen tidak valid.',
            ]);
        }

        return min($globalLimit, $requirementLimit);
    }

    private function validateFile(UploadedFile $file, DocumentRequirement $requirement, int $maxSizeKb): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => 'Berkas gagal diunggah. Silakan coba kembali.',
            ]);
        }

        $size = $file->getSize();
        if (! is_int($size) || $size > $maxSizeKb * 1024) {
            throw ValidationException::withMessages([
                'file' => "Ukuran berkas tidak boleh lebih dari {$maxSizeKb} KB.",
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        if (! isset($allowedExtensions[$extension])) {
            throw ValidationException::withMessages([
                'file' => 'Format berkas harus PDF, JPG, JPEG, atau PNG.',
            ]);
        }

        $actualMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath());
        $allowedMimes = array_intersect(
            ['application/pdf', 'image/jpeg', 'image/png'],
            $requirement->getAllowedMimesArray(),
        );

        if ($actualMime !== $allowedExtensions[$extension] || ! in_array($actualMime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => 'Isi berkas tidak sesuai dengan format dokumen yang diizinkan.',
            ]);
        }
    }

    private function safeOriginalName(string $originalName): string
    {
        $name = basename(str_replace('\\', '/', $originalName));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = mb_substr(trim($name), 0, 255);

        return $name !== '' ? $name : 'dokumen';
    }
}
