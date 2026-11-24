<?php

declare(strict_types=1);

namespace App\Services\Communication;

use App\Models\CommunicationSetting;
use Illuminate\Database\QueryException;

final class CommunicationMessageTemplateService
{
    public const OTP_ACTIVATION = 'otp_activation';

    public const OTP_PASSWORD_RESET = 'otp_password_reset';

    public const TEMPORARY_PASSWORD = 'temporary_password';

    public const TEMPORARY_PASSWORD_RESEND = 'temporary_password_resend';

    public const DOCUMENT_APPROVED_SCHEDULE = 'document_approved_schedule';

    private const COLUMNS = [
        self::OTP_ACTIVATION => 'otp_activation_template',
        self::OTP_PASSWORD_RESET => 'otp_password_reset_template',
        self::TEMPORARY_PASSWORD => 'temporary_password_template',
        self::TEMPORARY_PASSWORD_RESEND => 'temporary_password_resend_template',
        self::DOCUMENT_APPROVED_SCHEDULE => 'document_approved_schedule_template',
    ];

    private const DEFAULTS = [
        self::OTP_ACTIVATION => "{{institution_name}}\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nYth. {{applicant_name}},\n\nTerima kasih telah melakukan pendaftaran PPDB {{institution_name}}.\n\nBerikut kode OTP untuk aktivasi akun Anda:\n\n*{{otp}}*\n\nInformasi pendaftaran:\n\n- Nomor Pendaftaran: {{registration_number}}\n- NIK: {{masked_nik}}\n- Nama: {{applicant_name}}\n\nKode OTP berlaku selama *{{otp_validity_minutes}} menit* sejak pesan ini dikirim.\n\nPERHATIAN:\n\n- Jangan berikan kode OTP kepada siapa pun.\n- Pihak madrasah tidak pernah meminta kode OTP melalui telepon atau pesan pribadi.\n- Gunakan kode ini hanya pada halaman aktivasi resmi PPDB.\n\nTerima kasih.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
        self::OTP_PASSWORD_RESET => "{{institution_name}}\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nKami menerima permintaan pemulihan password akun PPDB Anda.\n\nBerikut kode OTP untuk verifikasi pemulihan password:\n\n*{{otp}}*\n\nKode OTP berlaku selama *{{otp_validity_minutes}} menit* sejak pesan ini dikirim.\n\nPERHATIAN:\n\n- Jangan berikan kode OTP kepada siapa pun.\n- Jangan meneruskan pesan ini kepada pihak lain.\n- Apabila Anda tidak merasa meminta pemulihan password, abaikan pesan ini.\n- Pihak madrasah tidak pernah meminta kode OTP Anda.\n\nTerima kasih.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
        self::TEMPORARY_PASSWORD => "{{institution_name}}\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nVerifikasi akun PPDB Anda berhasil.\n\nAkun pendaftaran Anda telah berhasil diaktifkan. Berikut informasi untuk masuk ke aplikasi PPDB:\n\n*Informasi Akun*\n\n- Nomor Pendaftaran: {{registration_number}}\n- Username: NIK yang digunakan saat pendaftaran\n- NIK terdaftar: {{masked_nik}}\n- Password sementara: *{{temporary_password}}*\n\nSilakan masuk menggunakan username dan password sementara tersebut.\n\n*PENTING:*\n\n1. Anda wajib mengganti password saat pertama kali berhasil login.\n2. Jangan membagikan password kepada siapa pun.\n3. Simpan password baru dengan aman.\n4. Pastikan Anda hanya mengakses portal PPDB resmi {{institution_name}}.\n\nTerima kasih.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
        self::TEMPORARY_PASSWORD_RESEND => "{{institution_name}}\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nBerikut informasi password sementara untuk akun PPDB Anda yang dikirim ulang sesuai permintaan.\n\n*Informasi Akun*\n\n- Nomor Pendaftaran: {{registration_number}}\n- Username: NIK yang digunakan saat pendaftaran\n- NIK terdaftar: {{masked_nik}}\n- Password sementara terbaru: *{{temporary_password}}*\n\nGunakan password sementara terbaru di atas untuk masuk ke aplikasi PPDB.\n\n*PENTING:*\n\n1. Gunakan password sementara terbaru, bukan password sementara yang sebelumnya Anda terima.\n2. Anda wajib mengganti password saat pertama kali berhasil login.\n3. Jangan membagikan password kepada siapa pun.\n4. Apabila Anda tidak merasa meminta pengiriman ulang, segera hubungi petugas resmi madrasah.\n\nTerima kasih.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
        self::DOCUMENT_APPROVED_SCHEDULE => "{{institution_name}}\n\nAssalamu'alaikum warahmatullahi wabarakatuh.\n\nYth. Orang Tua/Wali dari *{{applicant_name}}*,\n\nAlhamdulillah, berkas pendaftaran calon peserta didik baru telah diverifikasi dan *DISAHKAN* oleh Panitia PPDB {{institution_name}}.\n\nBerikut informasi pendaftaran dan jadwal pemetaan:\n\n- Nomor Urut Pendaftaran: *{{registration_number}}*\n- Nama Calon Siswa: {{applicant_name}}\n- Program Pilihan: {{program_name}}\n- Gelombang / Sesi: {{batch_title}}\n- Hari / Tanggal: *{{interview_date}}*\n- Waktu: *{{interview_time}}*\n\nSilakan masuk kembali ke portal resmi PPDB untuk mengunduh dan mencetak *Bukti Pendaftaran Resmi* ber-QR Code untuk dibawa saat pelaksanaan wawancara di madrasah.\n\nTerima kasih.\n\nWassalamu'alaikum warahmatullahi wabarakatuh.",
    ];

    public function template(string $key): string
    {
        $column = self::COLUMNS[$key] ?? throw new \InvalidArgumentException('Unknown communication template.');

        try {
            $stored = CommunicationSetting::query()->value($column);

            return is_string($stored) && trim($stored) !== '' ? $stored : self::DEFAULTS[$key];
        } catch (QueryException $exception) {
            if ($this->isMissingTemplateColumn($exception)) {
                return self::DEFAULTS[$key];
            }

            throw $exception;
        }
    }

    public function render(string $key, array $variables, ?string $defaultFallback = null): string
    {
        $rawTemplate = null;
        try {
            $rawTemplate = $this->template($key);
        } catch (\InvalidArgumentException $e) {
            if ($defaultFallback !== null) {
                $rawTemplate = $defaultFallback;
            } else {
                throw $e;
            }
        }

        if ($rawTemplate === null || trim($rawTemplate) === '') {
            $rawTemplate = $defaultFallback ?? '';
        }

        return strtr($rawTemplate, array_combine(
            array_map(static fn (string $name): string => '{{'.$name.'}}', array_keys($variables)),
            array_map(static fn (mixed $value): string => (string) $value, array_values($variables)),
        ));
    }

    public function all(): array
    {
        return array_map(fn (string $key): string => $this->template($key), array_keys(self::COLUMNS));
    }

    public static function allowedPlaceholders(string $key): array
    {
        return match ($key) {
            self::OTP_ACTIVATION => ['institution_name', 'applicant_name', 'otp', 'registration_number', 'masked_nik', 'otp_validity_minutes'],
            self::OTP_PASSWORD_RESET => ['institution_name', 'otp', 'otp_validity_minutes'],
            self::TEMPORARY_PASSWORD, self::TEMPORARY_PASSWORD_RESEND => ['institution_name', 'registration_number', 'masked_nik', 'temporary_password'],
            self::DOCUMENT_APPROVED_SCHEDULE => ['institution_name', 'applicant_name', 'registration_number', 'program_name', 'batch_title', 'interview_date', 'interview_time'],
            default => throw new \InvalidArgumentException('Unknown communication template.'),
        };
    }

    public static function defaults(): array
    {
        return self::DEFAULTS;
    }

    private function isMissingTemplateColumn(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'no such column')
            || str_contains(strtolower($exception->getMessage()), 'unknown column');
    }
}
