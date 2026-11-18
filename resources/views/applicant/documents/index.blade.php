<x-layouts.app title="Dokumen Persyaratan — PPDB MIN 1 Kota Madiun">
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div>
            <a href="{{ route('applicant.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 hover:text-primary-700 hover:underline mb-2">
                &larr; Kembali ke Dasbor
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-800 tracking-tight">Dokumen Persyaratan</h1>
            <p class="mt-1 text-sm text-neutral-600">Berkas disimpan secara privat dan hanya dapat diakses oleh pemilik akun.</p>
        </div>

        <x-applicant.form-steps :currentStep="6" />

        @if (session('success'))
            <x-ui.alert type="success" dismissible>{{ session('success') }}</x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert type="danger">
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if (! $registration)
            <x-ui.card class="p-8 text-center border-amber-200 bg-amber-50/50">
                <h2 class="text-lg font-bold text-neutral-800">Pilih Program Terlebih Dahulu</h2>
                <p class="text-sm text-neutral-600 max-w-xl mx-auto mt-2">
                    Persyaratan dokumen tersedia setelah Anda memilih program pada periode pendaftaran aktif.
                </p>
                <a href="{{ route('applicant.program.edit') }}" class="btn btn-primary inline-flex mt-5">Pilih Program</a>
            </x-ui.card>
        @elseif ($requirements->isEmpty())
            <x-ui.card class="p-8 text-center border-amber-200 bg-amber-50/50">
                <h2 class="text-lg font-bold text-neutral-800">Persyaratan Belum Tersedia</h2>
                <p class="text-sm text-neutral-600 max-w-xl mx-auto mt-2">
                    Persyaratan dokumen untuk periode ini belum dikonfigurasi. Silakan hubungi panitia PPDB.
                </p>
            </x-ui.card>
        @else
            <x-ui.card class="p-5 sm:p-6 border-primary-200 bg-primary-50/30">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-neutral-800">{{ $registration->admissionPeriod?->name }}</h2>
                        <p class="text-xs text-neutral-600 mt-1">
                            Kelengkapan dokumen wajib:
                            <strong>{{ $completeness['uploaded_count'] }} / {{ $completeness['required_count'] }}</strong>
                        </p>
                    </div>
                    <span class="text-xl font-extrabold {{ $completeness['is_complete'] ? 'text-green-700' : 'text-primary-700' }}">
                        {{ $completeness['percentage'] }}%
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden mt-4">
                    <div class="h-full rounded-full {{ $completeness['is_complete'] ? 'bg-green-500' : 'bg-primary-600' }}" style="width: {{ $completeness['percentage'] }}%"></div>
                </div>
            </x-ui.card>

            <div class="space-y-5">
                @foreach ($requirements as $requirement)
                    @php
                        $document = $documents->get($requirement->id);
                        $effectiveMaxSizeKb = min($globalMaxSizeKb, (int) $requirement->max_size_kb);
                    @endphp

                    <x-ui.card class="p-5 sm:p-6 border-neutral-200/90">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base font-bold text-neutral-800">{{ $requirement->name }}</h2>
                                    @if ($requirement->is_required)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-700">Wajib</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-neutral-100 text-neutral-600">Opsional</span>
                                    @endif
                                </div>
                                @if ($requirement->description)
                                    <p class="text-sm text-neutral-600 mt-1">{{ $requirement->description }}</p>
                                @endif
                                <p class="text-xs text-neutral-500 mt-2">
                                    Format: {{ str_replace(',', ', ', $requirement->allowed_mimes) }}
                                    &bull; Maksimum {{ $effectiveMaxSizeKb }} KB
                                </p>
                            </div>

                            @if ($document)
                                <span class="inline-flex shrink-0 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Sudah Diunggah</span>
                            @else
                                <span class="inline-flex shrink-0 px-2.5 py-1 rounded-full text-xs font-bold {{ $requirement->is_required ? 'bg-amber-100 text-amber-800' : 'bg-neutral-100 text-neutral-600' }}">
                                    {{ $requirement->is_required ? 'Belum Diunggah' : 'Belum Ada' }}
                                </span>
                            @endif
                        </div>

                        @if ($document)
                            <div class="mt-4 p-4 rounded-xl bg-neutral-50 border border-neutral-200">
                                <p class="text-sm font-semibold text-neutral-800 break-all">{{ $document->original_name }}</p>
                                <p class="text-xs text-neutral-500 mt-1">
                                    {{ number_format($document->file_size_bytes / 1024, 1) }} KB
                                    &bull; Diunggah {{ $document->uploaded_at?->format('d-m-Y H:i') }}
                                </p>
                                <div class="flex flex-wrap gap-3 mt-3">
                                    <a href="{{ route('applicant.documents.preview', $document->id) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-primary-700 hover:underline">Pratinjau</a>
                                    <a href="{{ route('applicant.documents.download', $document->id) }}" class="text-xs font-semibold text-primary-700 hover:underline">Unduh</a>
                                </div>
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('applicant.documents.upload', $requirement->id) }}"
                            enctype="multipart/form-data"
                            class="mt-4 space-y-3"
                        >
                            @csrf
                            <label for="document-file-{{ $requirement->id }}" class="block text-xs font-semibold text-neutral-700">
                                {{ $document ? 'Pilih berkas pengganti' : 'Pilih berkas' }}
                                @if ($requirement->is_required && ! $document)
                                    <span class="text-red-600">*</span>
                                @endif
                            </label>
                            <input
                                id="document-file-{{ $requirement->id }}"
                                class="document-upload-input block w-full text-sm text-neutral-700 file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                                type="file"
                                name="file"
                                accept=".pdf,.jpg,.jpeg,.png"
                                data-max-size-kb="{{ $effectiveMaxSizeKb }}"
                                @if ($requirement->is_required && ! $document) required @endif
                            >
                            <p class="document-upload-feedback text-xs text-neutral-500">
                                Batas unggah {{ $effectiveMaxSizeKb }} KB; maksimum global {{ $globalMaxSizeKb }} KB.
                                Pastikan jenis file dan isinya sesuai.
                            </p>

                            @if ($document)
                                <label for="document-reason-{{ $requirement->id }}" class="block text-xs font-semibold text-neutral-700">Alasan penggantian (opsional)</label>
                                <input
                                    id="document-reason-{{ $requirement->id }}"
                                    type="text"
                                    name="reason"
                                    maxlength="255"
                                    class="form-input w-full"
                                    placeholder="Contoh: memperbarui hasil pindai"
                                >
                            @endif

                            <button type="submit" class="btn btn-primary inline-flex text-xs">
                                {{ $document ? 'Ganti Dokumen' : 'Unggah Dokumen' }}
                            </button>
                        </form>

                        @if ($document && $document->revisions->isNotEmpty())
                            <details class="mt-5 border-t border-neutral-100 pt-4">
                                <summary class="cursor-pointer text-xs font-semibold text-neutral-700">
                                    Riwayat penggantian ({{ $document->revisions->count() }})
                                </summary>
                                <ol class="mt-3 space-y-3">
                                    @foreach ($document->revisions as $revision)
                                        <li class="rounded-lg border border-neutral-200 p-3">
                                            <p class="text-xs font-semibold text-neutral-800 break-all">{{ $revision->original_name }}</p>
                                            <p class="text-[11px] text-neutral-500 mt-1">
                                                Revisi {{ $revision->revision_number }}
                                                &bull; {{ $revision->replaced_at?->format('d-m-Y H:i') }}
                                                &bull; {{ $revision->reason }}
                                            </p>
                                            <div class="flex gap-3 mt-2">
                                                <a href="{{ route('applicant.documents.revisions.preview', [$document->id, $revision->id]) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-primary-700 hover:underline">Pratinjau</a>
                                                <a href="{{ route('applicant.documents.revisions.download', [$document->id, $revision->id]) }}" class="text-xs font-semibold text-primary-700 hover:underline">Unduh</a>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </details>
                        @endif
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('.document-upload-input').forEach((input) => {
            input.addEventListener('change', () => {
                const feedback = input.parentElement.querySelector('.document-upload-feedback');
                const file = input.files[0];
                const maxBytes = Number(input.dataset.maxSizeKb) * 1024;

                if (file && file.size > maxBytes) {
                    feedback.textContent = `Ukuran file melebihi batas ${input.dataset.maxSizeKb} KB.`;
                    feedback.classList.add('text-red-700');
                    input.setCustomValidity('Ukuran file melebihi batas yang ditentukan.');
                } else {
                    feedback.textContent = `Batas unggah ${input.dataset.maxSizeKb} KB; pastikan jenis file dan isinya sesuai.`;
                    feedback.classList.remove('text-red-700');
                    input.setCustomValidity('');
                }
            });
        });
    </script>
</x-layouts.app>
