<x-layouts.app title="Verifikasi Kode OTP — PPDB MIN 1 Kota Madiun">
    <div class="min-h-[calc(100vh-16rem)] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-secondary-50 text-secondary-600 shadow-sm border border-secondary-100 mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-extrabold text-neutral-800 tracking-tight">Verifikasi Nomor WhatsApp</h1>
                <p class="mt-2 text-sm text-neutral-600">
                    Masukkan 6 digit kode OTP yang dikirimkan ke nomor WhatsApp:
                </p>
                <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                    {{ $maskedPhone ?? '08**********' }}
                </div>
            </div>

            <!-- Flash alerts -->
            @if (session('success'))
                <div class="mb-5">
                    <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5">
                    <x-ui.alert type="danger" dismissible>{{ session('error') }}</x-ui.alert>
                </div>
            @endif

            <x-ui.card class="shadow-lg border-neutral-200/80 p-6 sm:p-8">
                <form method="POST" action="{{ route('activation.verify') }}" class="space-y-6" novalidate>
                    @csrf

                    <div>
                        <label for="otp" class="block text-sm font-medium text-neutral-700 text-center mb-2">
                            Kode Verifikasi (OTP)
                        </label>
                        <input
                            type="text"
                            name="otp"
                            id="otp"
                            maxlength="6"
                            pattern="[0-9]*"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            placeholder="······"
                            required
                            autofocus
                            class="w-full text-center text-3xl font-mono tracking-[0.5em] py-3 px-4 border rounded-xl shadow-sm transition focus:outline-none focus:ring-2 {{ $errors->has('otp') ? 'border-red-400 bg-red-50/30 text-red-900 focus:border-red-500 focus:ring-red-500/20' : 'border-neutral-300 focus:border-primary-600 focus:ring-primary-600/20' }}"
                        />
                        <x-forms.error name="otp" />
                        <p class="mt-2 text-xs text-neutral-500 text-center">
                            Kode berlaku selama 5 menit. Jangan berikan kode ini kepada siapapun.
                        </p>
                    </div>

                    <x-ui.button type="submit" variant="primary" class="w-full justify-center py-3 text-base shadow-md font-semibold">
                        Verifikasi Kode OTP
                    </x-ui.button>
                </form>

                <!-- Resend OTP Section -->
                <div class="mt-6 pt-6 border-t border-neutral-100 text-center">
                    <p class="text-xs text-neutral-500 mb-2">Belum menerima kode OTP?</p>
                    <form method="POST" action="{{ route('activation.resend') }}">
                        @csrf
                        <button
                            type="submit"
                            id="btn-resend"
                            class="text-sm font-semibold text-primary-600 hover:text-primary-700 hover:underline transition"
                        >
                            Kirim Ulang Kode OTP
                        </button>
                    </form>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ route('register') }}" class="text-xs text-neutral-500 hover:text-neutral-700 underline">
                        &larr; Nomor salah? Ulangi pendaftaran
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>

