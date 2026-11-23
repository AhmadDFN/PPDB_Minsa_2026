<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAccountPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token, private readonly string $purpose = 'invitation') {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $setup = $this->purpose === 'invitation';

        return (new MailMessage)
            ->subject($setup ? 'Aktivasi akun Admin PPDB' : 'Reset password Admin PPDB')
            ->greeting('Halo '.$notifiable->name.',')
            ->line($setup ? 'Akun Admin PPDB Anda telah dibuat. Silakan membuat password untuk mengaktifkannya.' : 'Permintaan reset password Admin PPDB diterima.')
            ->action($setup ? 'Buat Password' : 'Reset Password', route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->email]))
            ->line('Tautan ini berlaku sesuai kebijakan password broker dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak mengharapkan pesan ini, abaikan email ini.');
    }
}
