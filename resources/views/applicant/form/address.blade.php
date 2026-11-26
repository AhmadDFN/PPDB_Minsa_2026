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
                        required
                        help="Dapat memuat angka 0 di depan (misal: 003)."
                    />

                    <x-forms.input
                        name="rw"
                        label="RW (Rukun Warga)"
                        type="text"
                        maxlength="5"
                        placeholder="Contoh: 005"
                        :value="old('rw', $address?->rw)"
                        required
                        help="Dapat memuat angka 0 di depan (misal: 001)."
                    />
                </div>

                <!-- Kelurahan & Kecamatan -->
                <x-applicant.region-selects
                    label="Wilayah Domisili Calon Siswa"
                    :codes="['province' => old('province_code', $address?->province_code), 'city' => old('city_code', $address?->city_code), 'kecamatan' => old('district_code', $address?->district_code), 'kelurahan' => old('village_code', $address?->village_code)]"
                    :names="['province' => old('province', $address?->province ?? 'Jawa Timur'), 'city' => old('city', $address?->city ?? 'Kota Madiun'), 'kecamatan' => old('kecamatan', $address?->kecamatan), 'kelurahan' => old('kelurahan', $address?->kelurahan)]"
                    student
                />

                <section
                    id="distance-locator"
                    class="space-y-4 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 sm:p-5"
                    data-campus-lat="{{ config('ppdb.school.campus_one.latitude') }}"
                    data-campus-lon="{{ config('ppdb.school.campus_one.longitude') }}"
                    data-map-url="{{ asset('maps/min1-madiun-campus.json') }}"
                >
                    <div>
                        <h2 class="text-base font-semibold text-neutral-900">Tentukan Lokasi Tempat Tinggal</h2>
                        <p class="mt-1 text-sm leading-6 text-neutral-600">Gunakan lokasi perangkat atau masukkan koordinat secara manual. Jarak garis lurus ke MIN 1 Kota Madiun Kampus 1 dihitung di perangkat dan diverifikasi kembali oleh server.</p>
                    </div>

                    <button
                        type="button"
                        id="use-device-location"
                        class="btn btn-secondary min-h-11 w-full justify-center sm:w-auto"
                    >
                        Gunakan Lokasi Perangkat
                    </button>

                    <p id="location-feedback" class="min-h-5 text-sm text-neutral-600" aria-live="polite" role="status"></p>

                    <div>
                        <div class="relative aspect-[743/1000] overflow-hidden rounded-xl border border-emerald-200 bg-[#edf3e8] shadow-sm">
                            <canvas id="offline-campus-map-canvas" width="743" height="1000" class="absolute inset-0 h-full w-full" aria-hidden="true"></canvas>
                            <svg
                                id="offline-campus-map"
                                class="absolute inset-0 h-full w-full touch-manipulation"
                                viewBox="0 0 743 1000"
                                role="application"
                                aria-label="Peta offline sekitar Kampus 1. Ketuk atau klik untuk memasang titik lokasi, lalu seret pin untuk menyesuaikan."
                                tabindex="0"
                                aria-busy="true"
                            >
                                <text x="371.5" y="500" text-anchor="middle" fill="#4b6351" font-size="23">Memuat peta offline…</text>
                            </svg>
                        </div>
                        <div class="mt-2 flex flex-col gap-1 text-xs text-neutral-600 sm:flex-row sm:items-center sm:justify-between">
                            <span>Ketuk peta untuk meletakkan pin. Seret pin atau gunakan tombol panah setelah memilihnya.</span>
                            <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-800 underline underline-offset-2">© OpenStreetMap contributors · ODbL</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-forms.input
                            name="latitude"
                            label="Lintang (latitude)"
                            type="number"
                            step="any"
                            min="-90"
                            max="90"
                            placeholder="Contoh: -7.659100"
                            :value="old('latitude')"
                            help="Koordinat dipakai saat menghitung, lalu tidak disimpan."
                        />

                        <x-forms.input
                            name="longitude"
                            label="Bujur (longitude)"
                            type="number"
                            step="any"
                            min="-180"
                            max="180"
                            placeholder="Contoh: 111.514600"
                            :value="old('longitude')"
                        />
                    </div>

                    <p id="distance-preview" class="text-sm font-semibold text-emerald-900" aria-live="polite"></p>
                    <p class="text-xs leading-5 text-neutral-600">Lokasi tidak diminta sampai tombol ditekan. Koordinat tidak dikirim ke layanan peta atau pihak ketiga. Jika izin lokasi tidak tersedia, kosongkan koordinat dan masukkan perkiraan jarak di bawah.</p>
                </section>

                <!-- Kode Pos & Estimasi Jarak -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-forms.input
                        name="postal_code"
                        label="Kode Pos"
                        type="text"
                        maxlength="10"
                        placeholder="Contoh: 63121"
                        :value="old('postal_code', $address?->postal_code)"
                        required
                    />

                    <x-forms.input
                        name="distance_to_school_km"
                        label="Jarak ke Madrasah (KM)"
                        type="number"
                        step="0.1"
                        min="0"
                        placeholder="Contoh: 1.5"
                        :value="old('distance_to_school_km', $address?->distance_to_school_km)"
                        required
                        help="Dengan koordinat lengkap jarak dihitung otomatis. Tanpa koordinat, isi perkiraan manual dalam kilometer."
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

    @push('scripts')
    <script>
        (() => {
            const section = document.getElementById('distance-locator');
            const latitude = document.getElementById('latitude');
            const longitude = document.getElementById('longitude');
            const distance = document.getElementById('distance_to_school_km');
            const locateButton = document.getElementById('use-device-location');
            const feedback = document.getElementById('location-feedback');
            const preview = document.getElementById('distance-preview');
            const map = document.getElementById('offline-campus-map');
            const canvas = document.getElementById('offline-campus-map-canvas');
            if (!section || !latitude || !longitude || !distance || !locateButton || !feedback || !preview || !map || !canvas) return;

            const svgNamespace = 'http://www.w3.org/2000/svg';
            const width = 743;
            const height = 1000;
            const campusLatitude = Number(section.dataset.campusLat);
            const campusLongitude = Number(section.dataset.campusLon);
            let bounds = null;
            let pin = null;
            let dragging = false;

            const radians = value => value * Math.PI / 180;
            const calculateDistanceKm = (lat, lon) => {
                const latDelta = radians(campusLatitude - lat);
                const lonDelta = radians(campusLongitude - lon);
                const haversine = Math.min(1, Math.max(0,
                    Math.sin(latDelta / 2) ** 2
                    + Math.cos(radians(lat)) * Math.cos(radians(campusLatitude)) * Math.sin(lonDelta / 2) ** 2
                ));
                return 6371.0088 * 2 * Math.atan2(Math.sqrt(haversine), Math.sqrt(1 - haversine));
            };

            const mapPoint = (lat, lon) => ({
                x: ((lon - bounds.west) / (bounds.east - bounds.west)) * width,
                y: ((bounds.north - lat) / (bounds.north - bounds.south)) * height,
            });

            const coordinateAt = event => {
                const rect = map.getBoundingClientRect();
                const x = Math.min(width, Math.max(0, ((event.clientX - rect.left) / rect.width) * width));
                const y = Math.min(height, Math.max(0, ((event.clientY - rect.top) / rect.height) * height));
                return {
                    lat: bounds.north - (y / height) * (bounds.north - bounds.south),
                    lon: bounds.west + (x / width) * (bounds.east - bounds.west),
                };
            };

            const updatePin = (lat, lon) => {
                if (!bounds || !pin) return;
                const insideMap = lat >= bounds.south && lat <= bounds.north && lon >= bounds.west && lon <= bounds.east;
                pin.setAttribute('visibility', insideMap ? 'visible' : 'hidden');
                if (insideMap) {
                    const point = mapPoint(lat, lon);
                    pin.setAttribute('transform', `translate(${point.x} ${point.y})`);
                    pin.setAttribute('aria-label', `Pin lokasi, lintang ${lat.toFixed(6)}, bujur ${lon.toFixed(6)}`);
                }
                map.setAttribute('aria-busy', 'false');
            };

            const updatePreview = () => {
                const lat = Number(latitude.value);
                const lon = Number(longitude.value);
                if (latitude.value === '' || longitude.value === '' || !Number.isFinite(lat) || !Number.isFinite(lon)) {
                    preview.textContent = '';
                    if (pin) pin.setAttribute('visibility', 'hidden');
                    return;
                }

                if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                    preview.textContent = 'Koordinat belum valid.';
                    return;
                }

                const km = calculateDistanceKm(lat, lon);
                const rounded = Math.round(km * 100) / 100;
                distance.value = rounded.toFixed(2);
                preview.textContent = `Perkiraan jarak garis lurus: ${rounded.toFixed(2)} km. Server menghitung ulang saat disimpan.`;
                updatePin(lat, lon);
                if (bounds && (lat < bounds.south || lat > bounds.north || lon < bounds.west || lon > bounds.east)) {
                    feedback.textContent = 'Titik berada di luar cakupan peta offline sekitar Kampus 1. Koordinat tetap dapat digunakan untuk menghitung jarak.';
                }
            };

            const setPointFromMapEvent = event => {
                if (!bounds) return;
                const point = coordinateAt(event);
                latitude.value = point.lat.toFixed(6);
                longitude.value = point.lon.toFixed(6);
                feedback.textContent = 'Pin lokasi diperbarui. Koordinat hanya dipakai untuk menghitung jarak saat disimpan.';
                updatePreview();
            };

            const chooseMapPoint = event => {
                if (event.target === pin) return;
                setPointFromMapEvent(event);
            };

            const svgElement = (tag, attributes) => {
                const element = document.createElementNS(svgNamespace, tag);
                for (const [name, value] of Object.entries(attributes)) element.setAttribute(name, String(value));
                return element;
            };

            const drawMap = async () => {
                try {
                    const response = await fetch(section.dataset.mapUrl, { credentials: 'same-origin' });
                    if (!response.ok) throw new Error('Peta offline tidak dapat dimuat.');
                    const data = await response.json();
                    bounds = data.bounds;
                    const context = canvas.getContext('2d');
                    if (!context) throw new Error('Canvas tidak tersedia.');
                    context.fillStyle = '#edf3e8';
                    context.fillRect(0, 0, width, height);
                    context.lineJoin = 'round';
                    context.lineCap = 'round';
                    for (const feature of data.features.filter(item => item.type === 'building')) {
                        context.beginPath();
                        feature.coordinates.forEach(([lon, lat], index) => {
                            const point = mapPoint(lat, lon);
                            if (index === 0) context.moveTo(point.x, point.y);
                            else context.lineTo(point.x, point.y);
                        });
                        context.closePath();
                        context.fillStyle = '#e4d9c5';
                        context.strokeStyle = '#c9bea9';
                        context.lineWidth = 1.1;
                        context.fill();
                        context.stroke();
                    }
                    const roadWidth = roadClass => ({ motorway: 5, trunk: 4.5, primary: 4, secondary: 3.5, tertiary: 3, residential: 2.3, unclassified: 2, service: 1.5, footway: 1, path: 1 })[roadClass] || 2;
                    const roads = data.features.filter(item => item.type === 'road');
                    for (const road of roads) {
                        context.beginPath();
                        road.coordinates.forEach(([lon, lat], index) => {
                            const point = mapPoint(lat, lon);
                            if (index === 0) context.moveTo(point.x, point.y);
                            else context.lineTo(point.x, point.y);
                        });
                        context.strokeStyle = road.class === 'footway' || road.class === 'path' ? '#c1b8a9' : '#fffdf8';
                        context.lineWidth = roadWidth(road.class);
                        context.stroke();
                    }

                    const campus = mapPoint(campusLatitude, campusLongitude);
                    context.beginPath();
                    context.arc(campus.x, campus.y, 13, 0, 2 * Math.PI);
                    context.fillStyle = '#047857';
                    context.fill();
                    context.strokeStyle = '#ffffff';
                    context.lineWidth = 4;
                    context.stroke();
                    context.font = '700 23px system-ui, sans-serif';
                    context.lineWidth = 5;
                    context.strokeStyle = '#edf3e8';
                    context.strokeText('MIN 1 Kampus 1', campus.x + 17, campus.y - 12);
                    context.fillStyle = '#064e3b';
                    context.fillText('MIN 1 Kampus 1', campus.x + 17, campus.y - 12);

                    map.replaceChildren();
                    pin = svgElement('g', { tabindex: 0, role: 'button', cursor: 'grab', visibility: 'hidden' });
                    pin.style.touchAction = 'none';
                    pin.append(svgElement('circle', { cx: 0, cy: -10, r: 23, fill: '#dc2626', stroke: '#ffffff', 'stroke-width': 6 }));
                    pin.append(svgElement('circle', { cx: 0, cy: -10, r: 7, fill: '#ffffff' }));
                    pin.addEventListener('pointerdown', event => {
                        dragging = true;
                        pin.setPointerCapture(event.pointerId);
                        event.stopPropagation();
                    });
                    pin.addEventListener('pointermove', event => {
                        if (dragging) setPointFromMapEvent(event);
                    });
                    pin.addEventListener('pointerup', () => { dragging = false; });
                    pin.addEventListener('keydown', event => {
                        const step = event.shiftKey ? 0.0005 : 0.0001;
                        const lat = Number(latitude.value);
                        const lon = Number(longitude.value);
                        const offsets = { ArrowUp: [step, 0], ArrowDown: [-step, 0], ArrowLeft: [0, -step], ArrowRight: [0, step] };
                        if (!offsets[event.key]) return;
                        event.preventDefault();
                        latitude.value = (lat + offsets[event.key][0]).toFixed(6);
                        longitude.value = (lon + offsets[event.key][1]).toFixed(6);
                        updatePreview();
                    });
                    map.append(pin);
                    map.addEventListener('pointerdown', chooseMapPoint);
                    map.setAttribute('aria-busy', 'false');
                    updatePreview();
                } catch {
                    map.setAttribute('aria-busy', 'false');
                    feedback.textContent = 'Peta offline belum dapat dimuat. Masukkan koordinat atau gunakan perkiraan jarak manual.';
                }
            };

            latitude.addEventListener('input', updatePreview);
            longitude.addEventListener('input', updatePreview);
            locateButton.addEventListener('click', () => {
                if (!navigator.geolocation) {
                    feedback.textContent = 'Browser ini tidak mendukung lokasi perangkat. Masukkan koordinat atau perkiraan jarak secara manual.';
                    return;
                }

                locateButton.disabled = true;
                feedback.textContent = 'Meminta izin lokasi perangkat…';
                navigator.geolocation.getCurrentPosition(position => {
                    latitude.value = position.coords.latitude.toFixed(6);
                    longitude.value = position.coords.longitude.toFixed(6);
                    updatePreview();
                    if (bounds && position.coords.latitude >= bounds.south && position.coords.latitude <= bounds.north && position.coords.longitude >= bounds.west && position.coords.longitude <= bounds.east) {
                        feedback.textContent = 'Pin dipindahkan ke lokasi perangkat. Koordinat tidak dikirim ke penyedia peta.';
                    }
                    locateButton.disabled = false;
                }, error => {
                    feedback.textContent = error.code === error.PERMISSION_DENIED
                        ? 'Izin lokasi ditolak. Pilih titik pada peta, masukkan koordinat, atau isi jarak manual.'
                        : 'Lokasi tidak tersedia. Pilih titik pada peta, masukkan koordinat, atau isi jarak manual.';
                    locateButton.disabled = false;
                }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
            });

            drawMap();
        })();
    </script>
    @endpush
</x-layouts.applicant>
