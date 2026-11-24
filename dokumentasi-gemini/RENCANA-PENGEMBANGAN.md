# RENCANA PENGEMBANGAN SISTEM PPDB MINSATAMA
### Peta Jalan Implementasi, Kesiapan Go-Live, dan Strategi Keberlanjutan

Dokumen ini memuat peta rencana strategis bagi pengelola sistem, panitia PPDB, dan tim teknologi informasi MIN 1 Kota Madiun untuk mengawal fase peluncuran resmi (*go-live*), operasional pelayanan, serta peningkatan sistem di masa depan.

---

## 1. Rencana Fase Pra-Peluncuran (*Pre-Launch Readiness*)

Sebelum portal pendaftaran dibuka secara resmi untuk publik luas, langkah-langkah persiapan berikut wajib diselesaikan:

| No | Rencana Tindakan | Penanggung Jawab | Target Waktu | Indikator Keberhasilan |
|:---:|---|---|---|---|
| 1 | **Konfigurasi Variabel Produksi (.env)** | Superadmin & DevOps | H-7 Peluncuran | `APP_ENV=production`, `APP_DEBUG=false`, `PPDB_CONSENT_VERSION=v1.0`, dan redaksi consent resmi terisi. |
| 2 | **Aktivasi Saluran WhatsApp Gateway** | Tim IT / Superadmin | H-5 Peluncuran | Token Fonnte terisi, perangkat terhubung (*CONNECTED*), saldo kuota mencukupi (minimal 2.000 pesan). |
| 3 | **Penetapan Master Periode & Syarat Dokumen** | Panitia PPDB & Kepala Madrasah | H-3 Peluncuran | Periode PPDB TP 2026/2027 aktif, kuota indikatif per program terdaftar, syarat dokumen 5 MB tervalidasi. |
| 4 | **Uji Coba Bersama Panitia Loket PTSP** | Seluruh Panitia PPDB | H-2 Peluncuran | Panitia memahami alur verifikasi berkas digital, pemindaian QR Code, dan mekanisme pembukaan revisi (*reopen*). |
| 5 | **Simulasi Beban & Penguncian Hak Akses** | Senior Engineer | H-1 Peluncuran | Cache teroptimasi (`config:cache`, `route:cache`, `view:cache`), seluruh akun uji dihapus/direset secara bersih. |

---

## 2. Rencana Operasional Selama Masa Pendaftaran Aktif

Saat gerbang pendaftaran daring dibuka untuk umum:

### 2.1 Pemantauan Server & Lalu Lintas Harian
- **Monitoring CPU & RAM Hostinger**: Memantau grafik utilisasi sumber daya pada hPanel Hostinger, terutama pada 3 hari pertama pembukaan pendaftaran (jam sibuk 08.00 – 12.00 WIB).
- **Log Error Monitoring**: Memeriksa `storage/logs/laravel.log` secara harian untuk mendeteksi potensi kegagalan koneksi pihak ketiga (Fonnte API timeout) atau berkas gagal unggah.

### 2.2 Tata Kelola Pelayanan Loket PTSP Fisik
- **Jam Operasional**: Pelayanan verifikasi fisik dokumen asli di loket PTSP madrasah dibuka pukul **07.00 – 15.00 WIB** (hari kerja).
- **Verifikasi Berkas Harian**: Panitia memverifikasi dokumen digital yang masuk di panel `/admin/document-verification` setiap hari agar antrean tidak menumpuk saat orang tua datang membawa berkas fisik.

---

## 3. Strategi Pencadangan & Pemulihan Data (*Backup & Recovery*)

Data pendaftaran calon peserta didik baru merupakan arsip negara yang sangat krusial. Strategi pencadangan wajib dijalankan secara konsisten:

### 3.1 Frekuensi & Objek Pencadangan
1. **Basis Data MySQL**:
   - Frekuensi: Dicadangkan secara otomatis **setiap hari pada pukul 23.00 WIB**.
   - Metode: `mysqldump` terkompresi (`.sql.gz`) via cron job server Hostinger atau backup otomatis hPanel.
2. **Penyimpanan Berkas Digital Privat (`storage/app/private/ppdb`)**:
   - Frekuensi: Dicadangkan **setiap 3 hari sekali**.
   - Objek: Seluruh berkas scan KK, Akta Kelahiran, Pas Foto, dan berkas PDF pendaftaran.
3. **Berkas Konfigurasi Rahasia (`.env.production`)**:
   - Disimpan secara luring (*offline*) di flashdisk fisik terenkripsi milik Kepala Madrasah / Superadmin.

### 3.2 Target Waktu Pemulihan (RTO & RPO)
- **Recovery Point Objective (RPO)**: Maksimal **24 jam** kehilangan data transaksi jika terjadi kegagalan server total.
- **Recovery Time Objective (RTO)**: Sistem harus dapat dipulihkan kembali dan melayani pendaftar dalam waktu maksimal **2 jam**.

---

## 4. Peta Jalan Fitur Jangka Menengah & Jangka Panjang (Phase 11 & Beyond)

Setelah implementasi inti selesai, pengembangan sistem dapat diperluas untuk siklus tahun ajaran berikutnya:

### 4.1 Tahap 1: Broadcast WhatsApp Otomatis Berbasis Status Verifikasi
- Mengirimkan pesan WhatsApp otomatis ke orang tua ketika dokumen persyaratan disetujui (*APPROVED*) atau membutuhkan perbaikan (*REVISION_REQUIRED*).
- Menghilangkan kebutuhan orang tua mengecek portal secara berulang-ulang.

### 4.2 Tahap 2: Dasbor Analitik & Peta Sebaran Pendaftar
- Menampilkan grafik demografi asal kecamatan/kelurahan calon siswa secara visual (GIS mapping).
- Menganalisis sebaran usia rata-rata calon siswa yang mendaftar di Program Reguler vs Program Unggulan.

### 4.3 Tahap 3: Arsip Digital Akreditasi Madrasah
- Menghasilkan bundel berkas digital pendaftar per rombel dalam format ZIP terstruktur untuk mempermudah panitia saat pelaksanaan visitasi Akreditasi Madrasah oleh Badan Akreditasi Nasional (BAN-PDM).
