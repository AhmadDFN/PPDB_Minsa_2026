@extends('layouts.interviewer', ['title' => 'Scan & Cari Calon Siswa'])

@section('content')
<div class="space-y-5">
    {{-- Active Stage Information Card --}}
    @if ($stage)
        <div class="p-4 sm:p-5 rounded-2xl bg-linear-to-r from-emerald-800 to-emerald-950 text-white shadow-md">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-700/80 text-emerald-100 border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Tahap {{ $stage->stage_order }} Aktif
                </span>
                <span class="text-xs text-emerald-200/80 font-medium">
                    @if ($stage->program_scope === 'UNGGULAN')
                        Khusus Program Unggulan
                    @else
                        Semua Program (Reguler & Unggulan)
                    @endif
                </span>
            </div>
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white mb-1">
                {{ $stage->title }}
            </h1>
            <p class="text-xs sm:text-sm text-emerald-100/90 leading-relaxed">
                {{ $stage->description }}
            </p>
        </div>
    @else
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 shadow-xs">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h2 class="text-sm font-bold">Tahap Wawancara Tidak Ditemukan</h2>
            </div>
            <p class="text-xs text-amber-800">
                Akun Anda belum dikonfigurasikan untuk tahap wawancara pada periode aktif saat ini. Silakan hubungi Superadmin.
            </p>
        </div>
    @endif

    {{-- Active In-Progress Session Alert --}}
    @if ($activeSession)
        <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border-2 border-emerald-500 text-emerald-950 shadow-md">
            <div class="flex items-center justify-between gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white animate-pulse">
                    Sedang Berlangsung
                </span>
                <span class="text-xs font-semibold text-emerald-800">
                    Dimulai: {{ $activeSession->started_at?->diffForHumans() }}
                </span>
            </div>
            <div class="mb-3">
                <div class="text-xs text-emerald-700 font-medium">Calon Siswa Aktif:</div>
                <div class="text-base sm:text-lg font-bold text-gray-900">
                    {{ $activeSession->registration->applicant?->full_name ?? 'Calon Siswa' }}
                </div>
                <div class="text-xs text-emerald-800 font-mono mt-0.5">
                    No. Urut: <strong class="text-emerald-900 font-bold text-sm">{{ $activeSession->registration->registration_number ?? '-' }}</strong>
                    &bull; {{ $activeSession->registration->admissionPeriodProgram?->program?->name ?? 'Program' }}
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 pt-2 border-t border-emerald-200">
                <a
                    href="{{ route('interview.session', $activeSession) }}"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-xs transition min-h-11"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                    Lanjutkan Penilaian
                </a>
                <form
                    method="POST"
                    action="{{ route('interview.session.cancel', $activeSession) }}"
                    onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sesi ini? Data penilaian yang belum disimpan akan hilang.');"
                    class="sm:shrink-0"
                >
                    @csrf
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-white hover:bg-red-50 text-red-600 border border-red-200 text-sm font-semibold transition min-h-11"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batalkan Sesi
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Scan & Manual Input Card --}}
    @if ($stage && $stage->is_active)
        <div class="p-5 rounded-2xl bg-white border border-gray-200 shadow-sm space-y-5">
            <div>
                <h2 class="text-base font-bold text-gray-900">Mulai Sesi Wawancara</h2>
                <p class="text-xs text-gray-500">Scan QR Code pada bukti pendaftaran fisik siswa atau masukkan nomor urut manual.</p>
            </div>

            {{-- QR Scanner Section --}}
            <div class="space-y-3">
                <div id="qr-reader" class="hidden overflow-hidden rounded-xl border-2 border-dashed border-emerald-300 bg-gray-50"></div>

                <button
                    type="button"
                    id="toggle-camera-btn"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-sm font-semibold transition min-h-12"
                >
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span id="camera-btn-text">Buka Kamera Scan QR</span>
                </button>
            </div>

            <div class="relative flex items-center justify-center">
                <div class="border-t border-gray-200 w-full"></div>
                <span class="bg-white px-3 text-xs uppercase tracking-wider text-gray-400 font-semibold absolute">
                    Atau Input Manual
                </span>
            </div>

            {{-- Manual Input Form --}}
            <form id="lookup-form" method="POST" action="{{ route('interview.lookup') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="identifier" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                        Nomor Urut Pendaftaran
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="identifier"
                            name="identifier"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Contoh: 270001"
                            value="{{ old('identifier') }}"
                            required
                            class="w-full px-4 py-3 rounded-xl border @error('identifier') border-red-400 bg-red-50/50 @else border-gray-300 bg-white @enderror text-gray-900 font-mono text-xl sm:text-2xl font-bold tracking-wider placeholder:text-gray-300 placeholder:font-sans placeholder:text-base placeholder:font-normal focus:outline-hidden focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition min-h-12"
                        >
                    </div>
                    @error('identifier')
                        <p class="mt-1.5 text-xs text-red-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <button
                    type="submit"
                    id="submit-lookup-btn"
                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base shadow-sm hover:shadow-md transition active:scale-[0.99] min-h-12"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Mulai Wawancara</span>
                </button>
            </form>
        </div>
    @endif

    {{-- Today's Summary Card (Absolute Score Isolation: Count Only) --}}
    <div class="p-5 rounded-2xl bg-white border border-gray-200 shadow-xs flex items-center justify-between">
        <div class="space-y-0.5">
            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa Dinilai Hari Ini</div>
            <div class="text-xs text-gray-400">Total siswa yang telah Anda selesaikan & kunci</div>
        </div>
        <div class="text-right">
            <span class="text-3xl sm:text-4xl font-extrabold text-emerald-700 tracking-tight font-mono">
                {{ $completedTodayCount }}
            </span>
            <span class="text-xs text-gray-400 block font-medium">Siswa</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- html5-qrcode scanner script --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggle-camera-btn');
        const btnText = document.getElementById('camera-btn-text');
        const qrContainer = document.getElementById('qr-reader');
        const inputField = document.getElementById('identifier');
        const lookupForm = document.getElementById('lookup-form');

        let html5QrCode = null;
        let isScanning = false;

        if (toggleBtn && qrContainer && inputField && lookupForm) {
            toggleBtn.addEventListener('click', function () {
                if (!isScanning) {
                    startScan();
                } else {
                    stopScan();
                }
            });
        }

        function startScan() {
            if (typeof Html5Qrcode === 'undefined') {
                alert('Pustaka pemindai QR Code sedang tidak dapat diakses. Silakan ketik nomor pendaftaran secara manual.');
                return;
            }

            qrContainer.classList.remove('hidden');
            btnText.textContent = 'Tutup Kamera';
            isScanning = true;

            html5QrCode = new Html5Qrcode('qr-reader');
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            html5QrCode.start(
                { facingMode: 'environment' },
                config,
                function (decodedText) {
                    // Success callback
                    stopScan();
                    inputField.value = decodedText;
                    lookupForm.submit();
                },
                function (errorMessage) {
                    // Ignore transient frame scan errors
                }
            ).catch(function (err) {
                console.error('QR Scanner error:', err);
                stopScan();
                alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan pada peramban Anda, atau gunakan input manual.');
            });
        }

        function stopScan() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(function () {
                    html5QrCode.clear();
                    qrContainer.classList.add('hidden');
                    btnText.textContent = 'Buka Kamera Scan QR';
                    isScanning = false;
                }).catch(function (err) {
                    console.error('Stop error:', err);
                    qrContainer.classList.add('hidden');
                    btnText.textContent = 'Buka Kamera Scan QR';
                    isScanning = false;
                });
            } else {
                qrContainer.classList.add('hidden');
                btnText.textContent = 'Buka Kamera Scan QR';
                isScanning = false;
            }
        }
    });
</script>
@endpush
