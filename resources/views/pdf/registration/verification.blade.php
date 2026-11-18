<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Bukti Pendaftaran</title>
    <style>
        body { background: #f1f5f9; color: #172033; font-family: Arial, sans-serif; margin: 0; padding: 32px 16px; }
        main { background: #fff; border: 1px solid #dbe3ec; border-radius: 12px; margin: 0 auto; max-width: 620px; padding: 24px; }
        h1 { color: #163b68; font-size: 22px; margin-top: 0; }
        .status { border-radius: 8px; padding: 12px; }
        .current { background: #dcfce7; color: #166534; }
        .outdated, .superseded { background: #fef3c7; color: #92400e; }
        .unavailable { background: #fee2e2; color: #991b1b; }
        dt { color: #475569; font-size: 13px; margin-top: 16px; }
        dd { font-weight: bold; margin: 4px 0 0; overflow-wrap: anywhere; }
        .privacy { color: #475569; font-size: 13px; line-height: 1.5; margin-top: 20px; }
    </style>
</head>
<body>
    <main>
        <h1>Verifikasi Bukti Pendaftaran</h1>
        @php
            $statusLabels = [
                'current' => 'Dokumen valid dan merupakan versi terbaru.',
                'outdated' => 'Dokumen dapat dibaca, tetapi data pendaftaran telah berubah.',
                'superseded' => 'Dokumen dapat dibaca, tetapi telah digantikan oleh versi yang lebih baru.',
                'unavailable' => 'Dokumen tidak dapat diverifikasi. Hubungi PTSP madrasah.',
            ];
        @endphp
        <div class="status {{ $verification['status'] }}">
            {{ $statusLabels[$verification['status']] }}
        </div>
        <dl>
            <dt>Nomor pendaftaran</dt>
            <dd>{{ $verification['registration_number'] }}</dd>
            <dt>PDF diterbitkan</dt>
            <dd>{{ $verification['generated_at'] ?: 'Tidak tersedia' }}</dd>
            <dt>Sidik digital SHA-256</dt>
            <dd>{{ $verification['fingerprint'] }}</dd>
        </dl>
        <p class="privacy">Halaman ini hanya menampilkan status verifikasi. Data pribadi calon siswa tidak ditampilkan dan berkas PDF tidak dapat diunduh dari halaman publik.</p>
    </main>
</body>
</html>
