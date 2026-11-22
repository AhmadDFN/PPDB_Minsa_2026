# Dokumentasi PPDB Minsatama

Sumber kebenaran status proyek adalah [`PROJECT_STATUS.md`](PROJECT_STATUS.md). Dokumen fase dan laporan historis tidak boleh dipakai untuk menyimpulkan fitur saat ini tanpa rekonsiliasi terhadap kode.

## Peta dokumentasi

- `blueprints/` — cetak biru, spesifikasi dashboard, matriks modul, workflow, dan roadmap implementasi Admin/Superadmin.
- `manual/` — panduan operasional pengguna dan panitia.
- `audits/` — audit, isu yang diketahui, dan rekonsiliasi status.
- `phases/` — laporan historis per fase; statusnya dipertahankan sebagai snapshot.
- `deployment/` — panduan lingkungan dan rilis.
- `uat/` — rencana serta hasil uji penerimaan.

## Aturan governance

1. Setiap klaim `IMPLEMENTED` harus memiliki rute/controller/view atau test yang dapat ditunjuk.
2. Permission yang hanya ada di seeder dicatat sebagai *planned capability*, bukan fitur selesai.
3. Dokumen historis diberi tanggal/snapshot dan tidak diedit untuk mengubah fakta masa lalu.
4. Status saat ini, gap, keputusan bisnis, dan bukti verifikasi dicatat di `PROJECT_STATUS.md` serta audit rekonsiliasi terbaru.
5. Jangan menaruh credential, token, HMAC key, NIK, OTP, atau URL/path lokal absolut di dokumentasi.

## Titik mulai Admin/Superadmin

Baca [`blueprints/README.md`](blueprints/README.md), lalu [`ADMIN-SUPERADMIN-PLATFORM-BLUEPRINT.md`](blueprints/ADMIN-SUPERADMIN-PLATFORM-BLUEPRINT.md). Blueprint ini adalah rencana yang belum mengubah kode runtime.
