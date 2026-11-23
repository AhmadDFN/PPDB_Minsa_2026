<x-layouts.admin title="Communication Center">
    <x-slot:header>
        <div class="flex flex-col gap-1">
            <p class="text-xs font-bold uppercase tracking-wider text-primary-600">Communication Center</p>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Provider &amp; Gateway</h1>
            <p class="text-sm text-gray-500">Kelola satu kanal WhatsApp aktif secara manual. Credential tidak pernah ditampilkan kembali.</p>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 text-sm text-purple-900">
            <strong>Mode pengiriman:</strong>
            @if ($databaseModeEnabled)
                Database channel aktif. Provider tidak melakukan automatic failover.
            @else
                Legacy environment configuration{{ $legacyConfigured ? ' terdeteksi' : ' belum dikonfigurasi' }}. Menyimpan dan mengaktifkan channel database akan memindahkan pengiriman secara eksplisit.
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-xs p-6">
            <h2 class="text-base font-bold text-gray-900">Tambah Channel Fonnte</h2>
            <p class="text-xs text-gray-500 mt-1">Gunakan Device Token. Account Token dan manajemen perangkat berada di luar scope.</p>
            <form method="POST" action="{{ route('admin.communication.channels.store') }}" class="mt-5 grid gap-4 md:grid-cols-3">
                @csrf
                <label class="text-xs font-semibold text-gray-700">Nama konfigurasi<input name="channel_name" required placeholder="Fonnte Utama" class="mt-1 w-full rounded-lg border-gray-300 text-sm" /></label>
                <label class="text-xs font-semibold text-gray-700">Device Token<input name="device_token" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border-gray-300 text-sm" /></label>
                <div class="flex items-end"><button class="w-full rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">Validasi &amp; Simpan</button></div>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Konfigurasi Provider</h2></div>
            @forelse ($channels as $channel)
                <div class="p-6 border-b border-gray-100 last:border-0 space-y-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div class="flex items-center gap-2"><h3 class="font-bold text-gray-900">{{ $channel->channel_name }}</h3><span class="rounded bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-600">{{ $channel->provider_code }}</span></div>
                            <p class="mt-1 text-xs text-gray-500">Sender: {{ $channel->sender_name ?: '-' }} &bull; {{ $channel->sender_identifier ?: '-' }}</p>
                            <p class="mt-1 text-xs text-gray-500">Validasi terakhir: {{ $channel->last_validated_at?->setTimezone('Asia/Jakarta')->format('d M Y H:i').' WIB' ?: 'Belum pernah' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $channel->connection_status === 'connected' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $channel->connection_status }}</span>
                            @if ($activeChannelId === $channel->id)<span class="rounded-full bg-primary-100 px-2.5 py-1 text-xs font-semibold text-primary-700">ACTIVE</span>@endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.communication.channels.validate', $channel) }}">@csrf<button class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Test connection</button></form>
                        @if ($activeChannelId !== $channel->id)
                            <form method="POST" action="{{ route('admin.communication.channels.activate', $channel) }}" onsubmit="return confirm('Aktifkan channel ini? Provider aktif sebelumnya akan dinonaktifkan.')">@csrf<button class="rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white hover:bg-primary-700">Aktifkan</button></form>
                        @else
                            <form method="POST" action="{{ route('admin.communication.channels.deactivate', $channel) }}" onsubmit="return confirm('Nonaktifkan channel ini?')">@csrf<button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Nonaktifkan</button></form>
                        @endif
                    </div>
                    <details class="rounded-lg border border-gray-200 p-4">
                        <summary class="cursor-pointer text-xs font-semibold text-gray-700">Perbarui nama atau Device Token</summary>
                        <form method="POST" action="{{ route('admin.communication.channels.update', $channel) }}" class="mt-4 grid gap-4 md:grid-cols-3">
                            @csrf @method('PATCH')
                            <input type="hidden" name="credential_version" value="{{ $channel->credential?->credential_version ?? 0 }}" />
                            <label class="text-xs font-semibold text-gray-700">Nama konfigurasi<input name="channel_name" value="{{ $channel->channel_name }}" required class="mt-1 w-full rounded-lg border-gray-300 text-sm" /></label>
                            <label class="text-xs font-semibold text-gray-700">Device Token baru<input name="device_token" type="password" autocomplete="new-password" placeholder="Kosongkan jika tidak diganti" class="mt-1 w-full rounded-lg border-gray-300 text-sm" /></label>
                            <div class="flex items-end"><button class="w-full rounded-lg border border-primary-300 px-4 py-2.5 text-xs font-semibold text-primary-700 hover:bg-primary-50">Simpan perubahan</button></div>
                        </form>
                    </details>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">Belum ada channel database. Pengiriman legacy tetap berjalan sampai channel diaktifkan.</div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
