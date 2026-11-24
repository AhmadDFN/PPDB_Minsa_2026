# ATURAN WAJIB AI CODING AGENT (AI-RULES.md)
**Proyek:** PPDB Online MIN 1 Kota Madiun v2 (`PPDB_Minsa_2026`)  
**Target Deploy:** Produksi Langsung (`main` → Hostinger Auto-Deploy `https://ahmaddanyfn.biz.id/`)

Dokumen ini wajib dibaca dan dipatuhi oleh setiap AI Agent (Antigravity, Codex, Gemini, dll.) sebelum menulis atau mengubah satu baris kode pun.

---

## 1. Alur Kerja Wajib (Core Delivery Loop)

Setiap pengerjaan fitur atau perbaikan **WAJIB** mengikuti siklus berikut secara disiplin:

1. **Kerjakan secara atomik (1 sub-task per siklus):** Ambil satu sub-task dari `ppdbv2/todo-roadmap.md`. Jangan mencampur terlalu banyak perubahan besar dalam satu commit.
2. **Test sederhana sebelum push (Wajib Hindari Error):**
   - Jalankan test relevan (`php artisan test --filter=...` atau `php artisan test`).
   - Pastikan tidak ada syntax error PHP (`php -l`), route error (`php artisan route:list`), atau view compilation error (`php artisan view:cache && php artisan view:clear`).
   - Jika menyentuh migration, pastikan `php artisan migrate` berjalan mulus tanpa error.
3. **Update Dokumentasi Progres:**
   - Centang sub-task selesai di `ppdbv2/todo-roadmap.md`.
   - Catat ringkasan pekerjaan & file yang diubah di `ppdbv2/PROGRESS-LOG.md`.
4. **Push Langsung ke `main`:**
   - Lakukan commit dengan pesan yang jelas (contoh: `feat(interview): add interview master migrations and seeders`).
   - Jalankan `git push origin main` agar perubahan langsung naik ke server produksi.

---

## 2. Protokol Anti-Stuck (Jangan Berhenti di 1 Error!)

Proyek ini harus terus bergerak maju dengan stabil. Jika menemui error, bug lingkungan, atau kendala teknis yang tidak terselesaikan dalam **maksimal 2–3 kali percobaan perbaikan**:

1. **STOP mencoba-coba tanpa arah** pada error tersebut.
2. **Pastikan branch `main` tetap bersih dan tidak rusak:** Jangan pernah push kode yang menyebabkan HTTP 500 di produksi. Jika perubahan setengah jadi bikin error, pisahkan/kembalikan bagian bermasalah tersebut.
3. **Catat secara detail di `ppdbv2/KNOWN-ISSUES.md`:**
   - ID Issue, Tanggal, Fase/Task terkait, Pesan Error persis, File/Baris terkait, dan Hipotesis/Rencana Solusi.
4. **Lanjutkan segera** ke task berikutnya di `ppdbv2/todo-roadmap.md` yang tidak terblokir oleh error tersebut. Error yang dicatat akan dibenahi secara paralel/berkala.

---

## 3. Aturan Arsitektur & Batasan Teknis (WAJIB PATUH)

1. **Framework & Pola Desain:**
   - Gunakan **Laravel 13.x** dan **PHP 8.3+** dengan *strict types* bila relevan.
   - Patuhi **Service / Action Pattern** (*Thin Controllers*). Logika bisnis (penomoran, validasi urutan wawancara, kalkulasi skor, koreksi admin) wajib ditempatkan di kelas `App\Actions\...` atau `App\Services\...`, bukan menumpuk di Controller.
   - Validasi input wajib melalui **Form Request** dengan pesan error berbahasa Indonesia baku.
2. **Frontend Non-SPA (Strict):**
   - Gunakan **Blade Templates + Tailwind CSS v4 + Vanilla JavaScript**.
   - **DILARANG** memasang atau menggunakan React, Vue, Inertia, Svelte, Livewire, atau Bootstrap.
   - Untuk pemindai QR di portal pewawancara, gunakan library Vanilla JS ringan (`html5-qrcode`).
3. **Kompatibilitas Multi-Database (MySQL Produksi & SQLite Test):**
   - Semua file migration baru wajib *driver-agnostic*.
   - **DILARANG** menggunakan sintaks khusus MySQL (seperti `storedAs` di `ALTER TABLE` atau `ENUM` mentah yang gagal di SQLite) tanpa pengecekan `DB::getDriverName()`. Gunakan kolom `string` dengan validasi di aplikasi/Enum PHP.
4. **Keamanan & Isolasi Data:**
   - Pertahankan enkripsi NIK (AES-256 + HMAC), masking NIK di UI, dan penyimpanan dokumen di Private Storage.
   - Otorisasi wajib menggunakan **Spatie Laravel Permission** pada guard tunggal `web`.
   - **Isolasi Mutlak Nilai:** Pewawancara (`interviewer_stage_1/2/3`) **TIDAK BOLEH** bisa mengakses rute rekap nilai, melihat nilai setelah dikunci, atau mengakses tahap di luar rolenya.
   - **Ekspor CSV:** Wajib menggunakan sanitasi *anti-formula injection* (prefix `'` untuk sel yang diawali `=`, `+`, `-`, `@`).
   - **Audit Log:** Pengesahan nomor urut, penguncian nilai wawancara, dan koreksi nilai oleh Admin wajib tercatat di Audit Log.

---

## 4. Aturan Keselamatan Produksi (Direct-to-Main Safety)

Karena `git push origin main` langsung masuk ke lingkungan produksi (`https://ahmaddanyfn.biz.id/`):

1. **Migrasi Bersifat Additive:** Jangan pernah melakukan `dropColumn`, `dropTable`, atau `migrate:fresh` terhadap data produksi yang sudah ada. Selalu tambahkan kolom baru dengan `nullable()` agar data pendaftar v1 tetap aman.
2. **Zero Secret Leaks:** Dilarang keras meng-commit file `.env`, token Fonnte, `APP_KEY`, `NIK_HMAC_KEY`, atau `OTP_HMAC_KEY`.
3. **Jangan Rusak Fitur v1:** Pastikan alur registrasi OTP, login pendaftar, pengisian 6 modul, upload dokumen 5 MB, dan verifikasi QR publik tetap berfungsi normal setelah setiap perubahan.
4. **Bahasa UI:** Seluruh label, tombol, notifikasi, validasi, dan template WhatsApp wajib menggunakan **Bahasa Indonesia yang jelas dan ramah pengguna** (terutama bagi wali murid dan panitia madrasah).
---

## 5. Standar Desain Web & UI/UX (Distilasi Skill Populer: `frontend-design` + `web-design-guidelines` + `ui-ux-pro-max` + `impeccable`)

Aturan ini mengadaptasi praktik terbaik dari skill desain AI paling populer (*Anthropic `frontend-design`*, *Vercel `web-design-guidelines`*, *`ui-ux-pro-max`*, dan *`impeccable`*) khusus untuk stack **Blade + Tailwind CSS v4 + Vanilla JS** pada PPDB MIN 1 Kota Madiun:

### 5.1 Anti-"AI Slop" & Identitas Visual Konsisten
- **Gunakan Token Brand MINSATAMA:** Pertahankan konsistensi palet resmi (*Emerald Green* `#0B6E3F` sebagai primary, *Cyan/Blue* `#0F7FA3` untuk informasi/aksen sekunder, *Gold Accent* `#F2C94C` untuk highlight/badge penting, latar putih bersih `#FFFFFF` & permukaan kartu abu sangat lembut `#F8FAFC`).
- **DILARANG Desain Generik:** Hindari gradasi ungu/neon acak, bayangan berlebihan (*heavy drop shadows*), kartu bertumpuk tanpa hierarki, atau dekorasi yang mengganggu fokus kerja panitia dan pewawancara.
- **Hierarki Visual Tegas:** Satu halaman hanya boleh memiliki **1 tombol aksi utama (*Primary CTA*)** yang paling menonjol (misal: *"Sahkan Berkas & Terbitkan Nomor"* atau *"Simpan & Kunci Nilai"*). Aksi sekunder/batal wajib menggunakan gaya *outline/ghost*.

### 5.2 Ergonomi Mobile-First (Khusus Portal Pewawancara `/wawancara`)
- **Target Sentuh Nyaman (*Touch Target*):** Semua tombol, input angka `0–100`, dan tombol *Scan QR* wajib memiliki tinggi minimal **`44px` (`min-h-11`)** dengan jarak antar elemen minimal `8px–12px` agar pewawancara tidak salah tekan di HP/tablet.
- **Input Ramah Keyboard HP:** Gunakan `inputmode="numeric"` dan `pattern="[0-9]*"` pada input nomor urut (`270001`) serta input skor (`0–100`) agar papan ketik angka HP langsung muncul otomatis.
- **Fokus 1 Tugas per Layar (*Distill / Quieter*):** Halaman penilaian hanya menampilkan informasi esensial (Pas Foto 3x4, Nama, Nomor Urut, Program, dan form poin penilaian) tanpa sidebar admin yang memenuhi layar.

### 5.3 Kejelasan Form, Tabel & Status Interaksi (*Web Interface Guidelines*)
- **State Lengkap:** Setiap halaman/komponen wajib menangani kondisi **Loading** (disable tombol + spinner saat submit/scan untuk cegah double-submit), **Empty State** (pesan ramah + arahan saat antrean kosong), **Error State** (pesan validasi spesifik di bawah input terkait, bukan hanya alert umum), dan **Success State**.
- **Angka & Tabel Rapi:** Gunakan kelas `tabular-nums` pada seluruh Nomor Urut (`270001`), skor (`0–100`), jumlah, dan rata-rata (`87.50`) agar angka sejajar rapi secara vertikal di tabel rekap.
- **Keterbacaan & Aksesibilitas (WCAG AA):** Pastikan kontras teks minimal `4.5:1`, label form selalu terikat dengan `for`/`id`, dan modal konfirmasi (*Simpan & Kunci Nilai* / *Koreksi Admin*) memiliki tombol tutup jelas serta ringkasan data yang akan dieksekusi.
- **Mikro-Interaksi Terukur:** Transisi hover/modal cukup ringan (`duration-150 ease-out`) dan wajib menghormati `prefers-reduced-motion`. Hindari animasi berlebihan yang memperlambat HP kelas pemula.

### 5.4 Referensi Skill Eksternal (Opsional untuk Agen Pendukung)
Jika menggunakan agen coding yang mendukung instalasi skill eksternal (Antigravity / Codex / Claude Code), disarankan mengaktifkan kombinasi berikut sebagai referensi tambahan:
- `anthropics/skills` → **`frontend-design`** (kualitas visual & anti-slop)
- `vercel-labs/agent-skills` → **`web-design-guidelines`** (standar form, tabel, tipografi & aksesibilitas)
- `nextlevelbuilder/ui-ux-pro-max-skill` → **`ui-ux-pro-max`** (pola dashboard, admin panel & mobile touch UX)
- `pbakaus/impeccable` → **`polish` & `critique`** (audit akhir kerapatan jarak & konsistensi UI)