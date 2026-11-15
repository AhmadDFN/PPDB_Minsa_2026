<x-layouts.app title="Pendaftaran Akun Baru — PPDB MIN 1 Kota Madiun">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl">
            <!-- Header Brand Card -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 shadow-sm border border-primary-100 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Pendaftaran Akun Baru</h1>
                <p class="mt-2 text-sm text-neutral-600">Sistem Informasi PPDB MIN 1 Kota Madiun</p>
            </div>

            <!-- Notification Messages -->
            @if (session('error'))
                <div class="mb-6">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <div class="bg-primary-50/50 border border-primary-100/80 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-primary-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-primary-900 leading-relaxed">
                            <p class="font-semibold mb-1">Panduan Pendaftaran Awal:</p>
                            <p>Satu akun mewakili satu calon siswa. Masukkan NIK calon siswa sesuai dengan <strong>Kartu Keluarga (KK)</strong> atau <strong>Akta Kelahiran</strong>. NIK akan digunakan sebagai username untuk masuk ke sistem.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <!-- NIK Calon Siswa -->
                    <x-forms.input
                        name="nik"
                        label="Nomor Induk Kependudukan (NIK) Calon Siswa"
                        type="text"
                        maxlength="16"
                        pattern="[0-9]*"
                        inputmode="numeric"
                        placeholder="16 digit NIK calon siswa (contoh: 357301...)"
                        required
                        help="Tepat 16 digit angka yang tertera pada Kartu Keluarga / Akta Kelahiran anak."
                    />

                    <!-- Nama Lengkap Calon Siswa -->
                    <x-forms.input
                        name="full_name"
                        label="Nama Lengkap Calon Siswa"
                        type="text"
                        placeholder="Nama lengkap calon siswa sesuai Akta Kelahiran"
                        required
                        help="Gunakan huruf kapital sesuai akta kelahiran resmi."
                    />

                    <!-- Jenis Kelamin -->
                    <x-forms.select
                        name="gender"
                        label="Jenis Kelamin"
                        placeholder="-- Pilih Jenis Kelamin --"
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        required
                    />

                    <!-- Tempat & Tanggal Lahir -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-forms.input
                            name="birth_place"
                            label="Tempat Lahir"
                            type="text"
                            placeholder="Kota / Kabupaten lahir"
                            required
                        />

                        <x-forms.input
                            name="birth_date"
                            label="Tanggal Lahir"
                            type="date"
                            required
                        />
                    </div>

                    <!-- Nomor Telepon / WhatsApp Orang Tua -->
                    <x-forms.input
                        name="phone"
                        label="Nomor WhatsApp Orang Tua / Wali"
                        type="tel"
                        placeholder="Contoh: 081234567890"
                        required
                        help="Nomor aktif untuk pengiriman kode OTP verifikasi dan informasi pendaftaran."
                    />

                    <!-- Pernyataan Persetujuan -->
                    <div class="pt-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="consent"
                                value="1"
                                {{ old('consent') ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                required
                            />
                            <span class="text-xs text-neutral-600 leading-relaxed">
                                Saya menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan sesuai dokumen resmi, serta menyetujui ketentuan pendaftaran PPDB MIN 1 Kota Madiun. <span class="text-red-500 font-bold">*</span>
                            </span>
                        </label>
                        <x-forms.error name="consent" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                            Lanjut ke Verifikasi OTP &rarr;
                        </x-ui.button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                    <p class="text-sm text-neutral-600">
                        Sudah pernah mendaftar?
                        <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700 underline underline-offset-2">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>

