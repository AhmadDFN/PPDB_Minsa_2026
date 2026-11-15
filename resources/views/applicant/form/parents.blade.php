<x-layouts.app title="Langkah 3: Data Orang Tua / Wali — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Data Orang Tua & Wali</h1>
            <p class="mt-1 text-sm text-neutral-600">Pengisian data Ayah Kandung dan Ibu Kandung wajib diisi. Data Wali bersifat opsional.</p>
        </div>

        <!-- Step Indicator -->
        <x-applicant.form-steps :currentStep="3" />

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6">
                <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
            </div>
        @endif

        @php
            $educations = [
                'Tidak Sekolah' => 'Tidak Sekolah',
                'SD / MI' => 'SD / MI / Sederajat',
                'SMP / MTs' => 'SMP / MTs / Sederajat',
                'SMA / MA / SMK' => 'SMA / MA / SMK / Sederajat',
                'D1 / D2 / D3' => 'Diploma (D1 / D2 / D3)',
                'S1 / D4' => 'Sarjana (S1 / D4)',
                'S2' => 'Magister (S2)',
                'S3' => 'Doktor (S3)',
            ];

            $incomes = [
                'Tidak Berpenghasilan' => 'Tidak Berpenghasilan',
                '< 1.000.000' => 'Kurang dari Rp 1.000.000',
                '1.000.000 - 3.000.000' => 'Rp 1.000.000 - Rp 3.000.000',
                '3.000.000 - 5.000.000' => 'Rp 3.000.000 - Rp 5.000.000',
                '5.000.000 - 10.000.000' => 'Rp 5.000.000 - Rp 10.000.000',
                '> 10.000.000' => 'Lebih dari Rp 10.000.000',
            ];
        @endphp

        <form method="POST" action="{{ route('applicant.parents.update') }}" class="space-y-8" novalidate>
            @csrf

            <!-- ================= BAGIAN 1: DATA AYAH KANDUNG ================= -->
            <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-neutral-100">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-sm shrink-0">
                        1
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-neutral-800">Data Ayah Kandung</h2>
                        <p class="text-xs text-neutral-500">Wajib diisi sesuai dokumen Kartu Keluarga.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input
                            name="father_name"
                            label="Nama Lengkap Ayah"
                            type="text"
                            :value="old('father_name', $father?->name)"
                            required
                        />

                        <x-forms.input
                            name="father_nik"
                            label="NIK Ayah"
                            type="text"
                            maxlength="16"
                            placeholder="16 digit NIK ayah"
                            :value="old('father_nik', $father?->nik)"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.select
                            name="father_is_alive"
                            label="Status Keberadaan Ayah"
                            :selected="old('father_is_alive', $father ? ($father->is_alive ? '1' : '0') : '1')"
                            :options="['1' => 'Masih Hidup', '0' => 'Sudah Meninggal']"
                            required
                        />

                        <x-forms.input
                            name="father_phone"
                            label="Nomor WhatsApp / HP Ayah"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            :value="old('father_phone', $father?->phone)"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-forms.select
                            name="father_education"
                            label="Pendidikan Terakhir"
                            placeholder="-- Pilih Pendidikan --"
                            :selected="old('father_education', $father?->education)"
                            :options="$educations"
                        />

                        <x-forms.input
                            name="father_occupation"
                            label="Pekerjaan Ayah"
                            type="text"
                            placeholder="Contoh: Karyawan Swasta / PNS / Wiraswasta"
                            :value="old('father_occupation', $father?->occupation)"
                            required
                        />

                        <x-forms.select
                            name="father_monthly_income"
                            label="Penghasilan Rata-rata / Bulan"
                            placeholder="-- Pilih Rentang --"
                            :selected="old('father_monthly_income', $father?->monthly_income)"
                            :options="$incomes"
                        />
                    </div>
                </div>
            </x-ui.card>

            <!-- ================= BAGIAN 2: DATA IBU KANDUNG ================= -->
            <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-neutral-100">
                    <div class="w-10 h-10 rounded-xl bg-secondary-100 text-secondary-700 flex items-center justify-center font-bold text-sm shrink-0">
                        2
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-neutral-800">Data Ibu Kandung</h2>
                        <p class="text-xs text-neutral-500">Wajib diisi sesuai dokumen Kartu Keluarga.</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input
                            name="mother_name"
                            label="Nama Lengkap Ibu"
                            type="text"
                            :value="old('mother_name', $mother?->name)"
                            required
                        />

                        <x-forms.input
                            name="mother_nik"
                            label="NIK Ibu"
                            type="text"
                            maxlength="16"
                            placeholder="16 digit NIK ibu"
                            :value="old('mother_nik', $mother?->nik)"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.select
                            name="mother_is_alive"
                            label="Status Keberadaan Ibu"
                            :selected="old('mother_is_alive', $mother ? ($mother->is_alive ? '1' : '0') : '1')"
                            :options="['1' => 'Masih Hidup', '0' => 'Sudah Meninggal']"
                            required
                        />

                        <x-forms.input
                            name="mother_phone"
                            label="Nomor WhatsApp / HP Ibu"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            :value="old('mother_phone', $mother?->phone)"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-forms.select
                            name="mother_education"
                            label="Pendidikan Terakhir"
                            placeholder="-- Pilih Pendidikan --"
                            :selected="old('mother_education', $mother?->education)"
                            :options="$educations"
                        />

                        <x-forms.input
                            name="mother_occupation"
                            label="Pekerjaan Ibu"
                            type="text"
                            placeholder="Contoh: Ibu Rumah Tangga / Guru / PNS"
                            :value="old('mother_occupation', $mother?->occupation)"
                            required
                        />

                        <x-forms.select
                            name="mother_monthly_income"
                            label="Penghasilan Rata-rata / Bulan"
                            placeholder="-- Pilih Rentang --"
                            :selected="old('mother_monthly_income', $mother?->monthly_income)"
                            :options="$incomes"
                        />
                    </div>
                </div>
            </x-ui.card>

            <!-- ================= BAGIAN 3: DATA WALI (OPSIONAL) ================= -->
            <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-neutral-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-accent-100 text-neutral-800 flex items-center justify-center font-bold text-sm shrink-0">
                            3
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-neutral-800">Data Wali Calon Siswa (Opsional)</h2>
                            <p class="text-xs text-neutral-500">Diisi jika calon siswa diasuh atau tinggal bersama wali selain orang tua kandung.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl bg-neutral-50 border border-neutral-200 hover:bg-neutral-100/70 transition">
                        <input
                            type="checkbox"
                            name="has_guardian"
                            id="has_guardian"
                            value="1"
                            {{ old('has_guardian', $guardian ? '1' : '0') == '1' ? 'checked' : '' }}
                            onchange="toggleGuardianSection(this.checked)"
                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                        />
                        <span class="text-sm font-semibold text-neutral-800">
                            Calon siswa tinggal bersama atau diasuh oleh Wali
                        </span>
                    </label>
                </div>

                <div id="guardian_section" class="space-y-5 pt-2 {{ old('has_guardian', $guardian ? '1' : '0') == '1' ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input
                            name="guardian_name"
                            label="Nama Lengkap Wali"
                            type="text"
                            :value="old('guardian_name', $guardian?->name)"
                        />

                        <x-forms.input
                            name="guardian_nik"
                            label="NIK Wali"
                            type="text"
                            maxlength="16"
                            placeholder="16 digit NIK wali"
                            :value="old('guardian_nik', $guardian?->nik)"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input
                            name="guardian_occupation"
                            label="Pekerjaan Wali"
                            type="text"
                            placeholder="Pekerjaan wali"
                            :value="old('guardian_occupation', $guardian?->occupation)"
                        />

                        <x-forms.input
                            name="guardian_phone"
                            label="Nomor WhatsApp / HP Wali"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            :value="old('guardian_phone', $guardian?->phone)"
                        />
                    </div>
                </div>
            </x-ui.card>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <a href="{{ route('applicant.address.edit') }}" class="btn btn-outline w-full sm:w-auto justify-center">
                    &larr; Sebelumnya: Alamat
                </a>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <button type="submit" name="nav_action" value="save" class="btn btn-secondary w-full sm:w-auto justify-center font-semibold">
                        Simpan Perubahan
                    </button>
                    <button type="submit" name="nav_action" value="next" class="btn btn-primary w-full sm:w-auto justify-center font-semibold shadow-md">
                        Simpan & Lanjut ke Pendidikan &rarr;
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleGuardianSection(show) {
            const el = document.getElementById('guardian_section');
            if (el) {
                if (show) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }
        }
    </script>
</x-layouts.app>
