<x-layouts.app title="Masuk Calon Siswa — {{ \App\Models\SystemSetting::getInstitutionName() }}">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Header Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 mb-3">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain drop-shadow-xs">
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Masuk Akun Calon Siswa</h1>
                <p class="mt-2 text-sm text-neutral-600">Gunakan NIK calon siswa dan kata sandi yang telah dikirimkan ke WhatsApp</p>
            </div>

            <!-- Notifications -->
            @if (session('success'))
                <div class="mb-5">
                    <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-5">
                    <x-ui.alert type="warning" dismissible>{{ session('warning') }}</x-ui.alert>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5">
                    <x-ui.alert type="info" dismissible>{{ session('info') }}</x-ui.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <form method="POST" action="{{ route('login.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <!-- NIK Calon Siswa -->
                    <x-forms.input
                        name="nik"
                        label="NIK Calon Siswa"
                        type="text"
                        maxlength="16"
                        pattern="[0-9]*"
                        inputmode="numeric"
                        placeholder="16 digit NIK calon siswa"
                        required
                        autofocus
                        help="NIK yang tercantum pada Kartu Keluarga / Akta Kelahiran."
                    />

                    <!-- Kata Sandi -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-medium text-neutral-700">
                                Kata Sandi <span class="text-red-500 font-bold">*</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Masukkan kata sandi akun"
                                class="w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none pr-10 {{ $errors->has('password') ? 'border-red-400 bg-red-50/20 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' : 'border-neutral-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20' }}"
                            />
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('password', this)"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-400 hover:text-neutral-600"
                                aria-label="Lihat kata sandi"
                            >
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <x-forms.error name="password" />
                    </div>

                    <!-- Ingat Saya -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            />
                            <span class="text-xs text-neutral-600 font-medium">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Tombol Masuk -->
                    <div class="pt-2">
                        <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                            Masuk ke Akun Calon Siswa &rarr;
                        </x-ui.button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-neutral-100 flex flex-col gap-2 text-center text-xs text-neutral-600">
                    <div>
                        Belum menerima password sementara dari WhatsApp?
                        <form method="POST" action="{{ route('activation.resend_password') }}" class="inline">
                            @csrf
                            <button type="submit" class="font-semibold text-primary-600 hover:text-primary-700 hover:underline">
                                Kirim ulang ke WhatsApp
                            </button>
                        </form>
                    </div>

                    <div class="pt-2 border-t border-neutral-50 text-sm">
                        Belum memiliki akun pendaftaran?
                        <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 underline underline-offset-2">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.classList.toggle('text-primary-600', isPassword);
        }
    </script>
</x-layouts.app>
