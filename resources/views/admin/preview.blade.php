<x-layouts.admin title="Pratinjau Layout Admin">
    <x-slot:header>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pratinjau Fondasi Layout Admin</h1>
                <p class="text-sm text-gray-500 mt-1">Halaman uji fondasi tata letak administrator PPDB Minsatama (Fase 1).</p>
            </div>
            <div class="flex items-center gap-3">
                <x-ui.badge variant="success" dot>Fondasi Aktif</x-ui.badge>
                <x-ui.badge variant="primary">Fase 1 Selesai</x-ui.badge>
            </div>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Stat Cards Preview --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pendaftar</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                        👥
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Berkas Terkirim</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 flex items-center justify-center font-bold">
                        ✓
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Draf Pendaftaran</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        ✎
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status Periode</p>
                        <p class="text-sm font-bold text-secondary-600 mt-1">Siap Dikonfigurasi</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-secondary-50 text-secondary-600 flex items-center justify-center font-bold">
                        ⚙
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Component Demonstration Card --}}
        <x-ui.card title="Pengujian Komponen Blade Fondasi" subtitle="Verifikasi elemen antarmuka desain sistem Minsatama">
            <div class="space-y-6">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Varian Tombol (Button)</h4>
                    <div class="flex flex-wrap gap-3">
                        <x-ui.button variant="primary">Tombol Primary</x-ui.button>
                        <x-ui.button variant="secondary">Tombol Secondary</x-ui.button>
                        <x-ui.button variant="accent">Tombol Accent</x-ui.button>
                        <x-ui.button variant="outline">Tombol Outline</x-ui.button>
                        <x-ui.button variant="danger">Tombol Danger</x-ui.button>
                        <x-ui.button variant="ghost">Tombol Ghost</x-ui.button>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Varian Badge Status</h4>
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge variant="primary" dot>Primary</x-ui.badge>
                        <x-ui.badge variant="secondary" dot>Secondary</x-ui.badge>
                        <x-ui.badge variant="accent" dot>Accent</x-ui.badge>
                        <x-ui.badge variant="success" dot>Sukses</x-ui.badge>
                        <x-ui.badge variant="warning" dot>Peringatan</x-ui.badge>
                        <x-ui.badge variant="danger" dot>Bahaya</x-ui.badge>
                        <x-ui.badge variant="info" dot>Informasi</x-ui.badge>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Varian Alert Pemberitahuan</h4>
                    <div class="space-y-3">
                        <x-ui.alert type="info" title="Informasi Sistem">
                            Fondasi proyek Fase 1 telah berhasil diinisialisasi dan siap untuk Fase 2 (Database & Model).
                        </x-ui.alert>
                        <x-ui.alert type="success" title="Verifikasi Berhasil" dismissible>
                            Aset CSS Tailwind v4 dan JavaScript Vanilla berhasil dikompilasi tanpa galat.
                        </x-ui.alert>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>

