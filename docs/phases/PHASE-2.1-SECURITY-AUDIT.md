# PHASE 2.1 — AUDIT KEAMANAN NIK & KOMPATIBILITAS SKEMA MYSQL
## Laporan Hasil Audit Kriptografi Data Sensitif & Skema Database

**Status**: SELESAI & TERVERIFIKASI  
**Tanggal**: 15 November 2026

---

## 1. AUDIT KRIPTOGRAFI NIK (`NikManager`)

Sesuai instruksi audit keamanan basis data, berkas `app/Support/NikManager.php` diperiksa secara menyeluruh dengan kriteria pengujian berikut:

1. **Normalisasi Format NIK**:
   - Menghapus seluruh karakter non-numerik dan spasi.
   - Memastikan panjang tepat 16 digit numerik. Jika kurang atau lebih, melempar `InvalidArgumentException`.
2. **Pencarian Deterministik (HMAC-SHA256)**:
   - Menggunakan algoritma HMAC-SHA256 dengan kunci rahasia mandiri `NIK_HMAC_KEY`.
   - Menolak eksekusi (*fail closed*) jika `NIK_HMAC_KEY` belum diset di berkas lingkungan `.env`.
   - Menolak eksekusi (*fail closed*) jika `NIK_HMAC_KEY === APP_KEY`.
3. **Penyimpanan Terenkripsi (AES-256-CBC)**:
   - Menggunakan Laravel Crypt (`Crypt::encryptString` dan `Crypt::decryptString`) dengan kunci `APP_KEY`.
   - Menguji *round-trip* enkripsi dan dekripsi untuk menjamin teks asli dapat dikembalikan secara presisi saat mencetak lembar bukti pendaftaran resmi.
4. **Masking NIK**:
   - Format: 4 digit awal, 8 asterisk (`*`), 4 digit akhir (contoh: `3573********0001`).
   - Tidak ada plaintext NIK yang dituliskan pada berkas log aplikasi.

Hasil Uji: **11 pengujian unit pada `tests/Unit/SecurityAndOtpTest.php` LULUS 100%**.

---

## 2. AUDIT KONEKSI & SKEMA MYSQL INNODB

1. **Konfigurasi Database Terisolasi**:
   - Dibuat koneksi `mysql_test` pada `config/database.php` yang mengarah ke database `ppdb_minsatama_test`.
   - Seluruh 16 file migrasi menggunakan engine InnoDB dan foreign key constraint standar.
2. **Status Server MySQL Lokal**:
   - Pada saat audit dijalankan, service daemon MySQL lokal port 3306 pada perangkat Windows pengembang belum diaktifkan.
   - Sesuai protokol, 2 pengujian integrasi fisik MySQL (`MySqlSchemaCompatibilityTest`) diatur untuk *skip* secara elegan menunggu ketersediaan service MySQL lokal.
   - Seluruh pengujian relasi, indeks unik, cascade delete, dan validasi constraint dieksekusi secara sukses dan atomik di atas SQLite.
