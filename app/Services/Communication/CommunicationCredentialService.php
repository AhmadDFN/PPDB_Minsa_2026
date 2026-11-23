<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\CommunicationChannel;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CommunicationCredentialService
{
    public function __construct(private readonly CommunicationAuditService $audit) {}

    public function store(CommunicationChannel $channel, string $deviceToken): void
    {
        $channel->credential()->updateOrCreate([], [
            'credential_ciphertext' => Crypt::encryptString($deviceToken),
            'credential_version' => (int) ($channel->credential?->credential_version ?? 0) + 1,
        ]);
    }

    public function updateValidated(CommunicationChannel $channel, string $deviceToken, int $expectedVersion, array $metadata, ?User $actor = null): void
    {
        DB::transaction(function () use ($channel, $deviceToken, $expectedVersion, $metadata, $actor): void {
            $lockedChannel = CommunicationChannel::query()->lockForUpdate()->findOrFail($channel->id);
            $credential = $lockedChannel->credential()->lockForUpdate()->first();
            $currentVersion = (int) ($credential?->credential_version ?? 0);

            if ($currentVersion !== $expectedVersion) {
                throw new RuntimeException('credential_version_conflict');
            }

            $lockedChannel->credential()->updateOrCreate([], [
                'credential_ciphertext' => Crypt::encryptString($deviceToken),
                'credential_version' => $currentVersion + 1,
            ]);
            $lockedChannel->forceFill(array_merge($metadata, ['updated_by' => $actor?->id]))->save();
            $this->audit->record('credential_changed', $lockedChannel, $actor);
        });
    }

    public function reveal(CommunicationChannel $channel): ?string
    {
        $ciphertext = $channel->credential?->credential_ciphertext;

        if (! $ciphertext) {
            return null;
        }

        try {
            return Crypt::decryptString($ciphertext);
        } catch (\Throwable) {
            return null;
        }
    }
}
