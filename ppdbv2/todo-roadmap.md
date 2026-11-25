# TODO & ROADMAP IMPLEMENTASI PPDB v2 (MODUL WAWANCARA)

Gunakan dokumen ini sebagai panduan eksekusi berurutan. Setiap menyelesaikan satu item: **kerjakan → jalankan test sederhana → update checklist ini & `PROGRESS-LOG.md` → commit & `git push origin main`**. Jika ada blocker, catat di `KNOWN-ISSUES.md` lalu lanjut ke task lain yang tidak bergantung.

---

## Fase 0: Pre-Flight & Kesehatan Fondasi (Wajib Pertama)
- [x] **0.1** Perbaiki kompatibilitas SQLite pada migration `2026_11_24_120000_enforce_single_active_admission_period.php` (tambahkan pengecekan driver `DB::getDriverName() === 'sqlite'` agar `php artisan test` berjalan 100% bersih).
- [x] **0.2** Verifikasi seluruh test v1 (192+ tests) lulus tanpa error (`php artisan test`).
- [x] **0.3** Verifikasi konfigurasi `.env` lokal & produksi (termasuk pengecekan ekstensi PHP `gd` dan batas upload Hostinger).

---

## Fase 1: Skema Database, Model & Seeder Role/Akun
- [x] **1.1** Buat migration tabel `interview_batches`, `interview_stages`, `interview_criteria`, `interview_sessions`, `interview_scores` (driver-agnostic MySQL & SQLite).
- [x] **1.2** Buat migration penambahan kolom pada `registrations`: `queue_sequence`, `verified_at`, `verified_by`, `interview_batch_id` (serta pastikan `registration_number` nullable sebelum verifikasi).
- [x] **1.3** Buat Model Eloquent & relasinya: `InterviewBatch`, `InterviewStage`, `InterviewCriterion`, `InterviewSession`, `InterviewScore`, dan update Model `Registration`.
- [x] **1.4** Update `RoleAndPermissionSeeder`:
  - Tambahkan role `interviewer_stage_1`, `interviewer_stage_2`, `interviewer_stage_3` beserta permission granularnya.
  - Seed akun default: **1 `superadmin`**, **3 `admin_ppdb`**, **3 `interviewer_stage_1`**, **3 `interviewer_stage_2`**, **3 `interviewer_stage_3`**.
- [x] **1.5** Buat `InterviewMasterSeeder`:
  - Seed default 3 Tahap Wawancara (Tahap 1: BTQ/Iqro' 2 poin, Tahap 2: Calistung 4 poin, Tahap 3: Khusus Unggulan 3 poin).
  - Seed default Batch Jadwal Wawancara (70 nomor/hari).
- [x] **1.6** Jalankan test migrasi & seeder (`php artisan migrate:fresh --seed` di lokal + unit test dasar).

---

## Fase 2: Rombak Penomoran Urut (Opsi B) & Notifikasi WhatsApp
- [x] **2.1** Sesuaikan alur *Final Submit* pendaftar: status menjadi `SUBMITTED`, tetapi **belum** menerbitkan nomor urut pendaftaran.
- [x] **2.2** Buat `ApproveAndAssignQueueNumberAction`:
  - Validasi seluruh dokumen wajib berstatus `APPROVED`.
  - Terbitkan `queue_sequence` (`1..n`) dan `registration_number` format `{tahun}{4digit}` (misal `270001`) secara atomik (`lockForUpdate`).
  - Auto-mapping `interview_batch_id` berdasarkan rentang kuota harian (misal 70/hari).
  - Catat Audit Log.
- [x] **2.3** Tambahkan tombol **"Sahkan Berkas & Terbitkan Nomor"** pada halaman detail verifikasi panitia (`/admin/pendaftar/{id}` atau `/admin/document-verification`).
- [x] **2.4** Update template PDF Bukti Pendaftaran Final: tampilkan Nomor Urut (`270001`), Tanggal & Jam Jadwal Wawancara, dan QR Code pemindaian.
- [x] **2.5** Tambahkan template pesan WhatsApp baru di Communication Center (Fonnte API) untuk notifikasi *"Berkas Terverifikasi + Nomor Urut + Jadwal Wawancara"* dan hubungkan ke `ApproveAndAssignQueueNumberAction`.
- [x] **2.6** Jalankan Feature Test: pastikan submit tidak menerbitkan nomor, pengesahan panitia menerbitkan nomor urut `270001`, `270002` secara berurutan, dan memicu pengiriman WA (mock/log).

---

## Fase 3: Panel Master Pengaturan Wawancara (Superadmin)
- [x] **3.1** Buat CRUD **Master Jadwal / Batch Wawancara** (`/admin/interview-batches`): tambah/edit tanggal, jam mulai-selesai, kuota (default 70), dan rentang nomor urut otomatis.
- [x] **3.2** Buat CRUD **Master Tahap & Poin Penilaian** (`/admin/interview-stages`):
  - Ubah judul & deskripsi masing-masing tahap.
  - Tambah, edit, urutkan, atau aktif/nonaktifkan poin penilaian (`interview_criteria`) pada tiap tahap.
- [x] **3.3** Tambahkan proteksi: poin penilaian yang sudah memiliki riwayat nilai (`interview_scores`) tidak boleh di-hard-delete (hanya boleh dinonaktifkan / *soft toggle*).
- [x] **3.4** Jalankan Feature Test otorisasi (hanya `superadmin` yang dapat mengakses pengaturan master).

---

## Fase 4: Portal Pewawancara (Mobile-First, Scan QR & Sequential Gate)
- [x] **4.1** Buat rute & middleware khusus `/wawancara/*` yang hanya mengizinkan `interviewer_stage_1`, `interviewer_stage_2`, dan `interviewer_stage_3` (redirect otomatis sesuai role setelah login).
- [x] **4.2** Buat `LookupApplicantForInterviewAction` & `StartInterviewSessionAction`:
  - Validasi status pendaftaran `VERIFIED`.
  - Validasi **Sequential Gate** (Tahap 2 wajib Tahap 1 selesai; Tahap 3 wajib Program Unggulan + Tahap 1 & 2 selesai).
  - Validasi **Soft Lock** agar tidak terjadi penilaian ganda secara bersamaan.
- [x] **4.3** Buat UI Halaman Scan & Pencarian Cepat (`/wawancara`):
  - Scanner kamera QR Code (`html5-qrcode`) + input manual nomor `270001`.
  - Statistik ringkas hari ini (*jumlah siswa selesai saja, tanpa menampilkan nilai*).
- [x] **4.4** Buat `SubmitAndLockInterviewAction` & UI Form Penilaian (`/wawancara/sesi/{session}`):
  - Tampilkan Pas Foto 3x4, Nama Lengkap, Nomor Urut, Program.
  - Input skor `0–100` untuk setiap poin aktif pada tahap pewawancara tersebut.
  - Modal konfirmasi sebelum **"Simpan & Kunci Nilai"**.
  - Hitung `total_score` dan `average_score` di server, ubah status ke `COMPLETED` (terkunci mutlak), lalu redirect kembali ke halaman Scan.
- [x] **4.5** Jalankan Feature Test lengkap untuk Portal Pewawancara:
  - Penguji Tahap 1 tidak bisa akses Tahap 2/3.
  - Penguji Tahap 2 ditolak jika Tahap 1 belum selesai.
  - Penguji Tahap 3 ditolak jika siswa Reguler atau Tahap 1/2 belum selesai.
  - Setelah dikunci, penguji tidak bisa membuka/mengubah sesi yang sama lagi.

---

## Fase 5: Rekap Nilai Admin, Koreksi Ber-Audit & Ekspor CSV
- [x] **5.1** Buat halaman **Rekap Nilai Wawancara** (`/admin/interview-recaps`) khusus `admin_ppdb` & `superadmin`:
  - Tampilkan rincian poin, **Jumlah**, dan **Rata-rata terpisah per tahap** (Tahap 1, Tahap 2, Tahap 3).
  - Filter berdasarkan Batch/Hari, Program, Status Kelengkapan, dan pencarian Nama/Nomor Urut.
- [x] **5.2** Buat `AdminOverrideInterviewScoreAction` & Modal **Koreksi Nilai**:
  - Wajib isi alasan koreksi (minimal 10 karakter).
  - Hitung ulang `total_score` dan `average_score` tahap tersebut.
  - Simpan *before/after JSON diff*, alasan, dan aktor ke tabel Audit Log (`/admin/audit-logs`).
- [x] **5.3** Buat `ExportInterviewRecapCsvAction`: ekspor *streamed CSV* dengan kolom dinamis per tahap dan sanitasi *anti-formula injection*.
- [x] **5.4** Jalankan Feature Test akhir:
  - Pastikan semua role pewawancara mendapat `403 Forbidden` saat mencoba mengakses `/admin/interview-recaps`, koreksi nilai, atau ekspor CSV.
  - Pastikan koreksi admin wajib menyertakan alasan dan tercatat di Audit Log.
  - Jalankan full test suite (`php artisan test`) dan smoke test manual di produksi (`https://ahmaddanyfn.biz.id/`).
