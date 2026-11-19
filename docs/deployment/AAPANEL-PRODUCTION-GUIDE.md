# PANDUAN DEPLOYMENT PRODUKSI AAPANEL (VPS LINUX)
## Sistem Informasi PPDB MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Target Lingkungan**: Server VPS Mandiri (Ubuntu 22.04 LTS / 24.04 LTS atau Debian 12)  
**Control Panel**: aaPanel v6.8+ / v7.x  
**Web Server**: Nginx 1.24+ (Reverse Proxy / FastCGI)  
**Database**: MariaDB 10.11+ LTS / MySQL 8.0+ (InnoDB Engine, UTF8MB4)  
**Runtime**: PHP 8.3 FPM  
**Cache & Queue**: Redis 7.x (Opsional/Direkomendasikan untuk Lonjakan Beban Pendaftar)  
**Sertifikat Keamanan**: Let's Encrypt SSL (Auto-Renewal)

---

> [!IMPORTANT]
> **DOKUMEN PERSIAPAN ARSITEKTUR PRODUKSI**:
> Dokumen ini merupakan cetak biru resmi untuk persiapan deployment server produksi masa depan.
> **DILARANG MELAKUKAN DEPLOYMENT KE SERVER PRODUKSI/AAPANEL PADA FASE INI** tanpa instruksi tertulis eksplisit dan persetujuan Go-Live dari Pemilik Proyek.

---

## 1. SPESIFIKASI MINIMUM & REKOMENDASI SERVER PRODUKSI

Untuk melayani estimasi 500–1.000 pendaftar secara simultan pada hari pembukaan PPDB tanpa hambatan performa:

| Komponen Hardware | Spesifikasi Minimum | Spesifikasi Rekomendasi (Direkomendasikan) |
|---|---|---|
| **CPU / Processor** | 2 vCPU Core (2.4 GHz+) | 4 vCPU Core (High Performance Cloud) |
| **RAM / Memory** | 4 GB ECC DDR4 | 8 GB ECC DDR4 |
| **Penyimpanan (Disk)** | 40 GB NVMe SSD | 80 GB NVMe SSD (Enterprise Grade) |
| **Bandwidth & Jaringan** | 100 Mbps Unmetered | 1 Gbps Port Speed |
| **Sistem Operasi** | Ubuntu Server 22.04 LTS x64 | Ubuntu Server 24.04 LTS x64 |

---

## 2. DAFTAR PERANGKAT LUNAK (SOFTWARE STACK) AAPANEL

Pastikan paket-paket berikut diinstal melalui menu **App Store** pada dashboard aaPanel:

1. **Nginx 1.24+**: Web server utama berperforma tinggi.
2. **PHP 8.3**: Engine runtime utama Laravel.
   - Ekstensi PHP wajib yang harus diinstal via aaPanel (menu *PHP 8.3 > Extensions*):
     - `fileinfo` (Wajib untuk validasi MIME berkas unggahan).
     - `gd` atau `imagick` (Wajib untuk pemrosesan pas foto dan rendering PNG pada Dompdf).
     - `redis` (Untuk manajemen antrean dan cache performa tinggi).
     - `opcache` (Untuk akselerasi eksekusi bytecode PHP).
     - `exif` (Untuk pembacaan metadata orientasi foto).
     - `pdo_mysql`, `mbstring`, `openssl`, `curl`, `xml`, `zip`.
3. **MariaDB 10.11+** atau **MySQL 8.0+**: Database engine relasional.
4. **Redis 7.x**: Manajemen in-memory queue & caching.
5. **Supervisor**: Manajer proses background antrean pengiriman pesan WhatsApp OTP.

---

## 3. OPTIMASI KONFIGURASI PHP 8.3 FPM

Buka panel aaPanel > **App Store** > **PHP 8.3** > **Configuration**:

```ini
; Alokasi memori untuk Dompdf dan pemrosesan gambar
memory_limit = 512M

; Batas unggah berkas persyaratan (KK, Akta, Pas Foto)
upload_max_filesize = 16M
post_max_size = 20M

; Waktu eksekusi maksimum untuk rendering PDF dokumen kompleks
max_execution_time = 120
max_input_time = 120

; Timezone resmi madrasah
date.timezone = Asia/Jakarta

; Opcache Settings untuk Production
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 1
```

---

## 4. KONFIGURASI VIRTUAL HOST NGINX PRODUKSI

Pada dashboard aaPanel, tambahkan website baru (misal: `ppdb.min1kotamadiun.sch.id`).
Buka **Websites** > Klik nama domain > **Configuration**, masukkan konfigurasi blok server Nginx berikut:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name ppdb.min1kotamadiun.sch.id;
    index index.php index.html;
    root /www/wwwroot/ppdb.min1kotamadiun.sch.id/public;

    # Konfigurasi Sertifikat SSL (Dikelola otomatis oleh aaPanel Let's Encrypt)
    # ssl_certificate ...
    # ssl_certificate_key ...
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers Wajib Produksi
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    charset utf-8;

    # Maksimum ukuran payload unggah
    client_max_body_size 20M;

    # Penanganan URL Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Blokir akses langsung ke berkas penyimpanan privat
    location ^~ /storage/app/private/ {
        deny all;
        return 404;
    }

    # Blokir akses ke berkas sensitif dan tersembunyi
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # FastCGI PHP 8.3 FPM
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-83.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_read_timeout 120s;
    }

    # Caching Aset Statis Vite
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|woff|ttf|svg)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    access_log /www/wwwlogs/ppdb_access.log combined;
    error_log /www/wwwlogs/ppdb_error.log error;
}
```

---

## 5. STRUKTUR DIREKTORI & PERMISSION SERVER

Pastikan seluruh kepemilikan file dimiliki oleh user web server aaPanel (`www:www`):

```bash
# Path instalasi resmi
cd /www/wwwroot/ppdb.min1kotamadiun.sch.id

# Atur kepemilikan
chown -R www:www /www/wwwroot/ppdb.min1kotamadiun.sch.id

# Atur permission direktori dan file standar
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Direktori yang membutuhkan hak tulis oleh Nginx/PHP-FPM
chmod -R 775 storage bootstrap/cache

# Pastikan folder privat tersedia
mkdir -p storage/app/private/documents
mkdir -p storage/app/private/pdfs
chown -R www:www storage/app/private
chmod -R 750 storage/app/private
```

---

## 6. SCRIPT OTOMASI ZERO-DOWNTIME DEPLOYMENT

Buat skrip deployment otomatis `/www/wwwroot/deploy_ppdb.sh` yang dapat dipicu melalui webhook GitHub Actions atau dieksekusi secara manual:

```bash
#!/usr/bin/env bash
set -e

echo "=== MEMULAI DEPLOYMENT PRODUKSI PPDB MINSATAMA ==="
APP_DIR="/www/wwwroot/ppdb.min1kotamadiun.sch.id"
cd $APP_DIR

# 1. Masuk ke mode pemeliharaan (Maintenance Mode) jika ada migrasi berat
php artisan down --secret="minsatama-bypass-key-2026" --render="errors::503" || true

# 2. Ambil kode terbaru dari repositori
echo "Mengambil commit terbaru..."
git fetch origin main
git reset --hard origin/main

# 3. Instal dependensi Composer (Produksi saja, tanpa dev)
echo "Menginstal dependensi Composer..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Jalankan migrasi basis data secara aman
echo "Menjalankan database migration..."
php artisan migrate --force

# 5. Optimasi Cache Laravel
echo "Mengoptimalkan cache konfigurasi dan rute..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Pastikan permission direktori benar
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Restart antrean worker Supervisor jika menggunakan antrean
# supervisorctl restart ppdb-worker:*

# 8. Matikan mode pemeliharaan
php artisan up

echo "=== DEPLOYMENT PRODUKSI BERHASIL SELESAI ==="
```

---

## 7. OTOMASI PENCADANGAN HARIAN (BACKUP AUTOMATION)

Pada aaPanel, buka menu **Cron**:

1. **Pencadangan Database Harian (Pukul 01.00 WIB)**:
   - Tipe Task: *Backup Database*.
   - Target Database: Database PPDB Produksi.
   - Backup to: Local Disk / Google Drive / S3.
   - Retensi: Simpan 30 cadangan terakhir.
2. **Pencadangan Direktori Dokumen Privat Harian (Pukul 02.00 WIB)**:
   - Tipe Task: *Shell Script*.
   - Script:
     ```bash
     DATE=$(date +%Y%m%d_%H%M%S)
     BACKUP_DIR="/www/backup/ppdb_documents"
     mkdir -p $BACKUP_DIR
     tar -czf $BACKUP_DIR/docs_$DATE.tar.gz /www/wwwroot/ppdb.min1kotamadiun.sch.id/storage/app/private
     # Hapus arsip dokumen yang lebih lama dari 30 hari
     find $BACKUP_DIR -type f -name "docs_*.tar.gz" -mtime +30 -delete
     ```

---

## 8. PROSEDUR ROLLBACK DARURAT

Jika terjadi kendala fatal setelah pembaruan kode produksi:

1. **Rollback Kode ke Commit Stabil Terakhir**:
   ```bash
   cd /www/wwwroot/ppdb.min1kotamadiun.sch.id
   git log -5 --oneline
   git reset --hard <HASH_COMMIT_STABIL_SEBELUMNYA>
   ```
2. **Rollback Dependensi & Cache**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. **Rollback Basis Data (Jika Diperlukan)**:
   Gunakan file cadangan harian terbaru dari menu **aaPanel > Databases > Restore**.
