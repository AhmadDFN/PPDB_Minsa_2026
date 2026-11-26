# Dataset Wilayah Administratif Indonesia

Snapshot lokal untuk cascade alamat PPDB. Aplikasi membaca empat CSV dari direktori ini saat menjalankan `AdministrativeRegionSeeder`; tidak ada permintaan runtime ke geocoder atau API pihak ketiga.

## Sumber dan lisensi

- Sumber snapshot CSV: [prodhokter/dataset-wilayah-indonesia](https://github.com/prodhokter/dataset-wilayah-indonesia), dengan rujukan data 2025 dan lisensi MIT. Teks lisensi yang menyertai sumber tersimpan di `LICENSE`.
- Dataset asal: [cahyadsn/wilayah](https://github.com/cahyadsn/wilayah), merujuk Kepmendagri No. 300.2.2-2138 Tahun 2025. Teks lisensi upstream tersimpan di `LICENSE-WILAYAH-UPSTREAM.txt`.
- Snapshot mencakup 38 provinsi, 514 kabupaten/kota, 7.285 kecamatan, dan 83.762 desa/kelurahan (91.599 entri).

## Berkas dan hierarki

CSV UTF-8 dengan header; kode disimpan sebagai string agar leading zero tidak hilang.

| Berkas | Kolom |
|---|---|
| `provinces.csv` | `id,name` |
| `regencies.csv` | `id,province_id,name` |
| `districts.csv` | `id,regency_id,name` |
| `villages.csv` | `id,district_id,name` |

Seeder memetakan `id` ke `code`, kolom induk ke `parent_code`, serta mengimpor per batch setelah tabel `administrative_regions` dimigrasikan.

## Memperbarui snapshot

1. Periksa versi dan lisensi sumber sebelum mengganti CSV.
2. Pastikan jumlah baris, kode unik, seluruh relasi induk, panjang kode, dan nama wilayah valid.
3. Uji `php artisan db:seed --class=AdministrativeRegionSeeder --force` pada database lokal, lalu jalankan suite dan browser cascade.
4. Catat tanggal snapshot serta perubahan jumlah/kode pada log proyek.

Alamat rumah tidak dikirim ke sumber dataset atau layanan eksternal.
