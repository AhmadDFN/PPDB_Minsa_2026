# LIVE APPLICANT E2E REPORT — 20 NOVEMBER 2026

## 1. Lingkungan dan metode

- Lingkungan: Hostinger, domain yang dikonfirmasi pemilik proyek: `https://ahmaddanyfn.biz.id`.
- Metode: pengujian black-box melalui browser oleh pemilik proyek.
- Data: akun QA; kredensial dan PII tidak disimpan dalam laporan ini.
- Status deployment: aplikasi online/testing di Hostinger. Ini bukan persetujuan Go-Live resmi.

## 2. Hasil yang dikonfirmasi pemilik proyek

| Area | Hasil | Bukti/status |
|---|---|---|
| HTTPS dan website | Berhasil diakses | OWNER CONFIRMED / LIVE TESTED |
| Login applicant | Berhasil | OWNER CONFIRMED / LIVE TESTED |
| Dashboard applicant | Berhasil dibuka | OWNER CONFIRMED / LIVE TESTED |
| Biodata | Berhasil disimpan | OWNER CONFIRMED / LIVE TESTED |
| Alamat | Berhasil disimpan | OWNER CONFIRMED / LIVE TESTED |
| Ayah dan ibu | Berhasil disimpan | OWNER CONFIRMED / LIVE TESTED |
| Wali opsional | Dapat dikosongkan | OWNER CONFIRMED / LIVE TESTED |
| Pendidikan sebelumnya | Berhasil disimpan | OWNER CONFIRMED / LIVE TESTED |
| Program Unggulan | Berhasil dipilih | OWNER CONFIRMED / LIVE TESTED |
| Upload KK, akta, pas foto | Berhasil dan tervalidasi | OWNER CONFIRMED / LIVE TESTED |
| PDF draft | Berhasil dibuat dan diunduh | OWNER CONFIRMED / LIVE TESTED |
| Halaman tinjau | Berhasil dibuka | OWNER CONFIRMED / LIVE TESTED |
| Checklist kelengkapan | Berhasil diperiksa | OWNER CONFIRMED / LIVE TESTED |
| Masking NIK | Tampil | OWNER CONFIRMED / LIVE TESTED |
| Nomor pendaftaran | Konsisten | OWNER CONFIRMED / LIVE TESTED |

## 3. Batas pengujian

- Final Submit belum terbukti berhasil pada laporan live terakhir.
- Consent versi `v1.0` belum dipastikan terbaca pada lingkungan Hostinger.
- PDF final belum dikonfirmasi berhasil.
- QR verification belum dikonfirmasi berhasil.
- Uji live Admin dan Superadmin belum memadai.

Kesimpulan: **PASS WITH BLOCKER**. Alur applicant sampai PDF draft berjalan, tetapi penerimaan akhir masih menunggu verifikasi consent, Final Submit, PDF final, QR, dan uji operasional Admin/Superadmin.

## 4. Tindak lanjut

1. Verifikasi konfigurasi consent pada Hostinger.
2. Uji Final Submit dengan akun QA baru atau reset akun uji yang disetujui pemilik.
3. Verifikasi status `SUBMITTED`, nomor registrasi, PDF final, dan QR tanpa membuka PII.
4. Jalankan matriks uji Admin/Superadmin pada akun QA terpisah.
5. Simpan screenshot hanya dengan NIK, nomor telepon, dan identitas lain yang sudah dimasking.
