<x-layouts.admin title="Kelola Akses Edit Ulang Pendaftaran">
    <x-slot:header>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Akses Edit Ulang Pendaftaran</h1>
            <p class="mt-1 text-sm text-gray-500">Buka akses sementara untuk satu pendaftaran yang telah dikirim dan periode pendaftarannya sudah ditutup.</p>
        </div>
    </x-slot:header>

    @if (session('success'))
        <div class="mb-6">
            <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
        </div>
    @endif

    <x-ui.card class="overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Nomor pendaftaran</th>
                        <th class="px-5 py-3">Calon siswa</th>
                        <th class="px-5 py-3">Periode / program</th>
                        <th class="px-5 py-3">Akses edit ulang</th>
                        <th class="px-5 py-3">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($registrations as $registration)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 font-mono text-xs font-semibold text-gray-800">{{ $registration->registration_number }}</td>
                            <td class="px-5 py-4 font-medium text-gray-800">{{ $registration->applicant?->full_name }}</td>
                            <td class="px-5 py-4 text-gray-600">
                                <span class="block">{{ $registration->admissionPeriod?->academic_year }}</span>
                                <span class="text-xs">{{ $registration->admissionPeriodProgram?->program?->name }}</span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($registration->activeReopenWindow)
                                    <span class="font-semibold text-amber-700">Aktif sampai {{ $registration->activeReopenWindow->expires_at->format('d-m-Y H:i') }}</span>
                                    <p class="mt-1 max-w-xs text-xs text-gray-500">{{ $registration->activeReopenWindow->reason }}</p>
                                @else
                                    <span class="text-gray-500">Tidak aktif</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <a href="{{ route('admin.registrations.reopen.create', $registration) }}" class="font-semibold text-primary-700 hover:underline">
                                    {{ $registration->activeReopenWindow ? 'Kelola / lihat riwayat' : 'Buka akses' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-500">Belum ada pendaftaran terkirim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registrations->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $registrations->links() }}</div>
        @endif
    </x-ui.card>
</x-layouts.admin>
