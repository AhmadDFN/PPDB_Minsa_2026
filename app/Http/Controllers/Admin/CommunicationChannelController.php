<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\CommunicationChannel;
use App\Models\CommunicationSetting;
use App\Services\Communication\CommunicationAuditService;
use App\Services\Communication\CommunicationConnectionService;
use App\Services\Communication\CommunicationCredentialService;
use App\Services\Communication\WhatsAppProviderRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class CommunicationChannelController
{
    public function __construct(
        private readonly WhatsAppProviderRegistry $registry,
        private readonly CommunicationCredentialService $credentials,
        private readonly CommunicationConnectionService $connections,
        private readonly CommunicationAuditService $audit,
    ) {}

    public function index(): View
    {
        $this->authorizeSuperadmin(request());

        return view('admin.communication.index', [
            'channels' => CommunicationChannel::query()->with('credential')->latest()->get(),
            'activeChannelId' => ($activeChannelId = CommunicationSetting::query()->value('active_channel_id')) !== null ? (int) $activeChannelId : null,
            'providers' => $this->registry->all(),
            'databaseModeEnabled' => (bool) CommunicationSetting::query()->value('database_provider_mode_enabled'),
            'legacyConfigured' => is_string(config('services.fonnte.token')) && config('services.fonnte.token') !== '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $request->validate([
            'provider_code' => ['required', 'string', 'in:fonnte'],
            'channel_name' => ['required', 'string', 'max:120'],
            'device_token' => ['required', 'string', 'min:8', 'max:500'],
        ]);

        $provider = $this->registry->get($data['provider_code']);
        $result = $provider->validateConnection($data['device_token']);
        if (! $result->connected || ! $result->deviceProfile) {
            return back()->withInput($request->except('device_token'))->with('error', 'Token valid tetapi perangkat belum terhubung atau tidak dapat diverifikasi.');
        }

        $channel = CommunicationChannel::query()->create([
            'provider_code' => $provider->code(),
            'channel_name' => $data['channel_name'],
            'sender_identifier' => $result->deviceProfile->senderIdentifier,
            'sender_name' => $result->deviceProfile->senderName,
            'connection_status' => 'connected',
            'last_validated_at' => now(),
            'safe_metadata' => $result->deviceProfile->safeMetadata,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);
        $this->credentials->store($channel, $data['device_token']);
        $this->audit->record('channel_created', $channel, $request->user(), ['provider_code' => $provider->code()]);

        return back()->with('success', 'Konfigurasi provider berhasil disimpan. Token tidak ditampilkan kembali.');
    }

    public function update(Request $request, CommunicationChannel $channel): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $data = $request->validate([
            'channel_name' => ['required', 'string', 'max:120'],
            'device_token' => ['nullable', 'string', 'min:8', 'max:500'],
            'credential_version' => ['nullable', 'integer', 'min:0'],
        ]);

        if (! empty($data['device_token'])) {
            $result = $this->registry->get($channel->provider_code)->validateConnection($data['device_token']);
            if (! $result->connected || ! $result->deviceProfile) {
                return back()->withInput($request->except('device_token'))->with('error', 'Credential baru gagal divalidasi; credential lama tetap dipertahankan.');
            }
            try {
                $this->credentials->updateValidated($channel, $data['device_token'], (int) ($data['credential_version'] ?? 0), [
                    'sender_identifier' => $result->deviceProfile->senderIdentifier,
                    'sender_name' => $result->deviceProfile->senderName,
                    'safe_metadata' => $result->deviceProfile->safeMetadata,
                    'connection_status' => 'connected',
                    'last_validated_at' => now(),
                ], $request->user());
            } catch (RuntimeException $e) {
                if ($e->getMessage() === 'credential_version_conflict') {
                    return back()->withInput($request->except('device_token'))->with('error', 'Credential sudah diperbarui oleh proses lain. Muat ulang halaman sebelum mencoba lagi.');
                }

                throw $e;
            }
        }

        $channel->forceFill(['channel_name' => $data['channel_name'], 'updated_by' => $request->user()->id])->save();

        return back()->with('success', 'Konfigurasi channel diperbarui.');
    }

    public function validateConnection(Request $request, CommunicationChannel $channel): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $ok = $this->connections->validate($channel, $request->user());

        return back()->with($ok ? 'success' : 'error', $ok ? 'Perangkat terhubung dan siap digunakan.' : 'Validasi koneksi gagal atau perangkat belum terhubung.');
    }

    public function activate(Request $request, CommunicationChannel $channel): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        if (! $this->connections->activate($channel, $request->user())) {
            return back()->with('error', 'Channel belum dapat diaktifkan. Jalankan validasi dan pastikan perangkat terhubung.');
        }

        return back()->with('success', 'Channel aktif berhasil diganti secara manual.');
    }

    public function deactivate(Request $request, CommunicationChannel $channel): RedirectResponse
    {
        $this->authorizeSuperadmin($request);

        $this->connections->deactivate($channel, $request->user());

        return back()->with('success', 'Channel dinonaktifkan. Pengiriman tidak berpindah otomatis ke provider lain.');
    }

    private function authorizeSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('superadmin'), 403);
    }
}
