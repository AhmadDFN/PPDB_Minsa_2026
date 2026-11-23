<x-layouts.admin title="Riwayat Verifikasi">
    <x-slot:header>
        <div>
            <a href="{{ route('admin.document-verification.index') }}" class="text-sm text-primary-700 hover:underline">← Kembali ke verifikasi</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Riwayat Verifikasi Dokumen</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $registration->registration_number }} · {{ $document->original_name }}</p>
        </div>
    </x-slot:header>
    <div class="rounded-xl border border-gray-200 bg-white divide-y divide-gray-100">
        @forelse ($document->verificationHistory as $entry)
            <div class="px-5 py-4">
                <div class="flex justify-between gap-3"><span class="font-semibold text-sm text-gray-900">{{ $entry->to_status?->label() ?? $entry->to_status }}</span><time class="text-xs text-gray-500">{{ $entry->created_at?->format('d-m-Y H:i') }}</time></div>
                <p class="text-xs text-gray-500 mt-1">Oleh {{ $entry->actor?->name ?? 'Akun yang dihapus' }}</p>
                @if ($entry->reason)<p class="text-sm text-gray-700 mt-2">{{ $entry->reason }}</p>@endif
            </div>
        @empty
            <p class="px-5 py-8 text-sm text-gray-500">Belum ada riwayat verifikasi.</p>
        @endforelse
    </div>
</x-layouts.admin>
