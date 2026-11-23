<x-layouts.admin title="Laporan PPDB">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Laporan PPDB</h1>
                <p class="text-sm text-gray-500 mt-1">Rekap pendaftaran terkirim dengan data minimum untuk operasional panitia.</p>
            </div>
            <a href="{{ route('admin.reports.export', request()->query()) }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Unduh CSV</a>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        @if ($error)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $error }}</div>
        @endif

        <form method="GET" action="{{ route('admin.reports.index') }}" class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label for="period_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Periode</label>
                    <select id="period_id" name="period_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua periode</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected((string) ($filters['period_id'] ?? '') === (string) $period->id)>{{ $period->academic_year }} — {{ $period->name }}{{ $period->is_active ? ' (Aktif)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="program_id" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Program</label>
                    <select id="program_id" name="program_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua program</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected((string) ($filters['program_id'] ?? '') === (string) $program->id)>{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label for="date_from" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Submit mulai</label><input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div><label for="date_to" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Submit sampai</label><input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm"></div>
                <div>
                    <label for="verification_status" class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-1">Status dokumen</label>
                    <select id="verification_status" name="verification_status" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Semua status</option>
                        @foreach (\App\Enums\DocumentVerificationStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['verification_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-2"><button class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Terapkan Filter</button><a href="{{ route('admin.reports.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Reset</a></div>
        </form>

        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pendaftar SUBMITTED sesuai filter</p>
            <p class="mt-1 text-3xl font-extrabold text-gray-900">{{ number_format($summary['total_submitted'], 0, ',', '.') }}</p>
            <p class="mt-2 text-xs text-gray-500">Periode: {{ $filters['date_from'] ?: 'awal data' }} s.d. {{ $filters['date_to'] ?: 'hari ini' }} · Waktu submit ditampilkan dalam WIB.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs"><h2 class="font-bold text-gray-900">Per Program</h2>@forelse($summary['by_program'] as $item)<div class="flex justify-between border-b border-gray-100 py-2 text-sm"><span>{{ $item['label'] }}</span><strong>{{ number_format($item['total']) }}</strong></div>@empty<p class="text-sm text-gray-500 mt-3">Belum ada data.</p>@endforelse</section>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs"><h2 class="font-bold text-gray-900">Periode</h2>@forelse($summary['by_period'] as $item)<div class="flex justify-between border-b border-gray-100 py-2 text-sm gap-3"><span>{{ $item['label'] }}</span><strong>{{ number_format($item['total']) }}</strong></div>@empty<p class="text-sm text-gray-500 mt-3">Belum ada data.</p>@endforelse</section>
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs"><h2 class="font-bold text-gray-900">Status Dokumen</h2>@foreach([['PENDING','Menunggu'],['APPROVED','Disetujui'],['REVISION_REQUIRED','Perlu perbaikan'],['REJECTED','Ditolak']] as [$key,$label])<div class="flex justify-between border-b border-gray-100 py-2 text-sm"><span>{{ $label }}</span><strong>{{ number_format($summary['verification'][$key] ?? 0) }}</strong></div>@endforeach</section>
        </div>
    </div>
</x-layouts.admin>
