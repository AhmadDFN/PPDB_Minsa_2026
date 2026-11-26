<x-layouts.applicant title="Langkah 3: Data Orang Tua / Wali — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Data Orang Tua & Wali</h1>
            <p class="mt-1 text-sm text-slate-400">Pengisian data Ayah Kandung dan Ibu Kandung wajib diisi. Data Wali bersifat opsional.</p>
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
                'Tidak Bersekolah' => 'Tidak Bersekolah',
                'SD/Sederajat' => 'SD/Sederajat',
                'SMP/Sederajat' => 'SMP/Sederajat',
                'SMA/Sederajat' => 'SMA/Sederajat',
                'D1' => 'D1',
                'D2' => 'D2',
                'D3' => 'D3',
                'D4/S1' => 'D4/S1',
                'S2' => 'Magister (S2)',
                'S3' => 'Doktor (S3)',
                'Lainnya' => 'Lainnya',
            ];

            $incomes = [
                'dibawah 800.000' => 'dibawah 800.000',
                '800.001 - 1.200.000' => '800.001 - 1.200.000',
                '1.200.001 - 1.800.000' => '1.200.001 - 1.800.000',
                '1.800.001 - 2.500.000' => '1.800.001 - 2.500.000',
                '2.500.001 - 3.500.000' => '2.500.001 - 3.500.000',
                '3.500.001 - 4.800.000' => '3.500.001 - 4.800.000',
                '4.800.001 - 6.500.000' => '4.800.001 - 6.500.000',
                '6.500.001 - 10.000.000' => '6.500.001 - 10.000.000',
                '10.000.001 - 20.000.000' => '10.000.001 - 20.000.000',
                'diatas 20.000.001' => 'diatas 20.000.001',
            ];

            $occupations = array_combine(
                ['Tidak bekerja', 'Pensiunan', 'PNS', 'TNI/POLRI', 'Guru/Dosen', 'Wiraswasta', 'Pegawai Swasta', 'Pengacara/Jaksa/Hakim/Notaris', 'Seniman/Pelukis/Artis', 'Dokter/Bidan/Perawat', 'Pilot/Pramugara', 'Buruh(Tani/Pabrik,Bangunan)', 'Politikus', 'Lainnya'],
                ['Tidak bekerja', 'Pensiunan', 'PNS', 'TNI/POLRI', 'Guru/Dosen', 'Wiraswasta', 'Pegawai Swasta', 'Pengacara/Jaksa/Hakim/Notaris', 'Seniman/Pelukis/Artis', 'Dokter/Bidan/Perawat', 'Pilot/Pramugara', 'Buruh(Tani/Pabrik,Bangunan)', 'Politikus', 'Lainnya'],
            );

            $residenceStatuses = [
                'Rumah Orangtua' => 'Rumah Orangtua',
                'Rumah Saudara/Kerabat' => 'Rumah Saudara/Kerabat',
                'Rumah Dinas' => 'Rumah Dinas',
                'Rumah Sewa/Kontrak' => 'Rumah Sewa/Kontrak',
                'Lainnya' => 'Lainnya',
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
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input name="father_birth_place" label="Tempat Lahir Ayah" type="text" :value="old('father_birth_place', $father?->birth_place)" required />
                        <x-forms.input name="father_birth_date" label="Tanggal Lahir Ayah" type="date" :value="old('father_birth_date', $father?->birth_date?->format('Y-m-d'))" required />
                        <x-forms.select
                            name="father_status_label"
                            label="Status Ayah"
                            :selected="old('father_status_label', $father?->status_label ?? 'Masih Hidup')"
                            :options="['Masih Hidup' => 'Masih Hidup', 'Sudah Meninggal' => 'Sudah Meninggal', 'Tidak di ketahui' => 'Tidak diketahui']"
                            required
                        />

                        <x-forms.input
                            name="father_phone"
                            label="Nomor WhatsApp / HP Ayah"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            :value="old('father_phone', $father?->phone)"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-forms.select
                            name="father_education"
                            label="Pendidikan Terakhir"
                            placeholder="-- Pilih Pendidikan --"
                            :selected="old('father_education', $father?->education)"
                            :options="$educations"
                            required
                        />

                        <x-forms.select
                            name="father_occupation"
                            label="Pekerjaan Utama Ayah"
                            :options="$occupations"
                            :selected="old('father_occupation', $father?->occupation)"
                            required
                        />

                        <x-forms.select
                            name="father_monthly_income"
                            label="Penghasilan Rata-rata / Bulan"
                            placeholder="-- Pilih Rentang --"
                            :selected="old('father_monthly_income', $father?->monthly_income)"
                            :options="$incomes"
                            required
                        />
                    </div>
                    <h3 class="pt-4 border-t text-sm font-bold text-neutral-800">Alamat dan tempat tinggal ayah</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @include('applicant.form.partials.parent-address-fields', ['prefix' => 'father', 'record' => $father, 'required' => true, 'studentAddress' => $studentAddress])
                        <x-forms.select name="father_residence_status" label="Status tempat tinggal ayah" :options="$residenceStatuses" :selected="old('father_residence_status', $father?->residence_status)" required />
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
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.input name="mother_birth_place" label="Tempat Lahir Ibu" type="text" :value="old('mother_birth_place', $mother?->birth_place)" required />
                        <x-forms.input name="mother_birth_date" label="Tanggal Lahir Ibu" type="date" :value="old('mother_birth_date', $mother?->birth_date?->format('Y-m-d'))" required />
                        <x-forms.select
                            name="mother_status_label"
                            label="Status Ibu"
                            :selected="old('mother_status_label', $mother?->status_label ?? 'Masih Hidup')"
                            :options="['Masih Hidup' => 'Masih Hidup', 'Sudah Meninggal' => 'Sudah Meninggal', 'Tidak di ketahui' => 'Tidak diketahui']"
                            required
                        />

                        <x-forms.input
                            name="mother_phone"
                            label="Nomor WhatsApp / HP Ibu"
                            type="tel"
                            placeholder="Contoh: 081234567890"
                            :value="old('mother_phone', $mother?->phone)"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-forms.select
                            name="mother_education"
                            label="Pendidikan Terakhir"
                            placeholder="-- Pilih Pendidikan --"
                            :selected="old('mother_education', $mother?->education)"
                            :options="$educations"
                            required
                        />

                        <x-forms.select
                            name="mother_occupation"
                            label="Pekerjaan Utama Ibu"
                            :options="$occupations"
                            :selected="old('mother_occupation', $mother?->occupation)"
                            required
                        />

                        <x-forms.select
                            name="mother_monthly_income"
                            label="Penghasilan Rata-rata / Bulan"
                            placeholder="-- Pilih Rentang --"
                            :selected="old('mother_monthly_income', $mother?->monthly_income)"
                            :options="$incomes"
                            required
                        />
                    </div>
                    <h3 class="pt-4 border-t text-sm font-bold text-neutral-800">Alamat dan tempat tinggal ibu</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @include('applicant.form.partials.parent-address-fields', ['prefix' => 'mother', 'record' => $mother, 'required' => true, 'studentAddress' => $studentAddress])
                        <x-forms.select name="mother_residence_status" label="Status tempat tinggal ibu" :options="$residenceStatuses" :selected="old('mother_residence_status', $mother?->residence_status)" required />
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
                        <x-forms.input name="guardian_birth_place" label="Tempat Lahir Wali" type="text" :value="old('guardian_birth_place', $guardian?->birth_place)" />
                        <x-forms.input name="guardian_birth_date" label="Tanggal Lahir Wali" type="date" :value="old('guardian_birth_date', $guardian?->birth_date?->format('Y-m-d'))" />
                        <x-forms.select name="guardian_status_label" label="Status Wali" :selected="old('guardian_status_label', $guardian?->status_label ?? 'Masih Hidup')" :options="['Masih Hidup'=>'Masih Hidup','Sudah Meninggal'=>'Sudah Meninggal','Tidak di ketahui'=>'Tidak diketahui']" />
                        <x-forms.select name="guardian_education" label="Pendidikan Terakhir Wali" :options="$educations" :selected="old('guardian_education', $guardian?->education)" />
                        <x-forms.select name="guardian_monthly_income" label="Penghasilan Rata-rata Wali" :options="$incomes" :selected="old('guardian_monthly_income', $guardian?->monthly_income)" />
                    </div>
                    <h3 class="pt-4 border-t text-sm font-bold text-neutral-800">Alamat dan tempat tinggal wali</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @include('applicant.form.partials.parent-address-fields', ['prefix' => 'guardian', 'record' => $guardian, 'required' => old('has_guardian', $guardian ? '1' : '0') == '1', 'studentAddress' => $studentAddress])
                        <x-forms.select name="guardian_residence_status" label="Status tempat tinggal wali" :options="$residenceStatuses" :selected="old('guardian_residence_status', $guardian?->residence_status)" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <x-forms.select
                            name="guardian_occupation"
                            label="Pekerjaan Utama Wali"
                            :options="$occupations"
                            :selected="old('guardian_occupation', $guardian?->occupation)"
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
        document.querySelectorAll('[data-copy-address]').forEach((button) => {
            button.addEventListener('click', async () => {
                const prefix = button.dataset.copyAddress;
                document.querySelectorAll(`[data-parent="${prefix}"][data-parent-address]`).forEach((input) => {
                    const studentInput = document.getElementById(input.dataset.parentAddress === 'residence' ? 'city' : input.dataset.parentAddress);
                    const regionName = document.querySelector(`[data-region-group="student"] [data-region-name="${input.dataset.parentAddress}"]`);
                    input.value = studentInput?.value || regionName?.value || input.dataset.studentAddress || '';
                });
                await window.copyStudentRegionToParent(prefix);
            });
        });

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
</x-layouts.applicant>
