<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ApplicantRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Simplified in Phase 10.4 to only require NIK, full_name, phone, and consent.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'regex:/^\d{16}$/'],
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{8,12}$/'],
            'consent' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom Indonesian error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nik.required' => 'Nomor Induk Kependudukan (NIK) calon siswa wajib diisi.',
            'nik.regex' => 'NIK harus berupa 16 digit angka.',
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi sesuai Akta Kelahiran.',
            'full_name.min' => 'Nama lengkap minimal 2 karakter.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'phone.required' => 'Nomor WhatsApp / telepon orang tua/wali wajib diisi.',
            'phone.regex' => 'Format nomor WhatsApp / telepon tidak valid (contoh: 081234567890).',
            'consent.required' => 'Anda wajib menyetujui pernyataan kebenaran data dan ketentuan pendaftaran.',
            'consent.accepted' => 'Anda wajib menyetujui pernyataan kebenaran data dan ketentuan pendaftaran.',
        ];
    }
}
