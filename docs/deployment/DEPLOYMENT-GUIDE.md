# PANDUAN DEPLOYMENT PRODUKSI (DEPLOYMENT GUIDE)
## Sistem Informasi PPDB Online MIN 1 Kota Madiun (PPDB Minsatama)
### Tahun Ajaran 2026/2027

**Versi Dokumen**: 1.0.0
**Tanggal**: 19 November 2026
**Penulis**: Senior Software Engineer & Deployment Engineer
**Target Lingkungan**: Hostinger Web/Cloud Hosting atau Linux VPS (Ubuntu 22.04/24.04 LTS)

---

> [!WARNING]
> **Peringatan Kritis Keamanan Produksi**:
> 1. **DILARANG KERAS** menjalankan `php artisan migrate:fresh` atau `php artisan migrate:reset` pada lingkungan produksi karena akan menghapus seluruh data pendaftar dan file privat!
> 2. **DILARANG MENGUBAH** `APP_KEY` dan `NIK_HMAC_KEY` setelah pendaftaran dimulai. Perubahan salah satu key ini akan menyebabkan seluruh NIK yang terenkripsi dan hash pencarian tidak dapat dibaca kembali (*permanent data corruption*).
> 3. Jangan menaruh file `.env` atau kredensial database ke dalam Git repository.

---

## 1. PRASYARAT SERVER PRODUKSI

Sebelum memulai deployment, pastikan server tujuan memenuhi spesifikasi berikut:
- **Sistem Operasi**: Linux x86_64 (CloudLinux, AlmaLinux, Ubuntu, atau Debian).
- **Web Server**: Nginx atau Apache 2.4+ (dengan modul `mod_rewrite`).
- **PHP**: PHP 8.3 dengan ekstensi aktif:
  `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `simplexml`, `tokenizer`, `xml`, `xmlwriter`, `zip`.
- **Basis Data**: MySQL 8.x atau MariaDB 10.4+ (Default Storage Engine: InnoDB, Charset: `utf8mb4`, Collation: `utf8mb4_unicode_ci`).
- **SSL Certificate**: HTTPS wajib aktif (Let's Encrypt atau SSL Provider).
- **Node.js & NPM**: Node 20+ / 22+ (hanya jika proses build asset dilakukan langsung di server).

---

## 2. LANGKAH-LANGKAH DEPLOYMENT TAHAP DEMI TAHAP

### Langkah 1: Kloning Kode Program dari Repositori
Jalankan melalui terminal SSH di server hosting:
```bash
cd /home/username/public_html
git clone https://github.com/AhmadDFN/PPDB_Minsa_2026.git ppdb_app
cd ppdb_app
```

### Langkah 2: Pemasangan Dependensi PHP Produksi
Pasang dependensi Composer tanpa menyertakan dev-packages:
```bash
composer install --no-dev --optimize-autoloader --no-interaction
```

### Langkah 3: Konfigurasi Berkas Lingkungan (`.env`)
Salin template berkas `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sunting berkas `.env` menggunakan nano/vim dan sesuaikan konfigurasi produksi:
```ini
APP_NAME="PPDB MIN 1 Kota Madiun"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://ppdb.min1kotamadiun.sch.id

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_db_produksi
DB_USERNAME=nama_user_db
DB_PASSWORD=password_db_rahasia_dan_kuat

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

FILESYSTEM_DISK=local

# KUNCI KRIPTOGRAFI KHUSUS PPDB (Wajib diisi string rahasia independen)
NIK_HMAC_KEY=
OTP_HMAC_KEY=
OTP_DELIVERY_DRIVER=log # Ganti dengan driver gateway resmi jika provider WhatsApp madrasah telah aktif
```

### Langkah 4: Pembuatan Kunci Aplikasi dan HMAC
Jalankan generator kunci aplikasi Laravel:
```bash
php artisan key:generate
```
Buat kunci acak 64 karakter untuk `NIK_HMAC_KEY` dan `OTP_HMAC_KEY` menggunakan perintah PHP terisolasi:
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```
Salin kedua nilai unik di atas ke dalam baris `NIK_HMAC_KEY` dan `OTP_HMAC_KEY` pada file `.env`. Simpan dan amankan file `.env`.

### Langkah 5: Kompilasi Aset Frontend (Tailwind CSS v4 + Vite)
Jika Node.js terpasang di server:
```bash
npm ci --omit=dev
npm run build
```
*(Catatan: Jika server hosting tidak memiliki Node.js, kompilasi build dapat dilakukan di komputer lokal pengembang, lalu direktori `public/build/` diunggah ke server)*.

### Langkah 6: Pengaturan Hak Akses Direktori (*File Permissions*)
Beri izin baca dan tulis pada direktori cache dan penyimpanan berkas:
```bash
chmod -R 775 storage bootstrap/cache
# Jika menggunakan Apache/Nginx dengan user www-data:
# chown -R www-data:www-data storage bootstrap/cache
```

Pastikan direktori privat `storage/app/private` telah terbentuk:
```bash
mkdir -p storage/app/private/ppdb/documents
mkdir -p storage/app/private/ppdb/pdf
```

### Langkah 7: Eksekusi Migrasi Basis Data
Jalankan migrasi database dengan flag `--force`:
```bash
php artisan migrate --force
```

### Langkah 8: Inisialisasi Peran & Izin Administrator
Jalankan seeder Spatie Permission:
```bash
php artisan db:seed --class=RoleAndPermissionSeeder --force
```

### Langkah 9: Pembuatan Akun Super Administrator Pertama
Jalankan tinker atau seeder admin madrasah:
```bash
php artisan tinker
```
Di dalam tinker, eksekusi pembuatan akun admin:
```php
$admin = \App\Models\User::create([
    'name' => 'Administrator PPDB Minsatama',
    'email' => 'admin.ppdb@min1kotamadiun.sch.id',
    'password' => \Illuminate\Support\Facades\Hash::make('PasswordSuperKuatDanAman123!'),
    'account_type' => \App\Enums\AccountType::ADMIN,
    'account_status' => \App\Enums\AccountStatus::ACTIVE,
    'email_verified_at' => now(),
]);
$admin->assignRole('super_admin');
exit;
```

### Langkah 10: Optimasi Cache Produksi
Jalankan rangkaian perintah optimasi Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 3. PROSEDUR PEMBARUAN APLIKASI (DEPLOYMENT UPDATE PROCEDURE)

Ketika terdapat pembaruan kode di masa mendatang:
```bash
# 1. Masuk ke mode pemeliharaan (jika pembaruan besar)
php artisan down --secret="minsatama-bypass-key"

# 2. Ambil pembaruan kode
git pull origin main

# 3. Pasang dependensi terbaru
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

# 4. Jalankan migrasi tambahan jika ada
php artisan migrate --force

# 5. Segarkan cache aplikasi
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Nonaktifkan mode pemeliharaan
php artisan up
```

---

## 4. PROSEDUR ROLLBACK BILA TERJADI KENDALA
Jika terjadi anomali pasca-deployment:
```bash
# 1. Bersihkan seluruh cache aplikasi
php artisan optimize:clear

# 2. Kembalikan kode ke commit sebelumnya
git checkout HEAD~1

# 3. Segarkan cache kembali
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
