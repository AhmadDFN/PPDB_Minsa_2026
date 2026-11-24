# BLUEPRINT SISTEM PPDB v2 — MODUL WAWANCARA & PENOMORAN TERPADU
**Institusi:** MIN 1 Kota Madiun (MINSATAMA) — Kementerian Agama RI  
**Domain Produksi:** `https://ahmaddanyfn.biz.id/`  
**Basis Arsitektur:** Laravel 13.x, PHP 8.3+, Blade + Tailwind CSS v4 + Vanilla JS (Non-SPA), MySQL/MariaDB (Produksi), SQLite (Test Suite), Spatie Permission (Guard `web`), Fonnte API (WhatsApp Gateway)

---

## 1. Tujuan & Batasan Lingkup (Scope v2)

Versi 2 memperluas sistem PPDB Minsatama dari tahap pendaftaran & verifikasi berkas hingga **tahap pelaksanaan dan rekapitulasi nilai wawancara calon siswa**.

### Dalam Lingkup (In-Scope v2)
1. **Rombak Mekanisme Nomor Urut Pendaftaran (Opsi B):**
   - Nomor urut global **tidak lagi diterbitkan saat pendaftar Final Submit**, melainkan **saat Panitia (`admin_ppdb`) mengesahkan berkas wajib (`APPROVED`)**.
   - Format nomor urut global: **`{2-digit-tahun}{4-digit-urut}`** (contoh: `270001` s.d. `279999`), bersifat urut `1..n` secara global tanpa dipisah per program, menggunakan *atomic counter* anti-*race condition*.
2. **Penjadwalan Wawancara Otomatis Berbasis Kuota Harian:**
   - Jadwal wawancara ditentukan otomatis berdasarkan nomor urut pendaftaran (default: **70 nomor per hari**, dapat dikustomisasi oleh Superadmin per hari/batch).
   - Notifikasi WhatsApp otomatis via Fonnte dikirim ke wali murid begitu berkas disahkan, berisi konfirmasi terverifikasi, nomor urut (`270001`), dan jadwal wawancara.
   - Pembaruan PDF Bukti Pendaftaran Final agar menampilkan Nomor Urut, Tanggal/Sesi Wawancara, dan QR Code pemindaian pewawancara.
3. **Sistem Wawancara 3 Tahap (Calon Siswa):**
   - **Tahap 1** (Semua Program — Reguler & Unggulan): *Baca Tulis Al-Qur'an / Iqro'* — default **2 poin penilaian**.
   - **Tahap 2** (Semua Program — Reguler & Unggulan): *Calistung Sederhana* — default **4 poin penilaian**.
   - **Tahap 3** (Khusus **Unggulan** saja): *Wawancara Khusus / Rahasia* oleh Tim Khusus — default **3 poin penilaian**.
   - Judul tahap dan daftar poin penilaian bersifat **dinamis (*customizable*)** melalui panel Superadmin.
4. **Alur Lapangan Pewawancara Sistem Pool & Kunci Mutlak:**
   - Pewawancara yang sedang kosong cukup memindai QR Code pada Bukti Pendaftaran siswa atau mengetik nomor urut (`270001`).
   - Wajib mengikuti urutan **Tahap 1 → Tahap 2 → Tahap 3** (*Sequential Gate*) dengan *soft lock* anti-penilaian ganda.
   - Rentang skor per poin: **`0–100`**.
   - Begitu pewawancara menekan **"Simpan & Kunci Nilai"**, sesi terkunci permanen; pewawancara tidak dapat melihat maupun mengubah nilai kembali.
5. **Rekap Nilai Terpisah Per Wawancara & Audit Koreksi Admin:**
   - Rekap nilai menampilkan rincian poin, **jumlah skor (`total_score`)**, dan **rata-rata (`average_score`)** secara **terpisah per tahap wawancara** (tanpa penggabungan skor akhir antar tahap).
   - Hanya **`admin_ppdb`** dan **`superadmin`** yang dapat melihat rekap nilai dan mengekspor CSV tersanitasi.
   - Kesalahan input nilai oleh pewawancara hanya dapat dikoreksi oleh Admin dengan **alasan wajib** yang tercatat lengkap pada **Audit Log**.

### Di Luar Lingkup (Out-of-Scope v2)
- Sistem **belum** memproses penetapan kelulusan, perankingan otomatis, pengumuman kelulusan di portal pendaftar, maupun alur daftar ulang (*re-registration*). Proses berhenti pada **Rekap Hasil Nilai & Ekspor Data** untuk rapat dewan guru.

---

## 2. Perubahan State Machine Pendaftaran

```text
[DRAFT]
   │ (Pendaftar klik Final Submit + Consent v1.0)
   ▼
[SUBMITTED]  ──► (Belum punya nomor urut pendaftaran; masuk antrean verifikasi panitia)
   │
   ├── (Ada berkas buram/salah) ──► [REVISION_REQUIRED + Reopen Window] ──► (Upload ulang) ──► [SUBMITTED]
   │
   │ (Semua berkas wajib APPROVED & Panitia klik "Sahkan Berkas & Terbitkan Nomor")
   ▼
[VERIFIED]   ──► Terbit Nomor Urut (270001) + Auto-Assign Batch Jadwal + Regenerate PDF + Kirim WA Fonnte
   │
   ▼
[TAHAP 1: BTQ / Iqro']       (Status sesi: IN_PROGRESS → COMPLETED/LOCKED)
   │ (Wajib Tahap 1 COMPLETED)
   ▼
[TAHAP 2: Calistung]         (Status sesi: IN_PROGRESS → COMPLETED/LOCKED)
   │
   ├── (Jika Program REGULER)  ──► SELESAI WAWANCARA (2/2)
   │
   └── (Jika Program UNGGULAN & Tahap 1, 2 COMPLETED)
          ▼
       [TAHAP 3: Khusus]     (Status sesi: IN_PROGRESS → COMPLETED/LOCKED) ──► SELESAI WAWANCARA (3/3)
```

---

## 3. Matriks Role, Isolasi Akses & Seeder Akun

Seluruh otorisasi menggunakan **Spatie Laravel Permission** pada guard tunggal `web`. Pewawancara dikunci mutlak pada bidangnya dan bersifat *blind* terhadap rekap nilai.

| Role Spatie | Jumlah Akun Seeder | Ruang Lingkup Akses | Akses Rekap Nilai |
|---|:-:|---|---|
| `superadmin` | 1 | Seluruh pengaturan sistem, Master Batch/Jadwal, Master Tahap & Poin, Manajemen Akun, Fonnte, Audit Log, Rekap & Koreksi Nilai | **Ya (Semua Tahap)** |
| `admin_ppdb` | 3 | Verifikasi Dokumen, Pengesahan & Penerbitan Nomor, Reopen Revisi, Rekap Nilai Wawancara, Koreksi Nilai (dengan Audit Log), Ekspor CSV | **Ya (Semua Tahap)** |
| `interviewer_stage_1` | 3 | Khusus halaman Scan QR / Input Nomor & Form Penilaian **Tahap 1** | **Tidak (Diblokir 403)** |
| `interviewer_stage_2` | 3 | Khusus halaman Scan QR / Input Nomor & Form Penilaian **Tahap 2** | **Tidak (Diblokir 403)** |
| `interviewer_stage_3` | 3 | Khusus halaman Scan QR / Input Nomor & Form Penilaian **Tahap 3 (Unggulan)** | **Tidak (Diblokir 403)** |

### Konvensi Akun Seeder Default (Password wajib diganti saat produksi)
- **Superadmin (1):** `superadmin@minsatama.sch.id`
- **Admin PPDB (3):** `admin1@minsatama.sch.id`, `admin2@minsatama.sch.id`, `admin3@minsatama.sch.id`
- **Pewawancara Tahap 1 (3):** `penguji1a@minsatama.sch.id`, `penguji1b@minsatama.sch.id`, `penguji1c@minsatama.sch.id`
- **Pewawancara Tahap 2 (3):** `penguji2a@minsatama.sch.id`, `penguji2b@minsatama.sch.id`, `penguji2c@minsatama.sch.id`
- **Pewawancara Tahap 3 (3):** `penguji3a@minsatama.sch.id`, `penguji3b@minsatama.sch.id`, `penguji3c@minsatama.sch.id`

---

## 4. Desain Skema Database (Driver-Agnostic MySQL & SQLite)

> **Catatan Kompatibilitas:** Semua migrasi v2 **wajib** kompatibel dengan MySQL/MariaDB produksi dan SQLite in-memory (dilarang menggunakan `storedAs` pada `ALTER TABLE`).

### 4.1 Perubahan Tabel `registrations`
Tambahkan kolom secara additive (nullable untuk menjaga kompatibilitas data lama):
- `queue_sequence` (`unsignedInteger`, nullable, indexed) — nomor urut global `1..n` per periode.
- `verified_at` (`timestamp`, nullable) — waktu pengesahan berkas oleh panitia.
- `verified_by` (`foreignId` -> `users.id`, nullable) — panitia yang mengesahkan.
- `interview_batch_id` (`foreignId` -> `interview_batches.id`, nullable) — jadwal wawancara otomatis.
- `registration_number` (sudah ada, nullable saat masih `SUBMITTED`, diisi format `270001` saat `VERIFIED`, unique per periode).

### 4.2 Tabel Baru: `interview_batches` (Master Jadwal & Kuota Harian)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigIncrements` | Primary key |
| `admission_period_id` | `foreignId` | Relasi ke `admission_periods` |
| `batch_number` | `unsignedInteger` | Hari ke-1, 2, 3, dst. |
| `title` | `string(100)` | Misal: `Hari 1 - Gelombang Pagi` |
| `interview_date` | `date` | Tanggal pelaksanaan wawancara |
| `start_time` | `time` | Jam mulai (misal `07:30`) |
| `end_time` | `time` | Jam selesai (misal `14:00`) |
| `quota` | `unsignedInteger` | Default `70` |
| `sequence_start` | `unsignedInteger` | Awal rentang urut (misal `1`) |
| `sequence_end` | `unsignedInteger` | Akhir rentang urut (misal `70`) |
| `is_active` | `boolean` | Default `true` |
| `timestamps` | `timestamp` | `created_at`, `updated_at` |

### 4.3 Tabel Baru: `interview_stages` (Master Tahap Wawancara — Customizable)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigIncrements` | Primary key |
| `admission_period_id` | `foreignId` | Relasi ke `admission_periods` |
| `stage_order` | `unsignedTinyInteger` | `1`, `2`, `3` (unique per periode) |
| `code` | `string(30)` | `STAGE_1`, `STAGE_2`, `STAGE_3` |
| `title` | `string(150)` | Judul tahap (dapat diedit Superadmin) |
| `description` | `text` (nullable) | Panduan singkat untuk pewawancara |
| `program_scope` | `string(20)` | `ALL` (Tahap 1 & 2) atau `UNGGULAN` (Tahap 3) |
| `required_role` | `string(50)` | `interviewer_stage_1`, `interviewer_stage_2`, `interviewer_stage_3` |
| `is_confidential` | `boolean` | `true` untuk Tahap 3 |
| `is_active` | `boolean` | Default `true` |
| `timestamps` | `timestamp` | `created_at`, `updated_at` |

### 4.4 Tabel Baru: `interview_criteria` (Master Poin Penilaian — Customizable)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigIncrements` | Primary key |
| `interview_stage_id` | `foreignId` | Relasi ke `interview_stages` |
| `title` | `string(150)` | Nama poin penilaian (customizable) |
| `description` | `text` (nullable) | Rubrik / indikator penilaian |
| `min_score` | `unsignedTinyInteger` | Default `0` |
| `max_score` | `unsignedTinyInteger` | Default `100` |
| `sort_order` | `unsignedTinyInteger` | Urutan tampil |
| `is_active` | `boolean` | Default `true` |
| `timestamps` | `timestamp` | `created_at`, `updated_at` |

**Default Seeder Kriteria:**
- **Tahap 1 (2 Poin):** `1. Kelancaran Membaca Al-Qur'an / Iqro'`, `2. Makharijul Huruf & Ketepatan Bacaan`.
- **Tahap 2 (4 Poin):** `1. Kemampuan Membaca Sederhana`, `2. Kemampuan Menulis & Motorik Halus`, `3. Kemampuan Berhitung Dasar`, `4. Pemahaman Instruksi & Fokus`.
- **Tahap 3 (3 Poin):** `1. Kriteria Khusus 1 (Customizable)`, `2. Kriteria Khusus 2 (Customizable)`, `3. Kriteria Khusus 3 (Customizable)`.

### 4.5 Tabel Baru: `interview_sessions` (Header Sesi per Siswa per Tahap)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigIncrements` | Primary key |
| `registration_id` | `foreignId` | Relasi ke `registrations` |
| `interview_stage_id` | `foreignId` | Relasi ke `interview_stages` |
| `interviewer_id` | `foreignId` | Relasi ke `users` (pewawancara yang menilai) |
| `status` | `string(20)` | `IN_PROGRESS`, `COMPLETED` |
| `started_at` | `timestamp` | Waktu mulai scan/buka form (*soft lock*) |
| `completed_at` | `timestamp` (nullable) | Waktu dikunci permanen |
| `total_score` | `unsignedSmallInteger` (nullable) | Jumlah nilai seluruh poin pada tahap ini |
| `average_score` | `decimal(5,2)` (nullable) | `total_score / jumlah_poin` pada tahap ini |
| `notes` | `text` (nullable) | Catatan singkat pewawancara (opsional) |
| `last_edited_by` | `foreignId` (nullable) | Admin terakhir yang mengoreksi (jika ada) |
| `last_edit_reason` | `text` (nullable) | Alasan wajib koreksi admin |
| `timestamps` | `timestamp` | `created_at`, `updated_at` |

- **Unique Constraint:** `(registration_id, interview_stage_id)` — menjamin 1 calon siswa hanya memiliki 1 sesi penilaian pada setiap tahap.

### 4.6 Tabel Baru: `interview_scores` (Detail Nilai per Poin)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigIncrements` | Primary key |
| `interview_session_id` | `foreignId` | Relasi ke `interview_sessions` |
| `interview_criterion_id` | `foreignId` | Relasi ke `interview_criteria` |
| `score` | `unsignedTinyInteger` | Nilai integer `0–100` |
| `timestamps` | `timestamp` | `created_at`, `updated_at` |

- **Unique Constraint:** `(interview_session_id, interview_criterion_id)`.

---

## 5. Daftar Service / Action Classes (Clean Architecture)

Mengikuti pola *Thin Controllers* pada sistem v1:

1. `ApproveAndAssignQueueNumberAction`
   - Memastikan seluruh dokumen wajib berstatus `APPROVED`.
   - Mengunci counter periode dengan `lockForUpdate()`, menerbitkan `queue_sequence` (`1..n`) dan `registration_number` (`270001`).
   - Memetakan `interview_batch_id` berdasarkan rentang `sequence_start`–`sequence_end` (70/hari).
   - Memicu regenerasi PDF Bukti Pendaftaran Final dan pengiriman pesan WhatsApp via Fonnte.
   - Mencatat aktivitas ke Audit Log.
2. `LookupApplicantForInterviewAction`
   - Menerima hasil scan QR Code atau input manual `registration_number` (`270001`).
   - Menjalankan **Sequential Gate**:
     - Cek pendaftaran sudah `VERIFIED`.
     - Cek tahap sesuai role pewawancara login.
     - Tahap 2 wajib: Tahap 1 = `COMPLETED`.
     - Tahap 3 wajib: Program = `UNGGULAN` **dan** Tahap 1 & 2 = `COMPLETED`.
     - Cek *soft lock*: jika sedang `IN_PROGRESS` oleh pewawancara lain (dan belum melewati batas timeout misal 30 menit), tolak dengan pesan informatif.
3. `StartInterviewSessionAction`
   - Membuat/melanjutkan record `interview_sessions` berstatus `IN_PROGRESS` yang terikat ke `interviewer_id`.
4. `SubmitAndLockInterviewAction`
   - Validasi seluruh poin aktif tahap tersebut terisi integer `0–100`.
   - Menyimpan `interview_scores`, menghitung `total_score` dan `average_score` di server.
   - Mengubah status menjadi `COMPLETED` (`completed_at = now()`), mengunci sesi secara permanen.
   - Mencatat Audit Log (tanpa mengekspos nilai ke pewawancara setelah redirect).
5. `AdminOverrideInterviewScoreAction`
   - Hanya dapat dieksekusi oleh `admin_ppdb` atau `superadmin`.
   - Memvalidasi `reason` (wajib minimal 10 karakter) dan nilai baru `0–100`.
   - Menghitung ulang `total_score` dan `average_score` tahap terkait.
   - Menyimpan JSON *before/after diff*, alasan, dan aktor ke tabel Audit Log.
6. `ExportInterviewRecapCsvAction`
   - Menyusun *streamed CSV* per tahap (kolom dinamis mengikuti kriteria aktif) dengan sanitasi *anti-formula injection* (`'` prefix untuk sel berawalan `=`, `+`, `-`, `@`).

---

## 6. Spesifikasi UI/UX (Blade + Tailwind CSS v4 + Vanilla JS)

- **Identitas Visual:** Tetap menggunakan palet MINSATAMA (*Emerald Green* `#0B6E3F`, *Cyan/Blue* `#0F7FA3`, *Gold Accent* `#F2C94C`, latar putih bersih).
- **Portal Pewawancara (`/wawancara` — Mobile-First):**
  - Header menampilkan identitas tahap aktif (misal: *"Pos Wawancara Tahap 1 — Baca Tulis Al-Qur'an"*), nama penguji, dan penghitung ringkas (*"Selesai hari ini: X siswa"* tanpa angka nilai).
  - Area Scan QR berbasis kamera (`html5-qrcode` via lokal/CDN ringan) + kolom ketik manual nomor `270001` dengan tombol *Cari*.
  - Halaman Penilaian menampilkan **Pas Foto 3x4**, Nama Lengkap, Nomor Urut, Program, lalu input angka besar (`type="number" min="0" max="100"`) agar nyaman disentuh di layar HP/tablet.
  - Modal konfirmasi ganda sebelum menekan **"Simpan & Kunci Nilai"**.
- **Panel Admin Rekap (`/admin/interview-recaps`):**
  - Tabel responsif dengan filter: Batch/Hari, Program (Reguler/Unggulan), Status Kelengkapan Tahap, dan pencarian Nomor/Nama.
  - Kolom terpisah jelas: **Tahap 1 (Rincian + Jml + Rata²)**, **Tahap 2 (Rincian + Jml + Rata²)**, **Tahap 3 (Rincian + Jml + Rata²)**.
  - Tombol **Koreksi Nilai** per tahap (modal dengan kolom alasan wajib) dan tombol **Ekspor CSV**.
