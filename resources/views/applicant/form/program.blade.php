<x-layouts.applicant title="Langkah 5: Pilihan Program Pendidikan — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Pilihan Program Pendidikan</h1>
            <p class="mt-1 text-sm text-slate-400">Pilih program peminatan kelas yang dibuka pada periode pendaftaran PPDB aktif saat ini.</p>
        </div>

        <!-- Step Indicator -->
        <x-applicant.form-steps :currentStep="5" />

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6">
                <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6">
                <x-ui.alert type="danger">
                    <ul class="list-disc list-inside text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            </div>
        @endif

        @if (! $activePeriod)
            <!-- Tidak Ada Periode Aktif -->
            <x-ui.card class="p-8 text-center border-amber-200 bg-amber-50/50">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-neutral-800">Periode Pendaftaran Sedang Ditutup</h3>
                <p class="text-sm text-neutral-600 max-w-md mx-auto mt-2">
                    Saat ini belum ada periode pendaftaran PPDB yang berstatus aktif di MIN 1 Kota Madiun. Silakan pantau pengumuman resmi madrasah.
                </p>
                <div class="mt-6">
                    <a href="{{ route('applicant.dashboard') }}" class="btn btn-primary inline-flex">
                        Kembali ke Dasbor
                    </a>
                </div>
            </x-ui.card>
        @elseif (! $canEditProgram)
            <x-ui.card class="border-amber-200 bg-amber-50/50 p-6">
                <h2 class="font-bold text-neutral-800">Program Tidak Dapat Diubah</h2>
                <p class="mt-2 text-sm text-neutral-700">Pilihan program menjadi bagian dari identitas pendaftaran dan tidak dapat diubah setelah pendaftaran dikirim.</p>
                <a href="{{ route('applicant.registration.review') }}" class="btn btn-primary mt-4 inline-flex">Kembali ke Tinjauan Pendaftaran</a>
            </x-ui.card>
        @else
            <!-- Form Pemilihan Program -->
            <form method="POST" action="{{ route('applicant.program.update') }}" class="space-y-6" novalidate>
                @csrf

                <!-- Banner Periode Pendaftaran Aktif -->
                <x-ui.card class="p-6 border-primary-200 bg-primary-50/20">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 mb-2">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Periode Terbuka
                            </span>
                            <h2 class="text-lg sm:text-xl font-bold text-neutral-800">
                                {{ $activePeriod->name }}
                            </h2>
                            <p class="text-xs text-neutral-600 mt-0.5">
                                Tahun Ajaran {{ $activePeriod->academic_year }} &bull; Tanggal Acuan Usia:
                                <strong class="text-neutral-800">{{ $activePeriod->age_reference_date?->translatedFormat('d F Y') ?? $activePeriod->age_reference_date?->format('d F Y') }}</strong>
                            </p>
                        </div>
                        <div class="text-left sm:text-right shrink-0">
                            <p class="text-xs text-neutral-500">Jadwal Pendaftaran:</p>
                            <p class="text-xs font-semibold text-neutral-800">
                                {{ $activePeriod->start_date?->translatedFormat('d M Y') ?? $activePeriod->start_date?->format('d M Y') }} s/d {{ $activePeriod->end_date?->translatedFormat('d M Y') ?? $activePeriod->end_date?->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Pilihan Program -->
                <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
                    <h3 class="text-base font-bold text-neutral-800 mb-1">
                        Pilih Salah Satu Program Pendidikan <span class="text-red-500 font-bold">*</span>
                    </h3>
                    <p class="text-xs text-neutral-500 mb-6">
                        Setiap calon siswa hanya dapat memilih 1 (satu) program peminatan dalam satu periode pendaftaran.
                    </p>

                    @php
                        $selectedId = (int) old('admission_period_program_id', $registration?->admission_period_program_id);
                        $periodPrograms = $activePeriod->periodPrograms->where('is_active', true);
                    @endphp

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse ($periodPrograms as $periodProgram)
                            @php
                                $program = $periodProgram->program;
                                $isSelected = $selectedId === $periodProgram->id;
                            @endphp
                            <label class="relative flex flex-col p-5 border-2 rounded-2xl cursor-pointer transition hover:border-primary-400 hover:shadow-md {{ $isSelected ? 'border-primary-600 bg-primary-50/20 ring-2 ring-primary-500/20' : 'border-neutral-200 bg-white' }}">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl {{ $isSelected ? 'bg-primary-600 text-white' : 'bg-neutral-100 text-neutral-600' }} flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ strtoupper(substr($program->code, 0, 3)) }}
                                        </div>
                                        <div>
                                            <h4 class="text-base font-bold text-neutral-800">{{ $program->name }}</h4>
                                            <span class="inline-block text-[11px] font-semibold text-neutral-500 uppercase tracking-wider">
                                                Kode: {{ $program->code }}
                                            </span>
                                        </div>
                                    </div>
                                    <input
                                        type="radio"
                                        name="admission_period_program_id"
                                        value="{{ $periodProgram->id }}"
                                        class="h-5 w-5 text-primary-600 border-neutral-300 focus:ring-primary-500 mt-1"
                                        {{ $isSelected ? 'checked' : '' }}
                                        required
                                    />
                                </div>

                                <p class="text-xs text-neutral-600 leading-relaxed flex-1">
                                    {{ $program->description ?? 'Program pendidikan unggulan MIN 1 Kota Madiun yang berfokus pada pengembangan akademik dan karakter Qurani.' }}
                                </p>

                                @if ($periodProgram->notes)
                                    <div class="mt-3 pt-3 border-t border-neutral-100 text-[11px] text-neutral-500 italic">
                                        Catatan: {{ $periodProgram->notes }}
                                    </div>
                                @endif
                            </label>
                        @empty
                            <div class="col-span-2 p-6 text-center text-sm text-neutral-500 bg-neutral-50 rounded-xl">
                                Belum ada program pendidikan yang dibuka pada periode ini.
                            </div>
                        @endforelse
                    </div>

                    <x-forms.error name="admission_period_program_id" />

                    <!-- Catatan Status Draf -->
                    <div class="mt-6 p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-xs text-blue-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="leading-relaxed">
                            <strong>Status Pendaftaran Sementara (DRAFT):</strong> Menyimpan pilihan program ini akan membuat draf formulir pendaftaran. Anda masih dapat mengubah pilihan program selama pendaftaran belum dikunci/diserahkan secara final pada tahap selanjutnya.
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-6 mt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <a href="{{ route('applicant.education.edit') }}" class="btn btn-outline w-full sm:w-auto justify-center">
                            &larr; Sebelumnya: Riwayat Pendidikan
                        </a>

                        <button type="submit" class="btn btn-primary w-full sm:w-auto justify-center font-semibold shadow-md">
                            Simpan Pilihan Program & Selesai &rarr;
                        </button>
                    </div>
                </x-ui.card>
            </form>
        @endif
    </div>
</x-layouts.applicant>
