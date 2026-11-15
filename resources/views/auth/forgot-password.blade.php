<x-layouts.app title="Lupa Kata Sandi — PPDB MIN 1 Kota Madiun">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Header Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-50 text-primary-600 shadow-sm border border-primary-100 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-neutral-800 tracking-tight">Pemulihan Kata Sandi</h1>
                <p class="mt-2 text-sm text-neutral-600">Masukkan NIK calon siswa yang terdaftar untuk menerima kode verifikasi OTP</p>
            </div>

            @if (session('warning'))
                <div class="mb-5">
                    <x-ui.alert type="warning" dismissible>{{ session('warning') }}</x-ui.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <form method="POST" action="{{ route('password.send') }}" class="space-y-5" novalidate>
                    @csrf

                    <x-forms.input
                        name="nik"
                        label="NIK Calon Siswa Terdaftar"
                        type="text"
                        maxlength="16"
                        pattern="[0-9]*"
                        inputmode="numeric"
                        placeholder="16 digit NIK calon siswa"
                        required
                        autofocus
                        help="Kode OTP pemulihan akan dikirimkan ke nomor WhatsApp orang tua yang terverifikasi."
                    />

                    <div class="pt-2">
                        <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                            Kirim Kode OTP Pemulihan &rarr;
                        </x-ui.button>
                    </div>
                </form>

                <!-- Help Box for Lost Phone Access -->
                <div class="mt-6 p-4 rounded-xl bg-neutral-50 border border-neutral-200/80 text-xs text-neutral-600 leading-relaxed">
                    <p class="font-semibold text-neutral-800 mb-1">Nomor WhatsApp Tidak Aktif / Hilang?</p>
                    <p>Demi keamanan data pribadi calon siswa, pemulihan akun tanpa nomor telepon terverifikasi hanya dapat dilayani langsung di <strong>Loket PTSP MIN 1 Kota Madiun</strong> dengan membawa Kartu Keluarga dan Akta Kelahiran asli.</p>
                </div>

                <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 underline">
                        &larr; Kembali ke Halaman Masuk
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>

