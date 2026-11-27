# DEVELOPMENT GUIDE — PPDB MINSATAMA
## Panduan Teknis Lingkungan Pengembangan, Pengujian, dan Kontribusi Kode

---

## 1. PRASYARAT LINGKUNGAN PENGEMBANGAN

Sebelum menjalankan proyek, pastikan perangkat lokal telah terpasang:
- **PHP**: Versi 8.3 atau lebih baru (`php -v`).
  - Ekstensi PHP aktif: `pdo_sqlite`, `pdo_mysql`, `openssl`, `mbstring`, `intl`, `gd`, `curl`, `fileinfo`, `bcmath`, `zip`.
- **Composer**: Versi 2.7+ (`composer -V`).
- **Node.js**: Versi 20+ atau 24 LTS (`node -v`).
- **NPM**: Versi 9+ (`npm -v`).
- **Basis Data**: MySQL 8.x / MariaDB (untuk target produksi/lokal) atau SQLite (untuk pengujian otomatis).

---

## 2. PENGATURAN AWAL PROYEK (INSTALLATION & SETUP)

### 2.1 Kloning & Instalasi Dependensi
```bash
# Masuk ke direktori proyek
cd "d:\Min 1 Kota Madiun\Program\PPDB_2026"

# Instal dependensi backend via Composer
composer install

# Instal dependensi frontend via NPM
npm install
```

### 2.2 Konfigurasi Berkas Lingkungan (`.env`)
Salin berkas template lingkungan jika belum ada:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan parameter kriptografi khusus PPDB Minsatama telah ditentukan pada berkas `.env`:
```env
APP_NAME="PPDB Minsatama"
APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

# Konfigurasi Basis Data MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ppdb_minsatama
DB_USERNAME=root
DB_PASSWORD=

# Buat dua nilai acak dan independen menggunakan CSPRNG, lalu simpan hanya di .env lokal.
# Contoh generator lokal (jalankan terpisah untuk masing-masing key):
# php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
# Jangan membagikan atau mencatat nilai. Jangan ganti APP_KEY/NIK_HMAC_KEY jika data bergantung padanya.
NIK_HMAC_KEY=
OTP_HMAC_KEY=

# Konfigurasi OTP
OTP_LENGTH=6
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=5
OTP_DELIVERY_DRIVER=log
```

---

## 3. MENJALANKAN APLIKASI DI LOKAL

Gunakan dua terminal terpisah:

**Terminal 1 — Server Pengembangan Laravel:**
```bash
php artisan serve
# Aplikasi dapat diakses melalui http://localhost:8000
```

**Terminal 2 — Compiler Asset Vite:**
```bash
npm run dev
# Atau untuk membuat build produksi:
npm run build
```

---

## 4. MENJALANKAN BASIS DATA & SEEDER

```bash
# Menjalankan seluruh migrasi
php artisan migrate

# Menjalankan seeder master (Program PPDB, Peran & Akun Superadmin)
php artisan db:seed

# PERINGATAN: DILARANG menjalankan migrate:fresh pada database produksi!
```

Kredensial bawaan Superadmin (setelah seeding):
- **Email**: `superadmin@minsatama.sch.id`
- **Kata Sandi**: `[KREDENSIAL-DIHAPUS]`
- **Portal Masuk**: `http://localhost:8000/admin/login`

---

## 5. MENJALANKAN PENGUJIAN OTOMATIS (TEST SUITE)

Seluruh pengujian dijalankan di memori terisolasi (`sqlite :memory:`) sesuai konfigurasi `phpunit.xml`.

```bash
# Menjalankan seluruh test suite
php artisan test

# Menjalankan kelompok pengujian tertentu
php artisan test tests/Feature/Auth
php artisan test tests/Unit/OtpServiceSecurityTest.php

# Menjalankan pengujian dengan filter nama method
php artisan test --filter=test_active_applicant_can_login
```

---

## 6. FORMAT KODE & LINTING (LARAVEL PINT)

Setiap agen pengembang wajib memastikan kepatuhan standar format PSR-12 sebelum melakukan commit:

```bash
# Memeriksa kepatuhan kode (dry-run)
vendor/bin/pint --test

# Memperbaiki format kode secara otomatis
vendor/bin/pint
```

---

## 7. AUDIT KEAMANAN BERKALA

```bash
# Audit celah keamanan paket Composer
composer audit

# Audit celah keamanan paket NPM
npm audit
```

---

## 8. ALUR KERJA GIT (GIT WORKFLOW)

- Gunakan konvensi penamaan commit baku (*Conventional Commits*):
  - `feat(auth): add password recovery flow`
  - `fix(otp): adjust rate limiting threshold`
  - `docs(status): update Phase 3 completion report`
  - `test(database): add constraint verification test`
- Selalu pastikan branch bersih dan sinkron sebelum memulai fase baru.

---

## 9. PANDUAN DEPLOYMENT & LINGKUNGAN HOSTING

Untuk instruksi rinci deployment aplikasi ke lingkungan server:
1. **Lingkungan Staging (Hostinger)**:
   - Baca [docs/deployment/HOSTINGER-STAGING-GUIDE.md](deployment/HOSTINGER-STAGING-GUIDE.md).
   - Target preview: `https://sleek-forestgreen-mink-13.hostingersite.com`.
2. **Lingkungan Produksi (VPS Mandiri dengan aaPanel)**:
   - Baca [docs/deployment/AAPANEL-PRODUCTION-GUIDE.md](deployment/AAPANEL-PRODUCTION-GUIDE.md).
   - Arsitektur: Ubuntu Linux, Nginx FastCGI, PHP 8.3 FPM, MariaDB 10.11/MySQL 8, Redis, Supervisor, Let's Encrypt SSL.
   - **PERINGATAN**: Dilarang melakukan deployment produksi sebelum persetujuan resmi Go-Live dari Pemilik Proyek.
