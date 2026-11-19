# DATABASE DESIGN â€” PPDB MINSATAMA
## Spesifikasi Fisik Basis Data & Kamus Data Relasional

---

## 1. PHYSICAL ENTITY RELATIONSHIP DIAGRAM (ERD)

```mermaid
erDiagram
    users {
        bigint id PK
        string nik_lookup_hash "UK, VARCHAR(64)"
        text nik_encrypted "TEXT AES-256"
        string name "VARCHAR(255)"
        string email "VARCHAR(255), NULL"
        string phone "VARCHAR(25)", NULL"
        string account_type "VARCHAR(20), default 'applicant'"
        string account_status "VARCHAR(30), default 'pending_activation'"
        timestamp email_verified_at "NULL"
        timestamp phone_verified_at "NULL"
        string password "VARCHAR(255)"
        string remember_token "VARCHAR(100), NULL"
        timestamps created_at_updated_at
    }

    user_otp_tokens {
        bigint id PK
        bigint user_id FK
        string phone "VARCHAR(25)"
        string purpose "VARCHAR(30)"
        string otp_hash "VARCHAR(255)"
        smallint attempts "default 0"
        smallint max_attempts "default 5"
        datetime expires_at
        datetime verified_at "NULL"
        timestamps created_at_updated_at
    }

    applicants {
        bigint id PK
        bigint user_id FK "UK"
        string full_name "VARCHAR(255)"
        string gender "VARCHAR(5)"
        string birth_place "VARCHAR(255)"
        date birth_date
        string nisn "VARCHAR(10), NULL"
        smallint child_order "NULL"
        smallint siblings_count "NULL"
        timestamps created_at_updated_at
    }

    applicant_addresses {
        bigint id PK
        bigint applicant_id FK "UK"
        text address_street
        string rt "VARCHAR(5), NULL"
        string rw "VARCHAR(5), NULL"
        string village "VARCHAR(100)"
        string district "VARCHAR(100)"
        string city "VARCHAR(100)"
        string postal_code "VARCHAR(10), NULL"
        timestamps created_at_updated_at
    }

    applicant_guardians {
        bigint id PK
        bigint applicant_id FK
        string relationship_type "VARCHAR(20) [ayah, ibu, wali]"
        string name "VARCHAR(255)"
        string nik "VARCHAR(255), NULL"
        boolean is_alive "default true"
        string education "VARCHAR(50), NULL"
        string occupation "VARCHAR(100), NULL"
        string income "VARCHAR(50), NULL"
        string phone "VARCHAR(25), NULL"
        timestamps created_at_updated_at
    }

    applicant_educations {
        bigint id PK
        bigint applicant_id FK "UK"
        string level "VARCHAR(20) [tk, ra, paud, none]"
        string school_name "VARCHAR(255)"
        string npsn "VARCHAR(20), NULL"
        string school_address "TEXT, NULL"
        timestamps created_at_updated_at
    }

    admission_periods {
        bigint id PK
        string name "VARCHAR(100)"
        string academic_year "VARCHAR(20)"
        date start_date
        date end_date
        date age_reference_date "Acuan Hitung Umur"
        string status "VARCHAR(20) default 'draft'"
        timestamps created_at_updated_at
    }

    programs {
        bigint id PK
        string code "VARCHAR(50) UK [UNGGULAN, REGULER]"
        string name "VARCHAR(100)"
        text description "NULL"
        boolean is_active "default true"
        timestamps created_at_updated_at
    }

    admission_period_programs {
        bigint id PK
        bigint admission_period_id FK
        bigint program_id FK
        smallint capacity "default 0"
        boolean is_active "default true"
        timestamps created_at_updated_at
    }

    registrations {
        bigint id PK
        string registration_number "VARCHAR(50), UK, NULL"
        bigint applicant_id FK
        bigint admission_period_id FK
        bigint admission_period_program_id FK
        string status "VARCHAR(20) default 'draft'"
        datetime submitted_at "NULL"
        timestamps created_at_updated_at
    }

    registration_sequences {
        bigint id PK
        bigint admission_period_program_id FK "UK, legacy counter"
        timestamps created_at_updated_at
    }

    registration_number_sequences {
        bigint id PK
        string academic_year "VARCHAR(15)"
        bigint program_id FK "UK with academic_year"
        bigint last_sequence "global counter per academic year-program"
        timestamps created_at_updated_at
    }

    document_requirements {
        bigint id PK
        string code "VARCHAR(50) UK"
        string name "VARCHAR(100)"
        text description "NULL"
        boolean is_required "default true"
        string allowed_formats "VARCHAR(100)"
        smallint max_size_kb "default 5120; max 5120"
        timestamps created_at_updated_at
    }

    registration_documents {
        bigint id PK
        bigint registration_id FK
        bigint document_requirement_id FK
        string file_path "VARCHAR(255)"
        string original_filename "VARCHAR(255)"
        string mime_type "VARCHAR(100)"
        bigint file_size_bytes
        string verification_status "VARCHAR(20) default 'pending'"
        text verification_note "NULL"
        timestamps created_at_updated_at
    }

    registration_document_revisions {
        bigint id PK
        bigint registration_document_id FK
        string previous_file_path "VARCHAR(255)"
        string reason "TEXT, NULL"
        timestamps created_at_updated_at
    }

    registration_pdf_versions {
        bigint id PK
        bigint registration_id FK
        string document_type "VARCHAR(50) [draft, final]"
        smallint version_number "default 1"
        int data_revision "default 1"
        string file_path "VARCHAR(255)"
        string file_hash "VARCHAR(64)"
        string storage_disk "VARCHAR(255) default 'ppdb_private'"
        string mime_type "VARCHAR(127) NULL"
        bigint file_size_bytes "BIGINT UNSIGNED NULL"
        string verification_token_hash "VARCHAR(64) UK NULL"
        bigint generated_by FK "NULL"
        datetime generated_at
        timestamps created_at_updated_at
    }

    registration_consents {
        bigint id PK
        bigint registration_id FK "UK"
        boolean agreed_to_terms "default true"
        string consent_ip "VARCHAR(45)"
        datetime agreed_at
        timestamps created_at_updated_at
    }

    registration_logs {
        bigint id PK
        bigint registration_id FK
        bigint performed_by FK "NULL"
        string action "VARCHAR(100)"
        text details_redacted "JSON, NULL"
        timestamps created_at_updated_at
    }

    registration_reopen_windows {
        bigint id PK
        bigint registration_id FK
        bigint opened_by FK
        bigint closed_by FK "NULL"
        text reason
        datetime opened_at
        datetime expires_at
        datetime closed_at "NULL"
        text close_reason "NULL"
        timestamps created_at_updated_at
    }

    users ||--o{ user_otp_tokens : "has"
    users ||--|| applicants : "has"
    applicants ||--|| applicant_addresses : "has"
    applicants ||--o{ applicant_guardians : "has"
    applicants ||--|| applicant_educations : "has"
    applicants ||--o{ registrations : "registers"
    admission_periods ||--o{ registrations : "manages"
    admission_periods ||--o{ admission_period_programs : "offers"
    programs ||--o{ admission_period_programs : "provides"
    registrations }|--|| admission_period_programs : "selects"
    admission_period_programs ||--o| registration_sequences : "legacy counter"
    programs ||--o{ registration_number_sequences : "owns counters"
    registrations ||--o{ registration_documents : "attaches"
    document_requirements ||--o{ registration_documents : "defines"
    registration_documents ||--o{ registration_document_revisions : "records"
    registrations ||--o{ registration_pdf_versions : "issues"
    registrations ||--|| registration_consents : "signs"
    registrations ||--o{ registration_logs : "audits"
    registrations ||--o{ registration_reopen_windows : "reopens editing"
```

---

## 2. SPESIFIKASI TABEL, INDEX, & INTEGRITAS KUNCI ASING

### 2.1 Tabel `users`
- **Fungsi**: Akun sentral autentikasi sistem (calon siswa dan panitia admin).
- **Indeks & Constraint**:
  - `PRIMARY KEY (id)`
  - `UNIQUE KEY users_nik_lookup_hash_unique (nik_lookup_hash)`: Lookup deterministik $O(1)$ untuk NIK terenkripsi.
  - `INDEX users_account_type_account_status_index (account_type, account_status)`: Optimasi query pemilahan akun aktif.

### 2.2 Tabel `user_otp_tokens`
- **Fungsi**: Penyimpanan token OTP kriptografis HMAC-SHA256.
- **Indeks & Constraint**:
  - `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
  - `INDEX user_otp_lookup_idx (user_id, purpose, verified_at, expires_at)`

### 2.3 Tabel `applicants`
- **Fungsi**: Profil identitas inti calon siswa.
- **Indeks & Constraint**:
  - `FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE`
  - `UNIQUE KEY applicants_user_id_unique (user_id)`: 1 pengguna calon siswa memiliki tepat 1 entitas profil siswa.
  - `INDEX applicants_birth_date_index (birth_date)`
  - `INDEX applicants_gender_index (gender)`

### 2.4 Tabel `applicant_addresses`
- **Fungsi**: Alamat domisili tempat tinggal calon siswa.
- **Indeks & Constraint**:
  - `FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE`
  - `UNIQUE KEY applicant_addresses_applicant_id_unique (applicant_id)`: 1 calon siswa memiliki tepat 1 alamat.

### 2.5 Tabel `applicant_guardians`
- **Fungsi**: Data ayah kandung, ibu kandung, dan wali calon siswa.
- **Indeks & Constraint**:
  - `FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE`
  - `UNIQUE KEY app_guardians_rel_unique (applicant_id, relationship_type)`: Menjamin tidak ada duplikasi data untuk hubungan yang sama (hanya ada 1 Ayah, 1 Ibu, 1 Wali per pendaftar).

### 2.6 Tabel `applicant_educations`
- **Fungsi**: Riwayat pendidikan pra-sekolah (TK/RA/PAUD).
- **Indeks & Constraint**:
  - `FOREIGN KEY (applicant_id) REFERENCES applicants(id) ON DELETE CASCADE`
  - `UNIQUE KEY applicant_educations_applicant_id_unique (applicant_id)`

### 2.7 Tabel `admission_periods`
- **Fungsi**: Master periode PPDB (misal: Tahun Ajaran 2026/2027).
- **Kolom Kritis**: `age_reference_date` (Tanggal acuan patokan hitung umur calon siswa).

### 2.8 Tabel `programs`
- **Fungsi**: Master jalur pendidikan madrasah (UNGGULAN dan REGULER).
- **Indeks & Constraint**: `UNIQUE KEY programs_code_unique (code)`

### 2.9 Tabel `admission_period_programs`
- **Fungsi**: Tabel pivot penawaran program pada suatu periode PPDB.
- **Indeks & Constraint**:
  - `UNIQUE KEY period_program_unique (admission_period_id, program_id)`

### 2.10 Tabel `registrations`
- **Fungsi**: Rekam jejak pendaftaran siswa pada suatu periode.
- **Indeks & Constraint**:
  - `UNIQUE KEY registrations_registration_number_unique (registration_number)`: Nomor registrasi unik (bernilai `NULL` saat masih DRAFT, bernilai resmi saat SUBMITTED).
  - `UNIQUE KEY app_period_unique (applicant_id, admission_period_id)`: Satu calon siswa hanya boleh mendaftar 1 kali per periode pendaftaran.

### 2.11 Tabel `document_requirements` & `registration_documents`
- **Fungsi**: Manajemen berkas persyaratan dan dokumen digital pendaftar.
- **Indeks & Constraint**:
  - `UNIQUE KEY reg_doc_unique (registration_id, document_requirement_id)`: Menjamin satu pendaftaran hanya memiliki satu rekaman berkas aktif untuk tiap jenis persyaratan.
  - `FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE`

### 2.12 Tabel `registration_pdf_versions`
- **Fungsi**: Riwayat versi PDF lembar bukti pendaftaran.
- **Indeks & Constraint**:
  - `UNIQUE KEY reg_pdf_ver_unique (registration_id, document_type, version_number)`

---

## 3. PERLAKUAN DATA SENSITIF (SENSITIVE DATA HANDLING)

| Kolom Sensitif | Tabel | Tipe Proteksi Basis Data |
|---|---|---|
| `nik_encrypted` | `users` | Terenkripsi AES-256-CBC via Laravel Crypt. Tidak dapat dibaca dari SQL mentah. |
| `nik_lookup_hash` | `users` | Hash satu arah HMAC-SHA256 ber-secret key. Mencegah pembalikan teks asli. |
| `password` | `users` | Hash Argon2id / Bcrypt via Laravel Hash. |
| `otp_hash` | `user_otp_tokens` | Hash HMAC-SHA256 mengikat user, purpose, dan nomor telepon. Anti-rainbow table. |
| `details_redacted` | `registration_logs` | Payload JSON yang disanitasi secara ketat; password dan NIK selalu diredaksi sebelum disimpan. |
