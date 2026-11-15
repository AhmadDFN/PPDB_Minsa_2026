<x-layouts.app title="Buat Kata Sandi — PPDB MIN 1 Kota Madiun">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-accent-100 text-neutral-800 shadow-sm border border-accent-300 mb-4">
                    <svg class="w-8 h-8 text-neutral-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-neutral-800 tracking-tight">Buat Kata Sandi Akun</h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Langkah terakhir aktivasi akun calon siswa:
                    <strong class="text-neutral-800 block">{{ $user->name ?? 'Calon Siswa' }}</strong>
                </p>
            </div>

            <!-- Flash alerts -->
            @if (session('success'))
                <div class="mb-5">
                    <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <form method="POST" action="{{ route('activation.password.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <!-- Kata Sandi Baru -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 mb-1.5">
                            Kata Sandi Baru <span class="text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                minlength="8"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none pr-10 {{ $errors->has('password') ? 'border-red-400 bg-red-50/20 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' : 'border-neutral-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20' }}"
                            />
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('password', this)"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-400 hover:text-neutral-600"
                                aria-label="Lihat kata sandi"
                            >
                                <svg class="w-5 h-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-neutral-500">Gunakan kombinasi minimal 8 karakter.</p>
                        <x-forms.error name="password" />
                    </div>

                    <!-- Konfirmasi Kata Sandi -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 mb-1.5">
                            Konfirmasi Kata Sandi <span class="text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                minlength="8"
                                placeholder="Ulangi kata sandi baru"
                                class="w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none pr-10 border-neutral-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20"
                            />
                            <button
                                type="button"
                                onclick="togglePasswordVisibility('password_confirmation', this)"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-neutral-400 hover:text-neutral-600"
                                aria-label="Lihat konfirmasi kata sandi"
                            >
                                <svg class="w-5 h-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                            Aktifkan Akun & Selesai &rarr;
                        </x-ui.button>
                    </div>
                </form>
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

