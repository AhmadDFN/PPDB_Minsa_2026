@props([
    'prefix' => '',
    'label' => 'Alamat',
    'codes' => [],
    'names' => [],
    'student' => false,
    'required' => true,
])

@php
    $levels = [
        ['suffix' => 'province', 'code_suffix' => 'province_code', 'label' => 'Provinsi'],
        ['suffix' => 'city', 'code_suffix' => 'city_code', 'label' => 'Kabupaten / Kota'],
        ['suffix' => 'kecamatan', 'code_suffix' => 'district_code', 'label' => 'Kecamatan'],
        ['suffix' => 'kelurahan', 'code_suffix' => 'village_code', 'label' => 'Desa / Kelurahan'],
    ];
    $group = $prefix === '' ? 'student' : trim($prefix, '_');
@endphp

<fieldset class="space-y-4" data-region-group="{{ $group }}" data-region-endpoint="{{ route('applicant.regions.index') }}">
    <legend class="mb-2 text-sm font-semibold text-neutral-800">{{ $label }}</legend>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach ($levels as $level)
            @php
                $field = $prefix.$level['suffix'];
                $codeField = $prefix.$level['code_suffix'];
                $selectedCode = old($codeField, $codes[$level['suffix']] ?? null);
                $initialName = old($field, $names[$level['suffix']] ?? '');
            @endphp
            <div>
                <label for="{{ $codeField }}" class="mb-1.5 block text-sm font-medium text-neutral-700">{{ $level['label'] }} @if ($required)<span class="text-red-500">*</span>@endif</label>
                <select id="{{ $codeField }}" name="{{ $codeField }}" data-region-level="{{ $level['suffix'] }}" data-initial-code="{{ $selectedCode }}" data-initial-name="{{ $initialName }}" @if ($required) required @endif class="min-h-11 w-full rounded-lg border border-neutral-300 bg-white px-3 py-2.5 text-sm text-neutral-900 focus:border-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-600/20">
                    <option value="">Pilih {{ strtolower($level['label']) }}</option>
                </select>
                <input type="hidden" name="{{ $field }}" value="{{ $initialName }}" data-region-name="{{ $level['suffix'] }}">
                <x-forms.error :name="$codeField" />
                @if ($student)
                    <span class="hidden" data-student-region-name="{{ $level['suffix'] }}"></span>
                @endif
            </div>
        @endforeach
    </div>
</fieldset>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const groups = [...document.querySelectorAll('[data-region-group]')];
            const labels = ['province', 'city', 'kecamatan', 'kelurahan'];

            const selectOf = (group, level) => group.querySelector(`[data-region-level="${level}"]`);
            const nameOf = (group, level) => group.querySelector(`[data-region-name="${level}"]`);

            async function getChildren(group, parentCode = null) {
                const url = new URL(group.dataset.regionEndpoint, window.location.origin);
                if (parentCode) url.searchParams.set('parent', parentCode);
                const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!response.ok) throw new Error('Daftar wilayah tidak dapat dimuat. Muat ulang halaman atau isi ulang alamat.');
                return (await response.json()).items;
            }

            function setOptions(select, items, placeholder) {
                select.replaceChildren(new Option(placeholder, ''));
                items.forEach((item) => select.add(new Option(item.name, item.code)));
                select.disabled = false;
            }

            async function populate(group, startIndex = 0, parentCode = null, selectStored = false) {
                const items = await getChildren(group, parentCode);
                const select = selectOf(group, labels[startIndex]);
                const placeholder = `Pilih ${labels[startIndex] === 'city' ? 'kabupaten/kota' : labels[startIndex] === 'kecamatan' ? 'kecamatan' : labels[startIndex] === 'kelurahan' ? 'desa/kelurahan' : 'provinsi'}`;
                setOptions(select, items, placeholder);
                const hidden = nameOf(group, labels[startIndex]);
                const initialCode = select.dataset.initialCode;
                const initialName = select.dataset.initialName || hidden.value;
                const selected = items.find((item) => (initialCode && item.code === initialCode)
                    || (selectStored && initialName && item.name.toLocaleLowerCase('id') === initialName.toLocaleLowerCase('id')));

                if (selected) {
                    select.value = selected.code;
                    hidden.value = selected.name;
                    const studentCopy = group.querySelector(`[data-student-region-name="${labels[startIndex]}"]`);
                    if (studentCopy) studentCopy.textContent = selected.name;
                    if (startIndex < labels.length - 1) await populate(group, startIndex + 1, selected.code, selectStored);
                } else {
                    for (let i = startIndex + 1; i < labels.length; i++) {
                        const child = selectOf(group, labels[i]);
                        setOptions(child, [], `Pilih ${labels[i]}`);
                        child.disabled = true;
                        nameOf(group, labels[i]).value = '';
                    }
                }
                if (startIndex === 0 && selectStored) {
                    group.querySelectorAll('[data-region-level]').forEach((element) => {
                        element.dataset.initialCode = '';
                        element.dataset.initialName = '';
                    });
                }
            }

            groups.forEach((group) => {
                group.querySelectorAll('[data-region-level]').forEach((select, index) => {
                    select.disabled = index > 0;
                    select.addEventListener('change', async () => {
                        const hidden = nameOf(group, labels[index]);
                        hidden.value = select.selectedOptions[0]?.textContent ?? '';
                        const studentCopy = group.querySelector(`[data-student-region-name="${labels[index]}"]`);
                        if (studentCopy) studentCopy.textContent = hidden.value;
                        for (let i = index + 1; i < labels.length; i++) {
                            const child = selectOf(group, labels[i]);
                            child.replaceChildren(new Option(`Pilih ${labels[i]}`, ''));
                            child.disabled = true;
                            nameOf(group, labels[i]).value = '';
                        }
                        if (select.value && index < labels.length - 1) {
                            try { await populate(group, index + 1, select.value, false); }
                            catch (error) { group.dataset.error = error.message; }
                        }
                    });
                });
                populate(group, 0, null, true).catch((error) => { group.dataset.error = error.message; });
            });

            window.copyStudentRegionToParent = async (targetName) => {
                const target = groups.find((group) => group.dataset.regionGroup === targetName);
                const source = groups.find((group) => group.dataset.regionGroup === 'student');
                if (!target || !source) return;
                let parentCode = null;
                for (let i = 0; i < labels.length; i++) {
                    const sourceSelect = selectOf(source, labels[i]);
                    const targetSelect = selectOf(target, labels[i]);
                    const items = await getChildren(target, parentCode);
                    setOptions(targetSelect, items, `Pilih ${labels[i]}`);
                    targetSelect.value = sourceSelect.value;
                    nameOf(target, labels[i]).value = targetSelect.selectedOptions[0]?.textContent ?? '';
                    parentCode = targetSelect.value || null;
                }
            };
        });
    </script>
@endonce
