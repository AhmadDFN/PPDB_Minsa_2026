<x-layouts.applicant title="Langkah 2: Alamat Domisili — PPDB MIN 1 Kota Madiun">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Alamat Domisili Tempat Tinggal</h1>
            <p class="mt-1 text-sm text-slate-400">Isikan alamat domisili calon siswa yang ditempati saat ini sesuai Kartu Keluarga.</p>
        </div>

        <!-- Step Indicator -->
        <x-applicant.form-steps :currentStep="2" />

        <!-- Alerts -->
        @if (session('success'))
            <div class="mb-6">
                <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
            </div>
        @endif

        <x-ui.card class="p-6 sm:p-8 border-neutral-200/80 shadow-md">
            <form method="POST" action="{{ route('applicant.address.update') }}" class="space-y-6" novalidate>
                @csrf

                <!-- Alamat Jalan / Dusun -->
                <div>
                    <label for="address_line" class="block text-sm font-medium text-neutral-700 mb-1.5">
                        Alamat Jalan / Dusun / Lingkungan <span class="text-red-500 font-bold">*</span>
                    </label>
                    <textarea
                        name="address_line"
                        id="address_line"
                        rows="3"
                        required
                        placeholder="Contoh: Jl. Pahlawan No. 45, Dusun Krajan"
                        class="w-full rounded-lg px-4 py-2.5 text-sm transition focus:outline-none {{ $errors->has('address_line') ? 'border-red-400 bg-red-50/20 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' : 'border-neutral-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20' }}"
                    >{{ old('address_line', $address?->address_line) }}</textarea>
                    <x-forms.error name="address_line" />
                </div>

                <!-- RT / RW -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="rt"
                        label="RT (Rukun Tetangga)"
                        type="text"
                        maxlength="5"
                        placeholder="Contoh: 002"
                        :value="old('rt', $address?->rt)"
                        help="Dapat memuat angka 0 di depan (misal: 003)."
                    />

                    <x-forms.input
                        name="rw"
                        label="RW (Rukun Warga)"
                        type="text"
                        maxlength="5"
                        placeholder="Contoh: 005"
                        :value="old('rw', $address?->rw)"
                        help="Dapat memuat angka 0 di depan (misal: 001)."
                    />
                </div>

                <!-- Kelurahan & Kecamatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="kelurahan"
                        label="Kelurahan / Desa"
                        type="text"
                        placeholder="Nama Kelurahan / Desa"
                        :value="old('kelurahan', $address?->kelurahan)"
                        required
                    />

                    <x-forms.input
                        name="kecamatan"
                        label="Kecamatan"
                        type="text"
                        placeholder="Nama Kecamatan"
                        :value="old('kecamatan', $address?->kecamatan)"
                        required
                    />
                </div>

                <!-- Kota/Kabupaten & Provinsi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="city"
                        label="Kota / Kabupaten"
                        type="text"
                        placeholder="Contoh: Kota Madiun / Kab. Madiun"
                        :value="old('city', $address?->city ?? 'Kota Madiun')"
                        required
                    />

                    <x-forms.input
                        name="province"
                        label="Provinsi"
                        type="text"
                        placeholder="Nama Provinsi"
                        :value="old('province', $address?->province ?? 'Jawa Timur')"
                        required
                    />
                </div>

                <!-- Kode Pos & Estimasi Jarak -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-forms.input
                        name="postal_code"
                        label="Kode Pos"
                        type="text"
                        maxlength="10"
                        placeholder="Contoh: 63121"
                        :value="old('postal_code', $address?->postal_code)"
                    />

                    <x-forms.input
                        name="distance_to_school_km"
                        label="Perkiraan Jarak ke Madrasah (KM)"
                        type="number"
                        step="0.1"
                        min="0"
                        placeholder="Contoh: 1.5"
                        :value="old('distance_to_school_km', $address?->distance_to_school_km)"
                        help="Estimasi jarak rumah ke MIN 1 Kota Madiun (opsional)."
                    />
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <a href="{{ route('applicant.biodata.edit') }}" class="btn btn-outline w-full sm:w-auto justify-center">
                        &larr; Sebelumnya: Biodata
                    </a>

                    <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                        <button type="submit" name="nav_action" value="save" class="btn btn-secondary w-full sm:w-auto justify-center font-semibold">
                            Simpan Perubahan
                        </button>
                        <button type="submit" name="nav_action" value="next" class="btn btn-primary w-full sm:w-auto justify-center font-semibold shadow-md">
                            Simpan & Lanjut ke Data Orang Tua &rarr;
                        </button>
                    </div>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.applicant>
