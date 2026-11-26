<div class="section-title">Data Calon Siswa</div>
<table class="data-table">
    <tr>
        <td>
            <table>
                <tr><th>Nama lengkap</th><td>{{ $document['applicant']['full_name'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>NIK calon siswa</th><td>{{ $document['applicant']['nik'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>Nomor Kartu Keluarga (tersamar)</th><td>{{ $document['applicant']['family_card_number'] ?? 'Belum diisi' }}</td></tr>
                <tr><th>Jenis kelamin</th><td>{{ $document['applicant']['gender'] }}</td></tr>
                <tr><th>Tempat, tanggal lahir</th><td>{{ $document['applicant']['birth_place'] ?: 'Belum diisi' }}{{ $document['applicant']['birth_date'] ? ', '.$document['applicant']['birth_date'] : '' }}</td></tr>
                <tr><th>Usia pada tanggal acuan</th><td>{{ $document['age_at_reference'] }}</td></tr>
                <tr><th>NISN</th><td>{{ $document['applicant']['nisn'] ?: 'Belum diisi' }}</td></tr>
                <tr><th>No. handphone calon siswa</th><td>{{ $document['applicant']['student_phone'] ?? 'Belum diisi' }}</td></tr>
                <tr><th>Anak ke- / jumlah saudara</th><td>{{ $document['applicant']['child_order'] ?? 'Belum diisi' }} / {{ $document['applicant']['siblings_count'] ?? 'Belum diisi' }}</td></tr>
                <tr><th>Agama</th><td>{{ $document['applicant']['religion'] ?? 'Belum diisi' }}</td></tr>
                <tr><th>Hobi / cita-cita</th><td>{{ $document['applicant']['hobby'] ?? 'Belum diisi' }} / {{ $document['applicant']['aspiration'] ?? 'Belum diisi' }}</td></tr>
                <tr><th>Bantuan sosial (nomor tersamar)</th><td>{{ $document['applicant']['social_assistance_number'] ?? 'Belum diisi' }}</td></tr>
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
    <tr><th>Status tinggal / waktu tempuh / transportasi</th><td>{{ $document['applicant']['residence_status'] ?? 'Belum diisi' }} / {{ $document['applicant']['travel_time'] ?? 'Belum diisi' }} / {{ $document['applicant']['transportation'] ?? 'Belum diisi' }}</td></tr>
    <tr><th>Jarak ke madrasah</th><td>{{ $document['distance_to_school_km'] ?? 'Belum diisi' }} km</td></tr>
</table>

<div class="section-title">Data Orang Tua / Wali</div>
<table class="data-table">
    <tr><th>Ayah</th><td>{{ $document['father']['name'] ?? 'Belum diisi' }}; NIK {{ $document['father']['nik'] ?? 'Belum diisi' }}; {{ $document['father']['birth_place'] ?? 'tempat lahir belum diisi' }}{{ !empty($document['father']['birth_date']) ? ', '.$document['father']['birth_date'] : '' }}; {{ $document['father']['status'] ?? 'status belum diisi' }}; {{ $document['father']['education'] ?? 'pendidikan belum diisi' }}; {{ $document['father']['occupation'] ?? 'pekerjaan belum diisi' }}; {{ $document['father']['monthly_income'] ?? 'penghasilan belum diisi' }}; HP {{ $document['father']['phone'] ?? 'belum diisi' }}.</td></tr>
    <tr><th>Domisili / alamat ayah</th><td>{{ $document['father']['residence'] ?? 'Belum diisi' }}; {{ $document['father']['address'] ?? 'alamat belum diisi' }}; {{ $document['father']['residence_status'] ?? 'status tinggal belum diisi' }}</td></tr>
    <tr><th>Ibu</th><td>{{ $document['mother']['name'] ?? 'Belum diisi' }}; NIK {{ $document['mother']['nik'] ?? 'Belum diisi' }}; {{ $document['mother']['birth_place'] ?? 'tempat lahir belum diisi' }}{{ !empty($document['mother']['birth_date']) ? ', '.$document['mother']['birth_date'] : '' }}; {{ $document['mother']['status'] ?? 'status belum diisi' }}; {{ $document['mother']['education'] ?? 'pendidikan belum diisi' }}; {{ $document['mother']['occupation'] ?? 'pekerjaan belum diisi' }}; {{ $document['mother']['monthly_income'] ?? 'penghasilan belum diisi' }}; HP {{ $document['mother']['phone'] ?? 'belum diisi' }}.</td></tr>
    <tr><th>Domisili / alamat ibu</th><td>{{ $document['mother']['residence'] ?? 'Belum diisi' }}; {{ $document['mother']['address'] ?? 'alamat belum diisi' }}; {{ $document['mother']['residence_status'] ?? 'status tinggal belum diisi' }}</td></tr>
    @if ($document['guardian'])
        <tr><th>Wali</th><td>{{ $document['guardian']['name'] ?? 'Belum diisi' }}; NIK {{ $document['guardian']['nik'] ?? 'Belum diisi' }}; {{ $document['guardian']['birth_place'] ?? 'tempat lahir belum diisi' }}{{ !empty($document['guardian']['birth_date']) ? ', '.$document['guardian']['birth_date'] : '' }}; {{ $document['guardian']['status'] ?? 'status belum diisi' }}; {{ $document['guardian']['education'] ?? 'pendidikan belum diisi' }}; {{ $document['guardian']['occupation'] ?? 'pekerjaan belum diisi' }}; {{ $document['guardian']['monthly_income'] ?? 'penghasilan belum diisi' }}; HP {{ $document['guardian']['phone'] ?? 'belum diisi' }}; {{ $document['guardian']['residence'] ?? 'domisili belum diisi' }}; {{ $document['guardian']['address'] ?? 'alamat belum diisi' }}; {{ $document['guardian']['residence_status'] ?? 'status tinggal belum diisi' }}.</td></tr>
    @endif
</table>

@if (!empty($document['documents']))
    <div class="section-title">Status Berkas yang Diunggah</div>
    <table class="data-table">
        @foreach ($document['documents'] as $item)
            <tr><th>{{ $item['name'] }}</th><td>{{ $item['status'] }}</td></tr>
        @endforeach
    </table>
@endif

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
