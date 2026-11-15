<x-layouts.app title="Masuk Administrator — PPDB MIN 1 Kota Madiun">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 bg-neutral-50/50">
        <div class="w-full max-w-md">
            <!-- Header Brand -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-neutral-900 text-white shadow-lg mb-4">
                    <svg class="w-8 h-8 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-neutral-900 tracking-tight">Portal Administrator</h1>
                <p class="mt-1 text-xs uppercase tracking-widest text-neutral-500 font-semibold">Khusus Panitia PPDB MIN 1 Kota Madiun</p>
            </div>

            <!-- Alerts -->
            @if (session('error'))
                <div class="mb-5">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-5">
                    <x-ui.alert type="info" dismissible>{{ session('info') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-xl border-neutral-300 p-6 sm:p-8 bg-white">
                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <!-- Email Admin -->
                    <x-forms.input
                        name="email"
                        label="Alamat Email Administrator"
                        type="email"
                        placeholder="admin@minsatama.sch.id"
                        required
                        autofocus
                    />

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 mb-1.5">
                            Kata Sandi <span class="text-red-500 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="Masukkan kata sandi admin"
                                class="w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none pr-10 {{ $errors->has('password') ? 'border-red-400 bg-red-50/20 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' : 'border-neutral-300 focus:border-neutral-900 focus:ring-2 focus:ring-neutral-900/10' }}"
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

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                class="h-4 w-4 rounded border-gray-300 text-neutral-900 focus:ring-neutral-900"
                            />
                            <span class="text-xs text-neutral-600 font-medium">Ingat saya pada perangkat ini</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full justify-center py-3 text-sm font-bold uppercase tracking-wider text-white bg-neutral-900 hover:bg-neutral-800 rounded-lg shadow-md transition flex items-center gap-2"
                        >
                            <svg class="w-4 h-4 text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Masuk ke Panel Panitia
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                    <a href="{{ route('home') }}" class="text-xs text-neutral-500 hover:text-neutral-700">
                        &larr; Kembali ke Beranda PPDB
                    </a>
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
            btn.classList.toggle('text-neutral-900', isPassword);
        }
    </script>
</x-layouts.app>

