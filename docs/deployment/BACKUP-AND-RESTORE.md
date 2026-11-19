# PANDUAN PENCADANGAN DAN PEMULIHAN (BACKUP & RESTORE)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Versi Dokumen**: 1.0.0
**Tanggal**: 19 November 2026
**Sasaran**: Administrator Server / Database Engineer / Tim IT MIN 1 Kota Madiun

---

> [!IMPORTANT]
> **Prinsip Utama Integritas Data PPDB Minsatama**:
> Basis data MySQL dan media penyimpanan berkas privat (`storage/app/private/`) adalah **dua komponen yang saling terikat secara kriptografis**:
> 1. Setiap baris dokumen persyaratan di tabel `registration_documents` dan `registration_document_revisions` mencatat hash `file_hash` (SHA-256) dan path fisik di storage.
> 2. Setiap baris versi PDF di tabel `registration_pdf_versions` mencatat `file_hash` (SHA-256), `file_size_bytes`, dan hash token verifikasi.
> 3. Oleh karena itu, **pencadangan database dan berkas privat WAJIB dilakukan secara bersamaan (sinkron)** untuk mencegah inkonsistensi metadata.

---

## 1. KOMPONEN YANG HARUS DICADANGKAN

1. **Basis Data MySQL**:
   - Skema dan seluruh tabel pendaftaran, pendaftar, pengguna, log audit, consent, dokumen, dan sequence.
2. **Berkas Dokumen Persyaratan**:
   - Direktori `storage/app/private/ppdb/documents/` (kartu keluarga, akta kelahiran, pas foto).
3. **Berkas PDF Bukti Pendaftaran Resmi**:
   - Direktori `storage/app/private/ppdb/pdf/` (seluruh draf dan versi PDF final pendaftar).
4. **Berkas Konfigurasi Rahasia (`.env`)**:
   - Memuat `APP_KEY`, `NIK_HMAC_KEY`, `OTP_HMAC_KEY`, serta kredensial basis data.

---

## 2. PROSEDUR PENCADANGAN (BACKUP PROCEDURE)

### A. Pencadangan Basis Data MySQL (Otomatis / Harian)
Gunakan perintah `mysqldump` dengan opsi `--single-transaction` untuk memastikan konsistensi ACID InnoDB tanpa mengunci tabel:
```bash
#!/bin/bash
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/home/username/backups/ppdb"
DB_NAME="nama_db_produksi"
DB_USER="nama_user_db"
DB_PASS="password_db_rahasia"

mkdir -p $BACKUP_DIR/db

mysqldump --user=$DB_USER --password=$DB_PASS \
    --host=127.0.0.1 \
    --single-transaction \
    --quick \
    --default-character-set=utf8mb4 \
    --routines --triggers \
    $DB_NAME | gzip > "$BACKUP_DIR/db/ppdb_backup_${DATE}.sql.gz"

echo "Backup basis data selesai: ppdb_backup_${DATE}.sql.gz"
```

### B. Pencadangan Berkas Penyimpanan Privat
Cadangkan seluruh direktori `storage/app/private/` ke dalam arsip terkompresi:
```bash
#!/bin/bash
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/home/username/backups/ppdb"
APP_DIR="/home/username/public_html/ppdb_app"

mkdir -p $BACKUP_DIR/storage

tar -czf "$BACKUP_DIR/storage/ppdb_storage_${DATE}.tar.gz" -C "$APP_DIR/storage/app" private

echo "Backup storage selesai: ppdb_storage_${DATE}.tar.gz"
```

### C. Pencadangan Kunci Rahasia Lingkungan (`.env`)
Simpan berkas `.env` secara aman di brankas terenkripsi (*offline vault* / *password manager* institusi):
```bash
cp /home/username/public_html/ppdb_app/.env /home/username/backups/ppdb/env_backup_${DATE}.env
chmod 600 /home/username/backups/ppdb/env_backup_${DATE}.env
```

---

## 3. PROSEDUR PEMULIHAN BENCANA (DISASTER RECOVERY / RESTORE)

> [!CAUTION]
> Jangan pernah menguji proses restore langsung pada server produksi yang sedang melayani pendaftar! Lakukan pengujian pemulihan hanya pada server terisolasi (*staging/disposable database*).

### Langkah 1: Siapkan Server Tujuan
Pastikan PHP 8.3, web server, dan MySQL telah siap sesuai petunjuk `DEPLOYMENT-GUIDE.md`.

### Langkah 2: Pemulihan Berkas Program dan Konfigurasi
1. Kloning kode program dari Git.
2. Salin berkas cadangan `.env` ke direktori proyek.
3. Jalankan `composer install --no-dev --optimize-autoloader`.

### Langkah 3: Pemulihan Basis Data MySQL
Ekstrak dan pulihkan basis data:
```bash
gunzip < /path/to/backup/ppdb_backup_YYYYMMDD_HHMMSS.sql.gz | mysql -u nama_user -p nama_db_produksi
```

### Langkah 4: Pemulihan Direktori Penyimpanan Privat
Ekstrak arsip penyimpanan privat ke dalam direktori `storage/app/`:
```bash
tar -xzf /path/to/backup/ppdb_storage_YYYYMMDD_HHMMSS.tar.gz -C /home/username/public_html/ppdb_app/storage/app
chmod -R 775 /home/username/public_html/ppdb_app/storage
```

### Langkah 5: Verifikasi Integritas Kriptografis Pasca-Restore
Jalankan skrip verifikasi integritas untuk memastikan berkas pada disk cocok 100% dengan hash SHA-256 di database:
```bash
php artisan tinker --execute="
\$docs = \App\Models\RegistrationDocument::all();
\$mismatch = 0;
foreach (\$docs as \$d) {
    if (!\Illuminate\Support\Facades\Storage::disk('ppdb_private')->exists(\$d->file_path)) {
        echo 'Missing file: ' . \$d->file_path . PHP_EOL;
        \$mismatch++;
        continue;
    }
    \$hash = hash('sha256', \Illuminate\Support\Facades\Storage::disk('ppdb_private')->get(\$d->file_path));
    if (!hash_equals(\$d->file_hash, \$hash)) {
        echo 'Hash mismatch on: ' . \$d->file_path . PHP_EOL;
        \$mismatch++;
    }
}
echo 'Verifikasi selesai. Total dokumen: ' . \$docs->count() . ', anomali: ' . \$mismatch . PHP_EOL;
"
```

---

## 4. KEBIJAKAN FREKUENSI DAN RETENSI PENCADANGAN

- **Frekuensi Pencadangan Basis Data**:
  - Saat periode pendaftaran dibuka: Setiap 6 jam sekali (atau minimal harian pukul 23:59 WIB).
  - Di luar periode pendaftaran: Mingguan.
- **Frekuensi Pencadangan Berkas Storage**:
  - Harian secara inkremental (*rsync*) atau arsip harian.
- **Masa Retensi Cadangan**:
  - Cadangan harian disimpan selama 30 hari kalender.
  - Cadangan arsip final penutupan PPDB disimpan permanen selama 1 tahun ajaran hingga proses PPDB tahun berikutnya dimulai.
- **Pertimbangan RPO & RTO**:
  - **RPO (Recovery Point Objective)**: Maksimal data tertinggal adalah jeda jadwal backup terakhir (rekomendasi 6 jam saat beban puncak).
  - **RTO (Recovery Time Objective)**: Estimasi waktu pemulihan penuh sistem dari arsip backup adalah 30 hingga 60 menit pada infrastruktur yang telah disiapkan.
