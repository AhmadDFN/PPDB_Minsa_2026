# Spesifikasi Dashboard Admin & Superadmin

> **Arsitektur final:** satu Admin Workspace pada `/admin/dashboard` digunakan oleh `admin_ppdb` dan `superadmin`. Tidak ada Control Center atau workspace switcher terpisah. Menu dan aksi dibatasi oleh permission server-side; Superadmin memperoleh full access melalui role `superadmin` yang sudah ada.

## Admin PPDB

> **Phase 10.6 implementation note:** quick actions expose only working destinations (`Data Pendaftar`, `Buka Ulang Pendaftaran`, and portal publik). Verifikasi dokumen and export remain visibly unavailable until their routes/workflows exist.

### Struktur halaman

1. Header: periode aktif, identitas petugas, logout.
2. KPI: total pendaftaran, submitted, draft, per program, dokumen belum diverifikasi, antrean reopen.
3. Tabel kerja: pendaftar terbaru/bermasalah dengan pencarian nama/nomor/NIK melalui HMAC (NIK tidak ditampilkan penuh).
4. Quick actions: buka daftar pendaftar, verifikasi dokumen, reopen, ekspor sesuai izin.
5. Activity panel: aktivitas terbaru yang sudah ter-redaksi.

### State wajib

`loading`, `empty period`, `no result`, `permission denied`, `server degradation`, dan `stale data`. Metrik harus menjelaskan periode/filter yang dipakai.

## Superadmin

Superadmin menggunakan dashboard Admin yang sama dan dapat melihat atau menjalankan fitur yang dilindungi permission administratif. Health database/cache/storage, status Fonnte, pending migrations, master data, akun internal, Communication Center, dan audit center tetap menjadi roadmap terpisah sampai backend nyata tersedia.

## Data contract minimum

`period_id`, `period_label`, `generated_at_wib`, KPI numerik, filter aktif, dan `last_updated_at`. Endpoint target harus mengembalikan data terstruktur melalui controller/service; view tidak boleh membuat query sensitif sendiri.

## Aksesibilitas & privasi

Keyboard navigable, label Bahasa Indonesia, kontras memadai, pagination server-side, masking NIK, no-store untuk dokumen, dan konfirmasi dua langkah untuk aksi destruktif/berdampak luas.
