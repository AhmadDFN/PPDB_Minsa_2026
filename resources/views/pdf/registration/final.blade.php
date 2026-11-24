@extends('pdf.registration.layout')

@section('content')
    <h1 class="document-title">Bukti Pendaftaran</h1>
    <div class="notice">
        <strong>PENDAFTARAN TELAH DIKIRIM.</strong>
        Dokumen ini merupakan bukti pengiriman pendaftaran untuk diserahkan ke PTSP, bukan pengumuman hasil seleksi.
    </div>

    <div class="section-title">Identitas Pendaftaran</div>
    <table class="data-table">
        <tr><th>Nomor Urut Pendaftaran</th><td><strong style="font-size: 13px;">{{ $document['registration_number'] }}</strong> (Urut ke-{{ $document['queue_sequence'] ?? '-' }})</td></tr>
        <tr><th>Periode / tahun ajaran</th><td>{{ $document['period_name'] }} / {{ $document['academic_year'] }}</td></tr>
        <tr><th>Tanggal dikirim</th><td>{{ $document['submitted_at'] ?: 'Belum ditentukan' }}</td></tr>
        @if (!empty($document['verified_at']))
            <tr><th>Tanggal disahkan</th><td>{{ $document['verified_at'] }}</td></tr>
        @endif
        <tr><th>Versi data</th><td>{{ $document['data_revision'] }}</td></tr>
    </table>

    @if (!empty($document['interview_batch']))
        <div class="section-title">Jadwal & Lokasi Wawancara Pemetaan</div>
        <table class="data-table">
            <tr><th>Gelombang / Sesi</th><td><strong>{{ $document['interview_batch']['name'] }}</strong></td></tr>
            <tr><th>Hari & Tanggal</th><td><strong>{{ $document['interview_batch']['interview_date'] }}</strong></td></tr>
            <tr><th>Waktu Kehadiran</th><td>{{ $document['interview_batch']['start_time'] }} s.d. {{ $document['interview_batch']['end_time'] }}</td></tr>
            @if (!empty($document['interview_batch']['description']))
                <tr><th>Keterangan / Ruang</th><td>{{ $document['interview_batch']['description'] }}</td></tr>
            @endif
        </table>
    @endif

    @include('pdf.registration.applicant-summary')

    @if ($document['consent'])
        <div class="section-title">Pernyataan Persetujuan Historis</div>
        <table class="data-table">
            <tr><th>Pernyataan</th><td>{{ $document['consent']['statement_text'] }}</td></tr>
            <tr><th>Disetujui oleh</th><td>{{ $document['consent']['accepted_by_name'] }}</td></tr>
            <tr><th>Waktu persetujuan</th><td>{{ $document['consent']['agreed_at'] ?: 'Tidak tersedia' }}</td></tr>
        </table>
    @endif

    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                Madiun, ........................................
                <br>Orang tua/wali calon siswa
                <div class="signature-line"></div>
                ({{ $document['consent']['accepted_by_name'] ?? '........................................' }})
            </td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                Ruang tanda tangan / materai bila diperlukan
            </td>
        </tr>
    </table>

    <table class="qr-table">
        <tr>
            <td style="width: 115px;">
                @if ($qrCodeDataUri)
                    <img class="qr-code" src="{{ $qrCodeDataUri }}" alt="QR verifikasi bukti pendaftaran">
                @endif
            </td>
            <td>
                <strong>Verifikasi dokumen</strong><br>
                QR memuat nomor pendaftaran dan tautan verifikasi dengan token acak.
                <br><span class="small">{{ $verificationUrl ?? '' }}</span>
            </td>
        </tr>
    </table>

    <div class="notice">
        <strong>{{ $document['collection_instruction'] }}</strong>
        <div class="small">Sistem ini hanya menerbitkan bukti pengiriman pendaftaran; tidak mengelola seleksi atau keputusan penerimaan.</div>
    </div>
@endsection
