<div class="space-y-4" data-parent-address-group="{{ $prefix }}">
    <button type="button" class="btn btn-outline min-h-11" data-copy-address="{{ $prefix }}">Salin dari Alamat Siswa</button>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-forms.input name="{{ $prefix }}_residence" label="Domisili" type="text" :value="old($prefix.'_residence', $record?->residence)" :required="$required" data-parent-address="residence" :data-parent="$prefix" :data-student-address="$studentAddress?->city" />
        <x-forms.input name="{{ $prefix }}_address_line" label="Jalan / alamat lengkap" type="text" :value="old($prefix.'_address_line', $record?->address_line)" :required="$required" data-parent-address="address_line" :data-parent="$prefix" :data-student-address="$studentAddress?->address_line" />
        <x-forms.input name="{{ $prefix }}_rt" label="RT" type="text" maxlength="5" :value="old($prefix.'_rt', $record?->rt)" :required="$required" data-parent-address="rt" :data-parent="$prefix" :data-student-address="$studentAddress?->rt" />
        <x-forms.input name="{{ $prefix }}_rw" label="RW" type="text" maxlength="5" :value="old($prefix.'_rw', $record?->rw)" :required="$required" data-parent-address="rw" :data-parent="$prefix" :data-student-address="$studentAddress?->rw" />
    </div>
    <x-applicant.region-selects
        prefix="{{ $prefix }}_"
        :label="'Wilayah domisili '.($prefix === 'father' ? 'ayah' : ($prefix === 'mother' ? 'ibu' : 'wali'))"
        :required="$required"
        :codes="['province' => old($prefix.'_province_code', $record?->province_code), 'city' => old($prefix.'_city_code', $record?->city_code), 'kecamatan' => old($prefix.'_district_code', $record?->district_code), 'kelurahan' => old($prefix.'_village_code', $record?->village_code)]"
        :names="['province' => old($prefix.'_province', $record?->province), 'city' => old($prefix.'_city', $record?->city), 'kecamatan' => old($prefix.'_kecamatan', $record?->kecamatan), 'kelurahan' => old($prefix.'_kelurahan', $record?->kelurahan)]"
    />
    <x-forms.input name="{{ $prefix }}_postal_code" label="Kode Pos" type="text" maxlength="5" inputmode="numeric" :value="old($prefix.'_postal_code', $record?->postal_code)" :required="$required" data-parent-address="postal_code" :data-parent="$prefix" :data-student-address="$studentAddress?->postal_code" />
</div>
