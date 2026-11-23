<x-layouts.admin title="Verifikasi Berkas">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Verifikasi Berkas</h1>
            <p class="text-sm text-gray-500 mt-1">Tinjau berkas dari pendaftar yang sudah mengirimkan formulir.</p>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([['complete', 'Disetujui', 'bg-emerald-50 text-emerald-700'], ['pending', 'Menunggu', 'bg-amber-50 text-amber-700'], ['revision', 'Perlu Perbaikan', 'bg-red-50 text-red-700'], ['approved', 'Total Disetujui', 'bg-blue-50 text-blue-700']] as [$key, $label, $classes])
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 inline-flex rounded-lg px-2.5 py-1 text-2xl font-bold {{ $classes }}">{{ number_format($summary[$key]) }}</p>
                </div>
            @endforeach
        </div>

        <form method="GET" class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Cari</label>
                <input name="search" value="{{ request('search') }}" placeholder="Nomor pendaftaran atau nama" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Status dokumen</label>
                <select name="verification_status" class="w-full rounded-lg border-gray-300 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('verification_status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Filter</button>
                <a href="{{ route('admin.document-verification.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Reset</a>
            </div>
        </form>

        @if (session('success')) <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div> @endif

        <div class="space-y-5">
            @forelse ($registrations as $registration)
                <section class="rounded-xl border border-gray-200 bg-white shadow-xs overflow-hidden">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 border-b border-gray-100 px-5 py-4">
                        <div>
                            <h2 class="font-bold text-gray-900">{{ $registration->applicant?->full_name ?? 'Calon peserta didik' }}</h2>
                            <p class="text-xs text-gray-500 font-mono">{{ $registration->registration_number }} · {{ $registration->admissionPeriodProgram?->program?->name ?? '-' }}</p>
                        </div>
                        <span class="text-xs text-gray-500">Dikirim {{ $registration->submitted_at?->format('d-m-Y H:i') }}</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @forelse ($registration->documents as $document)
                            @php($status = $document->verification_status)
                            <div class="px-5 py-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-gray-900">{{ $document->requirement?->name ?? 'Dokumen' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $document->original_name }} · {{ number_format($document->file_size_bytes / 1024, 1) }} KB</p>
                                    @if ($document->verification_reason)<p class="text-xs text-red-700 mt-1">Catatan: {{ $document->verification_reason }}</p>@endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status?->value === 'APPROVED' ? 'bg-emerald-100 text-emerald-800' : ($status?->value === 'PENDING' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ $status?->label() ?? 'Menunggu Verifikasi' }}</span>
                                    <a class="text-xs font-semibold text-primary-700 hover:underline" target="_blank" rel="noopener" href="{{ route('admin.document-verification.preview', [$registration, $document]) }}">Pratinjau</a>
                                    <a class="text-xs font-semibold text-gray-700 hover:underline" href="{{ route('admin.document-verification.history', [$registration, $document]) }}">Riwayat</a>
                                    <form method="POST" action="{{ route('admin.document-verification.approve', [$registration, $document]) }}">@csrf <button class="rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white">Setujui</button></form>
                                    <form method="POST" action="{{ route('admin.document-verification.revision', [$registration, $document]) }}" class="flex gap-1"><input name="reason" required maxlength="255" placeholder="Alasan revisi" class="w-32 rounded-md border-gray-300 text-xs"><button class="rounded-md bg-amber-600 px-2.5 py-1.5 text-xs font-semibold text-white">Revisi</button></form>
                                    <form method="POST" action="{{ route('admin.document-verification.reject', [$registration, $document]) }}" class="flex gap-1"><input name="reason" required maxlength="255" placeholder="Alasan penolakan" class="w-32 rounded-md border-gray-300 text-xs"><button class="rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white">Tolak</button></form>
                                </div>
                            </div>
                        @empty
                            <p class="px-5 py-4 text-sm text-gray-500">Belum ada dokumen pada pendaftaran ini.</p>
                        @endforelse
                    </div>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-white px-5 py-10 text-center text-sm text-gray-500">Tidak ada pendaftaran terkirim yang sesuai filter.</div>
            @endforelse
        </div>
        {{ $registrations->links() }}
    </div>
</x-layouts.admin>
