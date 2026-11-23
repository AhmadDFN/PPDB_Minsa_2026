<x-layouts.admin>
    <div class="space-y-6">
        <div>
            <p class="text-sm font-semibold text-primary-700">Laporan & Sistem</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900">Audit Aktivitas</h1>
            <p class="mt-1 text-sm text-gray-600">Pemantauan riwayat aktivitas administrasi dan operasional PPDB.</p>
        </div>

        @if (collect($sourceHealth)->contains(fn (array $source): bool => ! $source['available']))
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-semibold">Sebagian sumber audit belum tersedia. Periksa status migration pada server.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($sourceHealth as $source)
                        @unless ($source['available'])
                            <li>{{ $source['label'] }} — {{ $source['migration'] }}</li>
                        @endunless
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Total aktivitas hari ini', $summary['total']], ['Aktivitas administratif', $summary['administrative']], ['Verifikasi dokumen', $summary['verification']], ['Aktivitas pendaftaran', $summary['registration']]] as [$label, $value])
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($value, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($sourceHealth as $source)
                <div class="rounded-lg border {{ $source['available'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} px-4 py-3 text-sm">
                    <span class="font-semibold">{{ $source['label'] }}</span>
                    <span class="ml-1">{{ $source['available'] ? 'Tersedia' : 'Belum tersedia' }}</span>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-xs md:grid-cols-3 xl:grid-cols-6">
            <label class="text-sm font-medium text-gray-700">Mulai<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
            <label class="text-sm font-medium text-gray-700">Sampai<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
            <label class="text-sm font-medium text-gray-700">Sumber<select name="source" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua sumber</option>@foreach ($sourceHealth as $key => $source)<option value="{{ $key }}" @selected(($filters['source'] ?? '') === $key)>{{ $source['label'] }}</option>@endforeach</select></label>
            <label class="text-sm font-medium text-gray-700">Aktivitas<select name="action" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua aktivitas</option>@foreach ($actions as $action)<option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>{{ $action }}</option>@endforeach</select></label>
            <label class="text-sm font-medium text-gray-700">Aktor<select name="actor_id" class="mt-1 w-full rounded-lg border-gray-300 text-sm"><option value="">Semua aktor</option>@foreach ($actors as $actor)<option value="{{ $actor->id }}" @selected((string) ($filters['actor_id'] ?? '') === (string) $actor->id)>{{ $actor->name }}</option>@endforeach</select></label>
            <label class="text-sm font-medium text-gray-700">Pencarian aman<input type="search" name="search" maxlength="80" value="{{ $filters['search'] ?? '' }}" placeholder="Nomor registrasi atau aktor" class="mt-1 w-full rounded-lg border-gray-300 text-sm"></label>
            <div class="flex items-end gap-2 md:col-span-3 xl:col-span-6"><button class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">Terapkan filter</button><a href="{{ route('admin.audit-logs.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Reset</a></div>
        </form>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"><tr><th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Aktor</th><th class="px-5 py-3">Kategori</th><th class="px-5 py-3">Aktivitas</th><th class="px-5 py-3">Objek terkait</th><th class="px-5 py-3">Ringkasan aman</th></tr></thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse ($activities as $activity)
                        <tr class="align-top"><td class="whitespace-nowrap px-5 py-4 text-gray-600">{{ $activity->occurred_at->format('d-m-Y H:i:s') }} WIB</td><td class="px-5 py-4 font-medium text-gray-900">{{ $activity->actor_display }}</td><td class="px-5 py-4 text-gray-600">{{ $activity->source }}</td><td class="px-5 py-4 font-semibold text-gray-900">{{ $activity->event_label }}</td><td class="px-5 py-4 text-gray-600">{{ $activity->subject_type }} #{{ $activity->subject_id }}@if ($activity->registration_number) <span class="block text-xs">{{ $activity->registration_number }}</span>@endif</td><td class="px-5 py-4 text-gray-600">{{ $activity->safe_summary }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">Belum ada aktivitas yang cocok dengan filter yang dipilih.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if ($activities->hasPages())<div class="border-t border-gray-200 px-5 py-4">{{ $activities->withQueryString()->links() }}</div>@endif
        </div>
    </div>
</x-layouts.admin>
