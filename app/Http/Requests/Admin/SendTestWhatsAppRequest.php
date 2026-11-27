<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Services\Authentication\Delivery\FonnteOtpDelivery;
use Illuminate\Foundation\Http\FormRequest;

class SendTestWhatsAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('superadmin') === true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || ! FonnteOtpDelivery::isValidIndonesianPhone(FonnteOtpDelivery::normalizePhone($value))) {
                        $fail('Nomor WhatsApp harus berupa nomor seluler Indonesia yang valid.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Nomor WhatsApp tujuan wajib diisi.',
            'phone.string' => 'Nomor WhatsApp harus berupa teks.',
            'phone.max' => 'Nomor WhatsApp terlalu panjang.',
        ];
    }
}
