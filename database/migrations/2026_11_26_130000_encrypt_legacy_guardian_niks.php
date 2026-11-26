<?php

declare(strict_types=1);

use App\Support\NikManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('applicant_guardians')
            ->whereNull('nik_encrypted')
            ->whereNotNull('nik')
            ->orderBy('id')
            ->chunkById(100, function ($guardians): void {
                foreach ($guardians as $guardian) {
                    $nik = trim((string) $guardian->nik);
                    if ($nik === '') {
                        continue;
                    }

                    DB::table('applicant_guardians')
                        ->where('id', $guardian->id)
                        ->update([
                            'nik_encrypted' => NikManager::encrypt($nik),
                            'nik_lookup_hash' => NikManager::hash($nik),
                            'nik' => null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Plaintext NIK values are intentionally not restored.
    }
};
