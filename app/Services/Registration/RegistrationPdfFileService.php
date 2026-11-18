<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Models\RegistrationPdfVersion;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RegistrationPdfFileService
{
    public function store(string $diskName, string $path, string $contents): void
    {
        $this->assertPrivateDisk($diskName);
        $this->assertPdfPath($path);

        $disk = Storage::disk($diskName);
        $stored = false;

        try {
            if (! $disk->put($path, $contents)) {
                throw new RuntimeException('Penyimpanan PDF gagal.');
            }
            $stored = true;

            $storedContents = $disk->get($path);
            if (
                ! str_starts_with($storedContents, '%PDF-')
                || ! hash_equals(hash('sha256', $contents), hash('sha256', $storedContents))
            ) {
                throw new RuntimeException('Pemeriksaan integritas PDF gagal.');
            }
        } catch (Throwable $exception) {
            if ($stored) {
                try {
                    if (! $disk->delete($path)) {
                        throw new RuntimeException('Berkas PDF sementara tidak dapat dibersihkan.');
                    }
                } catch (Throwable $cleanupFailure) {
                    report($cleanupFailure);

                    throw new RuntimeException(
                        'Pemeriksaan PDF gagal dan berkas sementara tidak dapat dibersihkan.',
                        previous: $exception,
                    );
                }
            }

            throw $exception;
        }
    }

    public function readVerified(RegistrationPdfVersion $version): ?string
    {
        $diskName = $version->storage_disk;
        if (! is_string($diskName) || $diskName === '') {
            return null;
        }

        try {
            $this->assertPrivateDisk($diskName);
            $this->assertPdfPath($version->file_path);
            $disk = Storage::disk($diskName);

            if (! $disk->exists($version->file_path)) {
                return null;
            }

            $contents = $disk->get($version->file_path);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (
            $version->mime_type !== null
            && $version->mime_type !== 'application/pdf'
        ) {
            return null;
        }

        if (
            ! str_starts_with($contents, '%PDF-')
            || ! hash_equals($version->file_hash, hash('sha256', $contents))
            || ($version->file_size_bytes !== null && $version->file_size_bytes !== strlen($contents))
        ) {
            report(new RuntimeException('A registration PDF failed its stored integrity metadata check.'));

            return null;
        }

        return $contents;
    }

    public function delete(string $diskName, string $path): void
    {
        $this->assertPrivateDisk($diskName);
        $this->assertPdfPath($path);

        if (! Storage::disk($diskName)->delete($path)) {
            throw new RuntimeException('Berkas PDF sementara tidak dapat dibersihkan.');
        }
    }

    private function assertPrivateDisk(string $diskName): void
    {
        $diskConfiguration = config('filesystems.disks.'.$diskName);

        if (
            ! is_array($diskConfiguration)
            || ($diskConfiguration['serve'] ?? false) === true
            || ($diskConfiguration['visibility'] ?? 'private') === 'public'
        ) {
            throw new RuntimeException('Penyimpanan PDF harus menggunakan disk privat.');
        }
    }

    private function assertPdfPath(string $path): void
    {
        $basePath = trim((string) config('ppdb.documents.pdf_path'), '/').'/';

        if (
            $basePath === '/'
            || ! str_starts_with($path, $basePath)
            || str_contains($path, '..')
            || str_contains($path, '\\')
        ) {
            throw new RuntimeException('Path penyimpanan PDF tidak valid.');
        }
    }
}
