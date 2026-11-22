# Spesifikasi Dashboard Admin & Superadmin

## Admin PPDB

### Struktur halaman

1. Header: periode aktif, identitas petugas, logout.
2. KPI: total pendaftaran, submitted, draft, per program, dokumen belum diverifikasi, antrean reopen.
3. Tabel kerja: pendaftar terbaru/bermasalah dengan pencarian nama/nomor/NIK melalui HMAC (NIK tidak ditampilkan penuh).
4. Quick actions: buka daftar pendaftar, verifikasi dokumen, reopen, ekspor sesuai izin.
5. Activity panel: aktivitas terbaru yang sudah ter-redaksi.

### State wajib

`loading`, `empty period`, `no result`, `permission denied`, `server degradation`, dan `stale data`. Metrik harus menjelaskan periode/filter yang dipakai.

## Superadmin

Dashboard menambahkan health database/cache/storage, status Fonnte tanpa token, pending migrations sebagai indikator operasional (bukan tombol HTTP), perubahan setting terakhir, serta shortcut master data dan akun internal.

## Data contract minimum

`period_id`, `period_label`, `generated_at_wib`, KPI numerik, filter aktif, dan `last_updated_at`. Endpoint target harus mengembalikan data terstruktur melalui controller/service; view tidak boleh membuat query sensitif sendiri.

## Aksesibilitas & privasi

Keyboard navigable, label Bahasa Indonesia, kontras memadai, pagination server-side, masking NIK, no-store untuk dokumen, dan konfirmasi dua langkah untuk aksi destruktif/berdampak luas.
