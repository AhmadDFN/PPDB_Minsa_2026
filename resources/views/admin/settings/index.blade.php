<x-layouts.admin title="Pengaturan Sistem — PPDB Minsatama">
    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-1" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-primary-600 transition">Dashboard</a>
                    <span>/</span>
                    <span class="text-gray-900 font-semibold">Pengaturan Sistem</span>
                </nav>
                <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                    Pengaturan Sistem & Branding
                </h1>
                <p class="text-xs sm:text-sm text-gray-600">
                    Konfigurasi nama institusi dan identitas tampilan utama sistem PPDB.
                </p>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if (session('success'))
            <x-ui.alert type="success" dismissible>
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert type="danger" dismissible>
                {{ session('error') }}
            </x-ui.alert>
        @endif

        {{-- Form Card --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Identitas Nama Institusi</h2>
                        <p class="text-xs text-gray-500">Digunakan secara dinamis pada header dasbor, pesan WhatsApp OTP & password, serta antarmuka pendaftar.</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
                @csrf

                <div>
                    <label for="institution_display_name" class="block text-sm font-semibold text-gray-800 mb-1.5">
                        Nama Institusi / Judul Aplikasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="institution_display_name"
                        id="institution_display_name"
                        value="{{ old('institution_display_name', $institutionName) }}"
                        class="w-full max-w-xl px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition @error('institution_display_name') border-red-500 ring-1 ring-red-500 @enderror"
                        placeholder="Contoh: PPDB MIN 1 KOTA MADIUN"
                        required
                        maxlength="100"
                    >
                    @error('institution_display_name')
                        <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        Default: <code>PPDB MIN 1 KOTA MADIUN</code>. Nama ini akan langsung muncul di pesan WhatsApp dan header aplikasi.
                    </p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end">
                    <button
                        type="submit"
                        class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-primary-600 hover:bg-primary-700 shadow-sm transition active:scale-[0.98] cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Audit Log Table Card --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-900">Riwayat Perubahan Pengaturan</h3>
                <p class="text-xs text-gray-500">Catatan audit riwayat perubahan konfigurasi sistem.</p>
            </div>

            @if ($recentLogs->isEmpty())
                <div class="p-8 text-center text-xs text-gray-500">
                    Belum ada riwayat perubahan pengaturan sistem.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3">Waktu</th>
                                <th class="px-6 py-3">Pengubah</th>
                                <th class="px-6 py-3">Kunci Pengaturan</th>
                                <th class="px-6 py-3">Nilai Lama</th>
                                <th class="px-6 py-3">Nilai Baru</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentLogs as $log)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-3.5 text-gray-600 whitespace-nowrap">
                                        {{ $log->created_at?->format('d/m/Y H:i') ?? '—' }} WIB
                                    </td>
                                    <td class="px-6 py-3.5 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $log->user?->name ?? 'Sistem' }}
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-gray-600">
                                        {{ $log->setting_key }}
                                    </td>
                                    <td class="px-6 py-3.5 text-gray-500 max-w-xs truncate">
                                        {{ $log->old_value ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3.5 font-semibold text-primary-700 max-w-xs truncate">
                                        {{ $log->new_value ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
