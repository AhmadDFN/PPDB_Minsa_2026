<div class="section-title">Data Calon Siswa</div>
<table class="data-table">
    <tr>
        <td>
            <table>
                <tr><th>Nama lengkap</th><td>{{ $document['applicant']['full_name'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>NIK calon siswa</th><td>{{ $document['applicant']['nik'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>Jenis kelamin</th><td>{{ $document['applicant']['gender'] }}</td></tr>
                <tr><th>Tempat, tanggal lahir</th><td>{{ $document['applicant']['birth_place'] ?: 'Belum diisi' }}{{ $document['applicant']['birth_date'] ? ', '.$document['applicant']['birth_date'] : '' }}</td></tr>
                <tr><th>Usia pada tanggal acuan</th><td>{{ $document['age_at_reference'] }}</td></tr>
                <tr><th>NISN</th><td>{{ $document['applicant']['nisn'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>Anak ke- / jumlah saudara</th><td>{{ $document['applicant']['child_order'] ?? 'Belum diisi' }} / {{ $document['applicant']['siblings_count'] ?? 'Belum diisi' }}</td></tr>
            </table>
        </td>
        <td class="photo-cell">
            @if ($document['photo_data_uri'])
                <img class="photo" src="{{ $document['photo_data_uri'] }}" alt="Pas foto calon siswa">
                <div class="small">Pas foto</div>
            @else
                <div class="small">Pas foto belum tersedia</div>
            @endif
        </td>
    </tr>
</table>

<div class="section-title">Alamat Domisili</div>
<table class="data-table">
    <tr><th>Alamat</th><td>{{ $document['address'] ?: 'Belum diisi' }}</td></tr>
</table>

<div class="section-title">Data Orang Tua / Wali</div>
<table class="data-table">
    <tr><th>Ayah</th><td>{{ $document['father']['name'] ?: 'Belum diisi' }}; {{ $document['father']['occupation'] ?: 'pekerjaan belum diisi' }}; {{ $document['father']['phone'] ?: 'telepon belum diisi' }}</td></tr>
    <tr><th>Ibu</th><td>{{ $document['mother']['name'] ?: 'Belum diisi' }}; {{ $document['mother']['occupation'] ?: 'pekerjaan belum diisi' }}; {{ $document['mother']['phone'] ?: 'telepon belum diisi' }}</td></tr>
    @if ($document['guardian'])
        <tr><th>Wali</th><td>{{ $document['guardian']['name'] ?: 'Belum diisi' }}; {{ $document['guardian']['occupation'] ?: 'pekerjaan belum diisi' }}; {{ $document['guardian']['phone'] ?: 'telepon belum diisi' }}</td></tr>
    @endif
</table>

<div class="section-title">Pendidikan Sebelumnya</div>
<table class="data-table">
    <tr><th>Jenjang</th><td>{{ $document['education']['level'] }}</td></tr>
    <tr><th>Nama sekolah/lembaga</th><td>{{ $document['education']['school_name'] ?: 'Belum diisi' }}</td></tr>
    <tr><th>NPSN / kota</th><td>{{ $document['education']['npsn'] ?: 'Belum diisi' }} / {{ $document['education']['city'] ?: 'Belum diisi' }}</td></tr>
</table>

<div class="section-title">Periode dan Program</div>
<table class="data-table">
    <tr><th>Periode pendaftaran</th><td>{{ $document['period_name'] }} ({{ $document['academic_year'] }})</td></tr>
    <tr><th>Jadwal pendaftaran</th><td>{{ $document['period_start_date'] ?: 'Belum ditentukan' }} - {{ $document['period_end_date'] ?: 'Belum ditentukan' }}</td></tr>
    <tr><th>Program pilihan</th><td>{{ $document['program_name'] }}</td></tr>
</table>
