<x-layouts.app title="Pendaftaran Akun Baru — {{ \App\Models\SystemSetting::getInstitutionName() }}">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl">
            <!-- Header Brand Card -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 mb-3">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain drop-shadow-xs">
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Pendaftaran Akun Baru</h1>
                <p class="mt-2 text-sm text-neutral-600">{{ \App\Models\SystemSetting::getInstitutionName() }}</p>
            </div>

            <!-- Notification Messages -->
            @if (session('error'))
                <div class="mb-6">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-6">
                    <x-ui.alert type="warning" dismissible>{{ session('warning') }}</x-ui.alert>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6">
                    <x-ui.alert type="info" dismissible>{{ session('info') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <div class="bg-primary-50/50 border border-primary-100/80 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-primary-700 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-primary-900 leading-relaxed space-y-1">
                            <p class="font-semibold">Panduan Pendaftaran Awal:</p>
                            <p>Cukup masukkan NIK dan nama lengkap calon siswa sesuai <strong>Kartu Keluarga (KK) / Akta Kelahiran</strong>, serta nomor WhatsApp aktif.</p>
                            <p class="text-primary-800">Setelah kode OTP WhatsApp berhasil diverifikasi, sistem akan <strong>otomatis membuat kata sandi sementara</strong> dan mengirimkannya ke WhatsApp Anda.</p>
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
                        help="Gunakan nama lengkap resmi sesuai dokumen kependudukan."
                    />

                    <!-- Nomor WhatsApp Orang Tua / Wali -->
                    <x-forms.input
                        name="phone"
                        label="Nomor WhatsApp Orang Tua / Wali"
                        type="tel"
                        placeholder="Contoh: 081234567890"
                        required
                        help="Nomor WhatsApp aktif untuk menerima kode OTP verifikasi dan kata sandi masuk."
                    />

                    <!-- Pernyataan Persetujuan Kebenaran Data -->
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
                                Saya selaku orang tua/wali menyatakan bahwa seluruh data yang diisikan adalah benar dan dapat dipertanggungjawabkan. <span class="text-red-500 font-bold">*</span>
                            </span>
                        </label>
                        <x-forms.error name="consent" />
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-3">
                        <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                            Daftar & Kirim Kode OTP &rarr;
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
