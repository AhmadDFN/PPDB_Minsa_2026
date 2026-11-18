@extends('pdf.registration.layout')

@section('content')
    <h1 class="document-title">Draf Pendaftaran</h1>
    <div class="notice">
        <strong>DOKUMEN DRAF - BUKAN BUKTI RESMI.</strong>
        Data dalam lembar ini dapat berubah dan belum membuktikan bahwa pendaftaran telah dikirim.
    </div>

    <div class="section-title">Identitas Draf</div>
    <table class="data-table">
        <tr><th>Nomor pendaftaran</th><td>{{ $document['registration_number'] }}</td></tr>
        <tr><th>Dibuat pada</th><td>{{ $document['generated_at'] }}</td></tr>
        <tr><th>Versi data</th><td>{{ $document['data_revision'] }}</td></tr>
    </table>

    @include('pdf.registration.applicant-summary')

    <div class="notice small">
        Draf ini bukan bukti pendaftaran resmi, bukan keputusan seleksi, dan tidak dapat digunakan sebagai bukti telah diterima.
    </div>
@endsection
