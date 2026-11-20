<x-layouts.app title="Ganti Kata Sandi — PPDB Minsatama">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            {{-- Header Brand --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 mb-3">
                    <img src="{{ asset('images/logo-minsatama.png') }}" alt="Logo MIN 1 Kota Madiun" class="w-full h-full object-contain drop-shadow-xs">
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Ganti Kata Sandi</h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Anda dapat memperbarui kata sandi akun pendaftaran Anda kapan saja. Silakan masukkan kata sandi baru di bawah ini.
                </p>
            </div>

            {{-- Flash Alerts --}}
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

            @if ($errors->any())
                <div class="mb-5">
                    <x-ui.alert type="danger" dismissible>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                </div>
            @endif

            {{-- Form Card --}}
            <x-ui.card class="shadow-xl border-neutral-200/80 p-6 sm:p-8 bg-white">
                <form method="POST" action="{{ route('applicant.password.change.update') }}" class="space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="password" class="block text-sm font-semibold text-neutral-800 mb-1.5">
                            Kata Sandi Baru <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 text-neutral-900 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition @error('password') border-red-500 ring-1 ring-red-500 @enderror"
                            placeholder="Minimal 8 karakter"
                            required
                            autocomplete="new-password"
                        >
                        <p class="mt-1 text-xs text-neutral-500">Gunakan kombinasi huruf dan angka agar lebih aman.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-neutral-800 mb-1.5">
                            Ulangi Kata Sandi Baru <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="w-full px-4 py-2.5 rounded-xl border border-neutral-300 text-neutral-900 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                            placeholder="Ketik ulang kata sandi baru"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white bg-primary-600 hover:bg-primary-700 shadow-md transition active:scale-[0.99] cursor-pointer"
                        >
                            Simpan Kata Sandi & Lanjut ke Dasbor
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-5 border-t border-neutral-100 flex items-center justify-between text-xs text-neutral-500">
                    <a href="{{ route('applicant.dashboard') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold hover:underline flex items-center gap-1">
                        &larr; Kembali ke Dasbor
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold hover:underline">
                            Keluar
                        </button>
                    </form>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
