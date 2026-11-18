@if ($parent)
    <dl class="mt-4 space-y-3 text-sm">
        <div><dt class="text-xs text-neutral-500">Nama</dt><dd class="font-medium text-neutral-800">{{ $parent['name'] }}</dd></div>
        <div><dt class="text-xs text-neutral-500">NIK (tersamar)</dt><dd class="font-mono font-medium text-neutral-800">{{ $parent['nik'] }}</dd></div>
        <div><dt class="text-xs text-neutral-500">Pekerjaan / pendidikan</dt><dd class="font-medium text-neutral-800">{{ $parent['occupation'] ?: '—' }} / {{ $parent['education'] ?: '—' }}</dd></div>
        <div><dt class="text-xs text-neutral-500">Nomor telepon</dt><dd class="font-medium text-neutral-800">{{ $parent['phone'] ?: '—' }}</dd></div>
        <div><dt class="text-xs text-neutral-500">Status</dt><dd class="font-medium text-neutral-800">{{ $parent['is_alive'] ? 'Hidup' : 'Meninggal' }}</dd></div>
    </dl>
@else
    <p class="mt-4 text-sm text-amber-700">Data belum diisi.</p>
@endif
