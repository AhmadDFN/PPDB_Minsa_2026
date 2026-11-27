<x-layouts.applicant title="Tinjau Pendaftaran — PPDB MIN 1 Kota Madiun">
    <div class="max-w-5xl mx-auto space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <a href="{{ route('applicant.dashboard') }}" class="mb-2 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 hover:text-emerald-300 hover:underline">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Tinjau Pendaftaran</h1>
            <p class="mt-1 text-sm text-slate-400">Periksa kembali data sebelum mengirim pendaftaran secara resmi.</p>
        </div>

        <x-applicant.form-steps :currentStep="7" />

        @if (session('success'))
            <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="danger">
                <ul class="list-inside list-disc space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if ($registration?->isSubmitted())
            <x-ui.card class="border-green-200 bg-green-50/60 p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-800">Pendaftaran Terkirim</span>
                        <h2 class="mt-3 text-lg font-bold text-neutral-800">Pendaftaran Anda telah dikirim</h2>
                        <p class="mt-1 text-sm text-neutral-600">Ini adalah tanda bukti pendaftaran, bukan keputusan penerimaan.</p>
                    </div>
                    <div class="rounded-xl border border-green-200 bg-white px-4 py-3 sm:text-right">
                        <p class="text-xs font-semibold text-neutral-500">Nomor Registrasi</p>
                        <p class="mt-1 break-all font-mono text-lg font-extrabold text-primary-800">{{ $registration->registration_number ?: 'Menunggu Pengesahan Berkas' }}</p>
                    </div>
                </div>
                <dl class="mt-5 grid gap-4 border-t border-green-200 pt-4 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-neutral-500">Nama calon siswa</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $applicant->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-neutral-500">Program</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $program?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-neutral-500">Periode akademik</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $period?->academic_year ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-neutral-500">Tanggal dan waktu pengiriman</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $registration->submitted_at?->translatedFormat('d F Y, H:i') ?? '—' }} WIB</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-neutral-500">Status</dt>
                        <dd class="mt-1 font-semibold text-neutral-800">{{ $registration->status->label() }}</dd>
                    </div>
                </dl>
                <p class="mt-5 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm leading-relaxed text-blue-900">
                    Buat dan unduh bukti pendaftaran PDF melalui dasbor, lalu cetak dan serahkan ke loket PTSP MIN 1 Kota Madiun.
                </p>
            </x-ui.card>
        @else
            <x-ui.card class="border-primary-200 bg-primary-50/40 p-5 sm:p-6">
                <h2 class="text-base font-bold text-neutral-800">Ringkasan Kesiapan</h2>
                <p class="mt-1 text-sm text-neutral-600">Bagian yang belum lengkap harus diperbaiki sebelum formulir dapat dikirim.</p>
                <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($readiness['sections'] as $section)
                        <li class="flex items-start gap-2 rounded-lg border {{ $section['is_complete'] ? 'border-green-200 bg-white' : 'border-amber-200 bg-amber-50' }} p-3 text-sm">
                            <span class="font-bold {{ $section['is_complete'] ? 'text-green-700' : 'text-amber-700' }}">
                                {{ $section['is_complete'] ? '✓' : '!' }}
                            </span>
                            <span>
                                <span class="font-semibold text-neutral-800">{{ $section['label'] }}</span>
                                @if (! $section['is_complete'])
                                    <span class="mt-0.5 block text-xs text-neutral-600">{{ implode(', ', $section['missing']) }}</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endif

        <section class="grid gap-5 md:grid-cols-2">
            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Identitas Calon Siswa</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.biodata.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-xs text-neutral-500">Nama lengkap</dt><dd class="font-medium text-neutral-800">{{ $applicant->full_name ?: 'Belum diisi' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">NIK (tersamar)</dt><dd class="font-mono font-medium text-neutral-800">{{ $maskedNik }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Tempat, tanggal lahir</dt><dd class="font-medium text-neutral-800">{{ $applicant->birth_place ?: '—' }}, {{ $applicant->birth_date?->translatedFormat('d F Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Jenis kelamin</dt><dd class="font-medium text-neutral-800">{{ $applicant->gender?->value === 'L' ? 'Laki-laki' : ($applicant->gender?->value === 'P' ? 'Perempuan' : '—') }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">NISN</dt><dd class="font-medium text-neutral-800">{{ $applicant->nisn ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">No. handphone calon siswa</dt><dd class="font-medium text-neutral-800">{{ $applicant->student_phone ?: '—' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Anak ke- / jumlah saudara</dt><dd class="font-medium text-neutral-800">{{ $applicant->child_order ?? '—' }} / {{ $applicant->siblings_count ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Usia pada tanggal acuan periode</dt><dd class="font-medium text-neutral-800">{{ $age['formatted'] ?? 'Belum dapat dihitung' }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Alamat Domisili</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.address.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                @if ($address)
                    <p class="mt-4 text-sm leading-relaxed text-neutral-700">{{ $address->full_address }}</p>
                    <p class="mt-3 text-xs text-neutral-500">Jarak ke madrasah: {{ $address->distance_to_school_km ?? '—' }} km</p>
                @else
                    <p class="mt-4 text-sm text-amber-700">Data alamat belum diisi.</p>
                @endif
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Data Ayah</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.parents.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                @include('applicant.registration.partials.parent-summary', ['parent' => $father])
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Data Ibu</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.parents.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                @include('applicant.registration.partials.parent-summary', ['parent' => $mother])
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Data Wali</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.parents.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                @if ($guardian)
                    @include('applicant.registration.partials.parent-summary', ['parent' => $guardian])
                @else
                    <p class="mt-4 text-sm text-neutral-600">Tidak ada wali tambahan yang dicatat (opsional).</p>
                @endif
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Pendidikan Sebelumnya</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.education.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                @if ($education)
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="text-xs text-neutral-500">Jenjang</dt><dd class="font-medium text-neutral-800">{{ strtoupper($education->education_level?->value ?? '—') }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">Nama sekolah/lembaga</dt><dd class="font-medium text-neutral-800">{{ $education->school_name ?: 'Tidak sekolah formal' }}</dd></div>
                        <div><dt class="text-xs text-neutral-500">NPSN / Kota</dt><dd class="font-medium text-neutral-800">{{ $education->npsn ?: '—' }} / {{ $education->city ?: '—' }}</dd></div>
                    </dl>
                @else
                    <p class="mt-4 text-sm text-amber-700">Riwayat pendidikan belum diisi.</p>
                @endif
            </x-ui.card>

            <x-ui.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Program dan Periode</h2>
                    @if ($canEditProgram)
                        <a href="{{ route('applicant.program.edit') }}" class="text-xs font-semibold text-primary-700 hover:underline">Ubah</a>
                    @endif
                </div>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-xs text-neutral-500">Program</dt><dd class="font-medium text-neutral-800">{{ $program?->name ?? 'Belum dipilih' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Periode akademik</dt><dd class="font-medium text-neutral-800">{{ $period?->academic_year ?? 'Belum tersedia' }}</dd></div>
                    <div><dt class="text-xs text-neutral-500">Jadwal pendaftaran</dt><dd class="font-medium text-neutral-800">{{ $period?->start_date?->translatedFormat('d F Y') ?? '—' }} s.d. {{ $period?->end_date?->translatedFormat('d F Y') ?? '—' }}</dd></div>
                </dl>
            </x-ui.card>

            <x-ui.card class="p-5 md:col-span-2">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-base font-bold text-neutral-800">Ringkasan Dokumen</h2>
                    @if ($canEdit)
                        <a href="{{ route('applicant.documents.index') }}" class="text-xs font-semibold text-primary-700 hover:underline">Kelola dokumen</a>
                    @endif
                </div>
                @if ($documents->isEmpty())
                    <p class="mt-4 text-sm text-amber-700">Belum ada dokumen yang diunggah.</p>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100">
                        @foreach ($documents as $document)
                            <li class="flex flex-col justify-between gap-1 py-3 text-sm sm:flex-row sm:items-center">
                                <span class="font-medium text-neutral-800">{{ $document['requirement_name'] }}</span>
                                <span class="break-all text-xs text-neutral-600">{{ $document['original_name'] }} &middot; {{ $document['uploaded_at']?->format('d-m-Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </section>

        @if (! $registration?->isSubmitted())
            @if ($canSubmit)
                <x-ui.card class="border-amber-300 bg-amber-50/60 p-5 sm:p-6">
                    <h2 class="text-base font-bold text-neutral-900">Pernyataan Orang Tua/Wali</h2>
                    <p class="mt-2 text-sm leading-relaxed text-neutral-700">{{ $consentStatement }}</p>
                    <p class="mt-1 text-xs text-neutral-500">Versi pernyataan: {{ $consentVersion }}</p>
                    <div class="mt-4 rounded-xl border border-amber-200 bg-white p-4 text-sm leading-relaxed text-amber-900">
                        <strong>Perhatian:</strong> Setelah tombol ditekan, pendaftaran akan berstatus SUBMITTED dan memperoleh nomor registrasi. Data masih dapat diperbarui selama periode pendaftaran terbuka, dan setiap perubahan akan dicatat.
                    </div>

                    <form method="POST" action="{{ route('applicant.registration.submit') }}" class="mt-5 space-y-4">
                        @csrf
                        <label class="flex items-start gap-3 text-sm text-neutral-700">
                            <input type="checkbox" name="consent" value="1" class="mt-1 rounded border-neutral-300 text-primary-700 focus:ring-primary-600" required>
                            <span>Saya telah membaca pernyataan di atas dan menyetujuinya.</span>
                        </label>
                        <div>
                            <label for="consent-name" class="mb-1 block text-sm font-semibold text-neutral-700">Nama orang tua/wali yang menyetujui</label>
                            <input id="consent-name" type="text" name="consent_name" value="{{ old('consent_name') }}" maxlength="255" required class="form-input w-full sm:max-w-md">
                        </div>
                        <button type="submit" class="btn btn-primary inline-flex w-full justify-center font-semibold sm:w-auto">
                            Kirim Pendaftaran Final
                        </button>
                    </form>
                </x-ui.card>
            @elseif ($registration?->isDraft())
                <x-ui.card class="border-amber-200 bg-amber-50/60 p-5">
                    <h2 class="font-bold text-neutral-800">Pendaftaran Belum Siap Dikirim</h2>
                    <p class="mt-1 text-sm text-neutral-700">Lengkapi semua bagian pada daftar kesiapan sebelum mengirim pendaftaran.</p>
                </x-ui.card>
            @else
                <x-ui.card class="p-5">
                    <p class="text-sm text-neutral-600">Pilih program pada periode pendaftaran yang sedang dibuka untuk memulai pendaftaran.</p>
                </x-ui.card>
            @endif
        @endif
    </div>
</x-layouts.applicant>
