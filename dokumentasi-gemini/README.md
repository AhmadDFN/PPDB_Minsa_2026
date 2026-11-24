# DOKUMENTASI SISTEM PPDB MIN 1 KOTA MADIUN (MINSATAMA)
### Direktori Resmi Dokumentasi Analisis, Review, Alur Kerja, dan Rencana Pengembangan

Direktori ini memuat dokumentasi arsitektur, hasil tinjauan teknis (*system review*), peta alur kerja aplikasi berbasis peran (*role-based workflows*), serta rekomendasi dan peta rencana pengembangan berkelanjutan untuk Sistem Informasi Penerimaan Peserta Didik Baru (PPDB) Online MIN 1 Kota Madiun.

---

## 1. Peta Navigasi Dokumen

Dokumentasi dibagi ke dalam beberapa berkas terstruktur untuk memudahkan pemahaman seluruh pemangku kepentingan (*stakeholders*), arsitek perangkat lunak, tim pengembang, dan panitia madrasah:

| Dokumen | Deskripsi & Cakupan |
|---|---|
| **[REVIEW-SISTEM.md](./REVIEW-SISTEM.md)** | Tinjauan menyeluruh terhadap kualitas arsitektur, keamanan data, implementasi backend/frontend, status database, pengujian otomatis, dan hasil live test di server produksi. |
| **[ALUR-KERJA-PER-ROLE.md](./ALUR-KERJA-PER-ROLE.md)** | Penjelasan detail proses bisnis ujung-ke-ujung (*end-to-end*) untuk setiap peran: **Calon Siswa / Wali Murid**, **Panitia PPDB / Verifikator**, **Super Administrator**, dan integrasi fisik di **Loket PTSP Madrasah**. |
| **[SARAN-DAN-REKOMENDASI.md](./SARAN-DAN-REKOMENDASI.md)** | Rekomendasi teknis, peningkatan UX/UI, mitigasi risiko infrastruktur Hostinger, optimasi mesin PDF, dan tata kelola operasional live. |
| **[RENCANA-PENGEMBANGAN.md](./RENCANA-PENGEMBANGAN.md)** | Rencana aksi persiapan go-live (*deployment readiness*), prosedur backup-restore, rencana pemeliharaan berkala, dan peta jalan fitur lanjutan. |

---

## 2. Ringkasan Eksekutif Sistem

- **Institusi**: Madrasah Ibtidaiyah Negeri (MIN) 1 Kota Madiun (Kementerian Agama RI).
- **Domain Produksi**: `https://ahmaddanyfn.biz.id/`
- **Zona Waktu & Bahasa**: `Asia/Jakarta` (WIB) / Bahasa Indonesia baku.
- **Jam Layanan Loket PTSP**: **07.00 – 15.00 WIB** (hari kerja).
- **Landasan Arsitektur**:
  - Framework: **Laravel 13.x** & **PHP 8.3+**.
  - Pola Arsitektur: Monolith MVC dengan **Service & Action Pattern** (*Thin Controllers*).
  - Tampilan (UI): **Blade Templates** + **Tailwind CSS v4** + **Vanilla JavaScript** (*Non-SPA, dilarang React/Vue/Inertia/Bootstrap*).
  - Basis Data: **MySQL / MariaDB (InnoDB, UTF8MB4)** pada produksi; SQLite untuk suite pengujian otomatis.
  - Otorisasi & Peran: **Spatie Laravel Permission** (guard tunggal `web`).
  - Komunikasi & OTP: WhatsApp Gateway terintegrasi (**Fonnte API**) dengan failover log abstraction.
  - Keamanan Data: Enkripsi NIK **AES-256-CBC**, Lookup Hash **HMAC-SHA256**, Proteksi IDOR, dan Dokumen Berkas tersimpan di **Private Storage**.

---

## 3. Batasan Lingkup Sistem (*Scope Boundaries*)

Untuk menjaga akuntabilitas dan efisiensi, batasan sistem PPDB Minsatama didefinisikan secara tegas:
1. **Dalam Lingkup (In-Scope)**:
   - Pendaftaran akun calon siswa mandiri berbasis NIK 16 digit.
   - Verifikasi kepemilikan nomor WhatsApp via OTP 6 digit.
   - Password acak aman otomatis dikirim via WhatsApp dengan kewajiban ganti password saat login pertama (`must_change_password`).
   - Pengisian formulir 6 tahap: Biodata, Alamat, Orang Tua/Wali, Riwayat TK/PAUD, Pilihan Program (Reguler / Unggulan).
   - Unggah dokumen persyaratan legal (Kartu Keluarga, Akta Kelahiran, Pas Foto) maksimal 5 MB di penyimpanan privat.
   - Pembuatan Lembar Draf Pendaftaran ber-watermark `DRAFT`.
   - Persetujuan legalitas pendaftaran (*Consent v1.0*) dan pengiriman final (*Final Submit*) yang atomik dan idempoten.
   - Penerbitan Lembar Bukti Pendaftaran PDF resmi ber-QR Code verifikasi publik tanpa membocorkan data pribadi (PII).
   - Panel Verifikasi Berkas Dokumen, Audit Log, dan Ekspor Rekapitulasi bagi Panitia PPDB.
2. **Di Luar Lingkup (Out-of-Scope)**:
   - Sistem **TIDAK** membatasi kuota pendaftaran secara otomatis (*sistem tidak menolak pendaftar atas dasar kuota*).
   - Sistem **TIDAK** melakukan seleksi akademik, perankingan usia/skor, pengumuman kelulusan, maupun alur daftar ulang (*re-registration*).
   - Alur sistem daring **BERAKHIR** saat orang tua mencetak Bukti Pendaftaran PDF Final dan menyerahkan berkas fisik ke loket PTSP madrasah.
