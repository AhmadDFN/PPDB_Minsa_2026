# Communication Center — Provider Foundation

**Status:** Phase 3 implemented locally, pending review

Communication Center berada dalam satu Admin Workspace dan hanya dapat dikelola Superadmin melalui permission `manage_communication_settings`.

## Implemented

- Provider-neutral contract, registry, resolver, channel, credential, connection, and audit services.
- Fonnte Device Token adapter menggunakan endpoint tetap `/device` dan `/send` tanpa prefix `Bearer`.
- Credential disimpan terpisah menggunakan Laravel encryption; plaintext tidak dikembalikan ke UI atau audit metadata.
- Device profile validation mengambil sender metadata dari respons provider.
- Satu active channel global melalui transaksi database dan manual provider switching.
- Jalur legacy `FONNTE_TOKEN` dipertahankan sampai channel database diaktifkan.
- Setelah database mode aktif, kegagalan tidak fallback diam-diam ke `.env` dan tidak melakukan automatic failover.
- UI Provider & Gateway, validasi koneksi, aktivasi, deaktivasi, dan pembaruan credential hanya untuk Superadmin.

## Hardening Phase 3B

- `communication_settings.active_channel_id` adalah satu-satunya sumber kebenaran active channel; UI dan resolver membaca pointer yang sama.
- Settings singleton memakai identity deterministik `id = 1`, insert-ignore, dan row lock di dalam transaction aktivasi/deaktivasi.
- Pembaruan credential memvalidasi token di luar transaction, lalu memakai row lock dan optimistic `credential_version` checking.
- Fonnte menolak target atau pesan kosong/malformed sebelum HTTP request.
- Validasi concurrency aktual terhadap MySQL/MariaDB masih perlu dilakukan pada database disposable; SQLite hanya memvalidasi alur dan optimistic conflict.
- Rollback penuh migration historis masih memiliki issue lama pada migration users dan dilacak sebagai maintenance terpisah.

## Belum diimplementasikan

- Provider adapter tambahan.
- Template editor dan versioning.
- Notification rules.
- Message log viewer dan retention policy.
- Arbitrary test-send, broadcast, atau manajemen perangkat Account API.
