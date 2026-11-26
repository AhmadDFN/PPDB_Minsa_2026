<x-layouts.applicant title="Langkah 1: Biodata Calon Siswa — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Formulir Pendaftaran Siswa Baru</h1>
            <p class="mt-1 text-sm text-slate-400">Lengkapi data identitas calon siswa sesuai dokumen resmi Akta Kelahiran dan Kartu Keluarga.</p>
        </div>

        <!-- Step Indicator -->
        <x-applicant.form-steps :currentStep="1" />

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6">
                <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
            <!-- NIK & Dynamic Age Informational Banner -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 p-4 rounded-xl bg-primary-50/50 border border-primary-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-neutral-500 font-medium block">NIK Calon Siswa (Username Masuk):</span>
                        <span class="text-sm font-mono font-bold text-neutral-800">{{ $maskedNik }}</span>
                        <span class="text-[11px] text-neutral-400 block">*NIK terkunci demi keamanan data</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-primary-200/60 pt-3 md:pt-0 md:pl-4">
                    <div class="w-10 h-10 rounded-lg bg-secondary-100 text-secondary-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs text-neutral-500 font-medium block">Perhitungan Usia (Per {{ $age['reference_date'] ?? 'Acuan PPDB' }}):</span>
                        <span class="text-sm font-bold text-secondary-800">{{ $age['formatted'] ?? '-' }}</span>
                        <span class="text-[11px] text-neutral-500 block">*Dihitung otomatis tanpa mengubah data asli</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('applicant.biodata.update') }}" class="space-y-6" novalidate>
                @csrf

                <!-- Nama Lengkap -->
                <x-forms.input
                    name="full_name"
                    label="Nama Lengkap Calon Siswa"
                    type="text"
                    :value="old('full_name', $applicant->full_name)"
                    required
                    help="Tuliskan nama lengkap sesuai dengan Akta Kelahiran resmi."
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Jenis Kelamin -->
                    <x-forms.select
                        name="gender"
                        label="Jenis Kelamin"
                        :selected="old('gender', $applicant->gender?->value ?? $applicant->gender)"
                        :options="['L' => 'Laki-laki', 'P' => 'Perempuan']"
                        required
                    />

                    <!-- NISN -->
                    <x-forms.input
                        name="nisn"
                        id="nisn"
                        label="Nomor Induk Siswa Nasional (NISN)"
                        type="text"
                        maxlength="10"
                        placeholder="10 digit NISN"
                        :value="old('nisn', $applicant->nisn)"
                        required
                        help="Isi NISN, atau centang pilihan di bawah jika belum memiliki NISN."
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input name="family_card_number" label="Nomor Kartu Keluarga" type="text" inputmode="numeric" maxlength="16" :value="old('family_card_number', $applicant->family_card_number)" required help="16 digit. Nomor disimpan terenkripsi." />
                    <x-forms.select name="religion" label="Agama" :selected="old('religion', $applicant->religion)" :options="['Islam'=>'Islam','Kristen Protestan'=>'Kristen Protestan','Katolik'=>'Katolik','Hindu'=>'Hindu','Buddha'=>'Buddha','Konghucu'=>'Konghucu']" required />
                </div>

                <x-forms.input name="student_phone" label="No. Handphone Calon Siswa" type="tel" inputmode="tel" autocomplete="tel" :value="old('student_phone', $applicant->student_phone)" required help="Jika belum memiliki nomor sendiri, isi nomor yang dapat dihubungi." />

                <label for="nisn_unavailable" class="flex items-start gap-3 rounded-xl border border-neutral-200 p-4 text-sm text-neutral-700">
                    <input id="nisn_unavailable" name="nisn_unavailable" type="checkbox" value="1" @checked(old('nisn_unavailable', $education?->nisn_unavailable ?? false)) class="mt-1 h-5 w-5 rounded border-neutral-300 text-primary-600" />
                    <span>Siswa belum mempunyai NISN dari asal TK</span>
                </label>
                <p class="-mt-4 text-xs text-neutral-500">Jika belum memiliki NISN, lampiran scan NISN tidak diwajibkan.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.select name="hobby" label="Hobi" :selected="old('hobby', $applicant->hobby)" :options="['Olahraga'=>'Olahraga','Kesenian'=>'Kesenian','Membaca'=>'Membaca','Menulis'=>'Menulis','Jalan-jalan'=>'Jalan-jalan','Lainnya'=>'Lainnya']" required />
                    <x-forms.input name="hobby_other" label="Hobi lainnya" type="text" :value="old('hobby_other', $applicant->hobby_other)" />
                    <x-forms.select name="aspiration" label="Cita-cita" :selected="old('aspiration', $applicant->aspiration)" :options="['PNS'=>'PNS','TNI/POLRI'=>'TNI/POLRI','Guru/Dosen'=>'Guru/Dosen','Dokter'=>'Dokter','Politikus'=>'Politikus','Wiraswasta'=>'Wiraswasta','Seniman/Artis'=>'Seniman/Artis','Ilmuwan'=>'Ilmuwan','Agamawan'=>'Agamawan','Lainnya'=>'Lainnya']" required />
                    <x-forms.input name="aspiration_other" label="Cita-cita lainnya" type="text" :value="old('aspiration_other', $applicant->aspiration_other)" />
                    <x-forms.select name="residence_status" label="Status Tempat Tinggal" :selected="old('residence_status', $applicant->residence_status)" :options="['Rumah orang tua'=>'Rumah orang tua','Rumah Saudara/ kerabat'=>'Rumah Saudara/kerabat','Rumah Dinas'=>'Rumah Dinas','Rumah Sewa / kontrak'=>'Rumah Sewa/kontrak','Lainnya'=>'Lainnya']" required />
                    <x-forms.select name="travel_time" label="Waktu Tempuh" :selected="old('travel_time', $applicant->travel_time)" :options="['1 - 10 menit'=>'1 - 10 menit','10 - 19 menit'=>'10 - 19 menit','20 - 29 menit'=>'20 - 29 menit','30 - 39 menit'=>'30 - 39 menit','> 1 jam'=>'> 1 jam']" required />
                    <x-forms.select name="transportation" label="Transportasi ke Sekolah" :selected="old('transportation', $applicant->transportation)" :options="['Jalan Kaki'=>'Jalan Kaki','Sepeda'=>'Sepeda','Antar Jemput Sekolah'=>'Antar Jemput Sekolah']" required />
                    <x-forms.input name="social_assistance_number" label="Nomor KIP / PKH / KKS" type="text" :value="old('social_assistance_number', $applicant->social_assistance_number)" required help="Jika tidak memiliki kartu, isi Tidak Ada." />
                </div>

                <!-- Tempat & Tanggal Lahir -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="birth_place"
                        label="Tempat Lahir"
                        type="text"
                        :value="old('birth_place', $applicant->birth_place)"
                        required
                    />

                    <x-forms.input
                        name="birth_date"
                        label="Tanggal Lahir"
                        type="date"
                        :value="old('birth_date', $applicant->birth_date?->format('Y-m-d'))"
                        required
                    />
                </div>

                <!-- Anak ke & Jumlah Saudara -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="child_order"
                        label="Anak ke-"
                        type="number"
                        min="1"
                        max="20"
                        placeholder="Contoh: 1"
                        :value="old('child_order', $applicant->child_order)"
                        required
                        help="Urutan kelahiran anak dalam keluarga."
                    />

                    <x-forms.input
                        name="siblings_count"
                        label="Jumlah Saudara Kandung"
                        type="number"
                        min="0"
                        max="20"
                        placeholder="Contoh: 2 (isi 0 jika anak tunggal)"
                        :value="old('siblings_count', $applicant->siblings_count)"
                        required
                        help="Jumlah saudara kandung di luar calon siswa."
                    />
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <a href="{{ route('applicant.dashboard') }}" class="btn btn-outline w-full sm:w-auto justify-center">
                        &larr; Dasbor
                    </a>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <button type="submit" name="nav_action" value="save" class="btn btn-secondary w-full sm:w-auto justify-center font-semibold">
                            Simpan Perubahan
                        </button>
                        <button type="submit" name="nav_action" value="next" class="btn btn-primary w-full sm:w-auto justify-center font-semibold shadow-md">
                            Simpan & Lanjut ke Alamat &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
    @push('scripts')
    <script>
        (() => {
            const checkbox = document.getElementById('nisn_unavailable');
            const nisn = document.getElementById('nisn');
            if (!checkbox || !nisn) return;
            const syncNisnRequirement = () => {
                nisn.required = !checkbox.checked;
                nisn.disabled = checkbox.checked;
            };
            checkbox.addEventListener('change', syncNisnRequirement);
            syncNisnRequirement();
        })();
    </script>
    @endpush
</x-layouts.applicant>
