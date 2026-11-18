@extends('pdf.registration.layout')

@section('content')
    <h1 class="document-title">Bukti Pendaftaran</h1>
    <div class="notice">
        <strong>PENDAFTARAN TELAH DIKIRIM.</strong>
        Dokumen ini merupakan bukti pengiriman pendaftaran untuk diserahkan ke PTSP, bukan pengumuman hasil seleksi.
    </div>

    <div class="section-title">Identitas Pendaftaran</div>
    <table class="data-table">
        <tr><th>Nomor pendaftaran</th><td><strong>{{ $document['registration_number'] }}</strong></td></tr>
        <tr><th>Periode / tahun ajaran</th><td>{{ $document['period_name'] }} / {{ $document['academic_year'] }}</td></tr>
        <tr><th>Tanggal dikirim</th><td>{{ $document['submitted_at'] ?: 'Belum ditentukan' }}</td></tr>
        <tr><th>Versi data</th><td>{{ $document['data_revision'] }}</td></tr>
    </table>

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
