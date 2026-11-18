<x-layouts.admin title="Buka Akses Edit Ulang Pendaftaran">
    <x-slot:header>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.registrations.index') }}" class="text-xs font-semibold text-primary-700 hover:underline">&larr; Kembali ke daftar pendaftaran</a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">Akses Edit Ulang</h1>
                <p class="mt-1 text-sm text-gray-500">Akses berlaku hanya untuk pendaftaran ini dan otomatis berhenti saat kedaluwarsa.</p>
            </div>
        </div>
    </x-slot:header>

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6">
            <x-ui.alert type="danger">
                <ul class="list-inside list-disc space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card title="Rincian Pendaftaran">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-gray-500">Nomor pendaftaran</dt>
                    <dd class="mt-1 break-all font-mono font-semibold text-gray-900">{{ $registration->registration_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Calon siswa</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $registration->applicant?->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Tahun ajaran</dt>
                    <dd class="mt-1 text-gray-800">{{ $registration->admissionPeriod?->academic_year }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Program</dt>
                    <dd class="mt-1 text-gray-800">{{ $registration->admissionPeriodProgram?->program?->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Status</dt>
                    <dd class="mt-1 font-semibold text-green-700">{{ $registration->status->label() }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Dikirim pada</dt>
                    <dd class="mt-1 text-gray-800">{{ $registration->submitted_at?->format('d-m-Y H:i') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card title="Status Akses Saat Ini">
            @if ($registration->activeReopenWindow)
                <div class="space-y-3">
                    <p class="text-sm font-semibold text-amber-700">Aktif sampai {{ $registration->activeReopenWindow->expires_at->format('d-m-Y H:i') }}</p>
                    <p class="text-sm text-gray-700">{{ $registration->activeReopenWindow->reason }}</p>
                    <form method="POST" action="{{ route('admin.registrations.reopen.close', $registration->activeReopenWindow) }}" class="space-y-3 border-t border-gray-100 pt-4">
                        @csrf
                        <label for="close-reason" class="block text-sm font-semibold text-gray-700">Alasan penutupan lebih awal</label>
                        <textarea id="close-reason" name="reason" rows="3" maxlength="1000" required class="form-input w-full">{{ old('reason') }}</textarea>
                        <button type="submit" class="btn btn-danger">Tutup akses sekarang</button>
                    </form>
                </div>
            @else
                <p class="text-sm text-gray-600">Tidak ada akses edit ulang yang sedang berlaku.</p>

                @if (! $registration->admissionPeriod?->isOpen())
                    <form method="POST" action="{{ route('admin.registrations.reopen.store', $registration) }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="reason" class="mb-1 block text-sm font-semibold text-gray-700">Alasan pembukaan akses</label>
                            <textarea id="reason" name="reason" rows="3" maxlength="1000" required class="form-input w-full">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="expires-at" class="mb-1 block text-sm font-semibold text-gray-700">Berlaku sampai</label>
                            <input id="expires-at" type="datetime-local" name="expires_at" value="{{ old('expires_at', now()->addDay()->format('Y-m-d\TH:i')) }}" required class="form-input w-full">
                            @error('expires_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Buka akses edit ulang</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-amber-700">Periode pendaftaran masih terbuka; akses edit ulang khusus tidak diperlukan.</p>
                @endif
            @endif
        </x-ui.card>

        <x-ui.card class="lg:col-span-2" title="Riwayat Pembukaan dan Penutupan">
            @if ($registration->reopenWindows->isEmpty())
                <p class="text-sm text-gray-500">Belum ada riwayat akses edit ulang.</p>
            @else
                <ol class="divide-y divide-gray-100">
                    @foreach ($registration->reopenWindows as $window)
                        <li class="py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Dibuka oleh {{ $window->openedBy?->name ?? 'Akun administrator' }} pada {{ $window->opened_at->format('d-m-Y H:i') }}</p>
                                    <p class="mt-1 text-sm text-gray-600">{{ $window->reason }}</p>
                                </div>
                                <p class="text-xs text-gray-500">Kedaluwarsa {{ $window->expires_at->format('d-m-Y H:i') }}</p>
                            </div>
                            @if ($window->closed_at)
                                <p class="mt-2 text-xs text-gray-500">Ditutup oleh {{ $window->closedBy?->name ?? 'Akun administrator' }} pada {{ $window->closed_at->format('d-m-Y H:i') }} — {{ $window->close_reason }}</p>
                            @elseif ($window->expires_at->lessThanOrEqualTo(now()))
                                <p class="mt-2 text-xs text-gray-500">Akses kedaluwarsa secara otomatis.</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </x-ui.card>
    </div>
</x-layouts.admin>
