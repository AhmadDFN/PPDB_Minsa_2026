<x-layouts.app title="Langkah 4: Riwayat Pendidikan Asal — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Riwayat Pendidikan Asal</h1>
            <p class="mt-1 text-sm text-neutral-600">Informasi pendidikan pra-sekolah (RA / TK / PAUD) yang pernah ditempuh oleh calon siswa.</p>
        </div>

        <!-- Step Indicator -->
        <x-applicant.form-steps :currentStep="4" />

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6">
                <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
            @php
                $currentLevel = old('education_level', $education?->education_level?->value ?? ($education?->education_level ?? 'tk'));
            @endphp

            <form method="POST" action="{{ route('applicant.education.update') }}" class="space-y-6" novalidate id="educationForm">
                @csrf

                <!-- Pilihan Jenjang Pendidikan Sebelumnya -->
                <div>
                    <label class="block text-sm font-semibold text-neutral-800 mb-2">
                        Jenjang Pendidikan Anak Sebelumnya <span class="text-red-500 font-bold">*</span>
                    </label>
                    <p class="text-xs text-neutral-500 mb-3">Pilih jenjang sekolah pra-dasar yang pernah atau sedang diikuti.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-neutral-50 {{ $currentLevel === 'ra' ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/20' : 'border-neutral-200' }}">
                            <input
                                type="radio"
                                name="education_level"
                                value="ra"
                                class="text-primary-600 focus:ring-primary-500"
                                {{ $currentLevel === 'ra' ? 'checked' : '' }}
                                onchange="toggleSchoolFields(this.value)"
                            />
                            <div>
                                <span class="block text-sm font-bold text-neutral-800">Raudhatul Athfal (RA)</span>
                                <span class="text-xs text-neutral-500">Pendidikan pra-dasar Kemenag</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-neutral-50 {{ $currentLevel === 'tk' ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/20' : 'border-neutral-200' }}">
                            <input
                                type="radio"
                                name="education_level"
                                value="tk"
                                class="text-primary-600 focus:ring-primary-500"
                                {{ $currentLevel === 'tk' ? 'checked' : '' }}
                                onchange="toggleSchoolFields(this.value)"
                            />
                            <div>
                                <span class="block text-sm font-bold text-neutral-800">Taman Kanak-kanak (TK)</span>
                                <span class="text-xs text-neutral-500">Pendidikan pra-dasar Kemendikbud</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-neutral-50 {{ $currentLevel === 'paud' ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/20' : 'border-neutral-200' }}">
                            <input
                                type="radio"
                                name="education_level"
                                value="paud"
                                class="text-primary-600 focus:ring-primary-500"
                                {{ $currentLevel === 'paud' ? 'checked' : '' }}
                                onchange="toggleSchoolFields(this.value)"
                            />
                            <div>
                                <span class="block text-sm font-bold text-neutral-800">PAUD / Kelompok Bermain</span>
                                <span class="text-xs text-neutral-500">Pendidikan anak usia dini</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-neutral-50 {{ $currentLevel === 'other' ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/20' : 'border-neutral-200' }}">
                            <input
                                type="radio"
                                name="education_level"
                                value="other"
                                class="text-primary-600 focus:ring-primary-500"
                                {{ $currentLevel === 'other' ? 'checked' : '' }}
                                onchange="toggleSchoolFields(this.value)"
                            />
                            <div>
                                <span class="block text-sm font-bold text-neutral-800">Lainnya</span>
                                <span class="text-xs text-neutral-500">Homeschooling / Sanggar Belajar</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3.5 border rounded-xl cursor-pointer transition hover:bg-neutral-50 {{ $currentLevel === 'none' ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/20' : 'border-neutral-200' }}">
                            <input
                                type="radio"
                                name="education_level"
                                value="none"
                                class="text-primary-600 focus:ring-primary-500"
                                {{ $currentLevel === 'none' ? 'checked' : '' }}
                                onchange="toggleSchoolFields(this.value)"
                            />
                            <div>
                                <span class="block text-sm font-bold text-neutral-800">Tidak / Belum Sekolah</span>
                                <span class="text-xs text-neutral-500">Langsung mendaftar MI/SD</span>
                            </div>
                        </label>
                    </div>
                    <x-forms.error name="education_level" />
                </div>

                <!-- Bagian Detail Sekolah Asal (Tersembunyi jika Tidak / Belum Sekolah) -->
                <div id="schoolDetailsSection" class="space-y-5 pt-4 border-t border-neutral-100 {{ $currentLevel === 'none' ? 'hidden' : '' }}">
                    <div>
                        <x-forms.input
                            name="school_name"
                            label="Nama RA / TK / PAUD Asal"
                            type="text"
                            placeholder="Contoh: RA Perwanida 1 Kota Madiun"
                            :value="old('school_name', $education?->school_name)"
                            help="Tuliskan nama lengkap lembaga pendidikan anak sebelumnya."
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input
                            name="npsn"
                            label="NPSN Sekolah Asal"
                            type="text"
                            maxlength="10"
                            placeholder="Contoh: 69752310"
                            :value="old('npsn', $education?->npsn)"
                            help="Nomor Pokok Sekolah Nasional (8 digit, opsional jika tidak tahu)."
                        />

                        <x-forms.input
                            name="city"
                            label="Kota / Kabupaten Sekolah Asal"
                            type="text"
                            placeholder="Contoh: Kota Madiun"
                            :value="old('city', $education?->city ?? 'Kota Madiun')"
                            help="Kota atau Kabupaten tempat lembaga asal berada."
                        />
                    </div>
                </div>

                <!-- Info Box saat Tidak Sekolah Dipilih -->
                <div id="noSchoolNotice" class="p-4 rounded-xl bg-neutral-50 border border-neutral-200 text-neutral-700 text-xs leading-relaxed {{ $currentLevel === 'none' ? '' : 'hidden' }}">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-neutral-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Calon siswa yang tidak menempuh pendidikan pra-sekolah formal tetap berhak mendaftar di MIN 1 Kota Madiun sesuai ketentuan usia penerimaan.</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <a href="{{ route('applicant.parents.edit') }}" class="btn btn-outline w-full sm:w-auto justify-center">
                        &larr; Sebelumnya: Orang Tua / Wali
                    </a>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <button type="submit" name="nav_action" value="save" class="btn btn-secondary w-full sm:w-auto justify-center font-semibold">
                            Simpan Perubahan
                        </button>
                        <button type="submit" name="nav_action" value="next" class="btn btn-primary w-full sm:w-auto justify-center font-semibold shadow-md">
                            Simpan & Lanjut ke Pilihan Program &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>

    @push('scripts')
    <script>
        function toggleSchoolFields(level) {
            const schoolSection = document.getElementById('schoolDetailsSection');
            const noSchoolNotice = document.getElementById('noSchoolNotice');
            const schoolNameInput = document.getElementById('school_name');

            if (level === 'none') {
                schoolSection.classList.add('hidden');
                noSchoolNotice.classList.remove('hidden');
                if (schoolNameInput) {
                    schoolNameInput.required = false;
                }
            } else {
                schoolSection.classList.remove('hidden');
                noSchoolNotice.classList.add('hidden');
                if (schoolNameInput) {
                    schoolNameInput.required = true;
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
