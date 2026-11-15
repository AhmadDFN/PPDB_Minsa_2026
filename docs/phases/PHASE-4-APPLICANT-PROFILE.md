# PHASE 4 — PROFIL CALON SISWA, BIODATA ORANG TUA & PILIHAN PROGRAM
## Rencana Kerja dan Desain Modul Pengisian Data Pendaftaran

**Status**: MENUNGGU PERSETUJUAN PEMILIK PROYEK  
**Target Pelaksanaan**: Fase 4

---

## 1. TUJUAN FASE 4

Membangun alur pengisian dan pembaruan data calon peserta didik baru setelah akun diaktifkan pada Fase 3. Modul ini memungkinkan wali murid melengkapi seluruh data yang diwajibkan oleh madrasah secara bertahap atau sekaligus.

---

## 2. MODUL FORMULIR YANG AKAN DIBANGUN

1. **Biodata Siswa Lanjutan**:
   - NISN (opsional 10 digit).
   - Anak ke-berapa dan jumlah saudara kandung.
   - Perhitungan umur dinamis (*runtime calculation*) berdasarkan `birth_date` dan `age_reference_date` periode aktif tanpa menyimpan kolom umur permanen di database.
2. **Alamat Domisili Siswa**:
   - Alamat jalan / RT / RW / Kelurahan / Kecamatan / Kota / Kode Pos (`applicant_addresses`).
3. **Data Orang Tua & Wali**:
   - Data Ayah Kandung: Wajib diisi (`applicant_guardians`, `relationship_type = ayah`).
   - Data Ibu Kandung: Wajib diisi (`applicant_guardians`, `relationship_type = ibu`).
   - Data Wali: Opsional (`applicant_guardians`, `relationship_type = wali`).
4. **Riwayat Pendidikan TK/RA**:
   - Status jenjang: TK, RA, PAUD, atau Tidak Sekolah.
   - Nama sekolah asal dan alamat (`applicant_educations`).
5. **Pemilihan Program Pendidikan**:
   - Memilih Program Unggulan atau Program Reguler yang ditawarkan pada periode PPDB aktif.
   - Tanpa pembatasan kuota penolakan.

---

## 3. ARSITEKTUR KELAS & ACTION YANG DIRENCANAKAN

- `app/Actions/Applicant/UpdateApplicantBiodataAction.php`
- `app/Actions/Applicant/SaveApplicantAddressAction.php`
- `app/Actions/Applicant/SaveApplicantGuardianAction.php`
- `app/Actions/Applicant/SaveApplicantEducationAction.php`
- `app/Actions/Applicant/SelectAdmissionProgramAction.php`
- `app/Http/Requests/Applicant/UpdateBiodataRequest.php`
- `app/Http/Requests/Applicant/SaveAddressRequest.php`
- `app/Http/Requests/Applicant/SaveGuardianRequest.php`
- `app/Http/Requests/Applicant/SaveEducationRequest.php`
- `app/Http/Requests/Applicant/SelectProgramRequest.php`
- `app/Http/Controllers/Applicant/RegistrationFormController.php`

---

## 4. PERSYARATAN KELUARAN (EXIT CRITERIA)

1. Wali murid dapat melengkapi dan menyimpan form per bagian.
2. Seluruh validasi data tersaring ketat dengan pesan Bahasa Indonesia.
3. Feature tests mencakup skenario happy path, validasi gagal, dan penolakan manipulasi antar-pendaftar.
4. Laporan Fase 4 disusun di `docs/phases/PHASE-4-APPLICANT-PROFILE.md`.
