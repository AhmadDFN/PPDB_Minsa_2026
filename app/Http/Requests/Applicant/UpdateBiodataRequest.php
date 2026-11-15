<?php

declare(strict_types=1);

namespace App\Http\Requests\Applicant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isApplicant();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'gender' => ['required', 'string', 'in:L,P'],
            'birth_place' => ['required', 'string', 'min:2', 'max:100'],
            'birth_date' => ['required', 'date', 'before:today'],
            'nisn' => ['nullable', 'string', 'digits:10'],
            'child_order' => ['required', 'integer', 'min:1', 'max:20'],
            'siblings_count' => ['required', 'integer', 'min:0', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap calon siswa wajib diisi sesuai Akta Kelahiran.',
            'full_name.min' => 'Nama lengkap minimal 2 karakter.',
            'full_name.max' => 'Nama lengkap maksimal 255 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'birth_place.required' => 'Tempat lahir wajib diisi.',
            'birth_place.max' => 'Tempat lahir maksimal 100 karakter.',
            'birth_date.required' => 'Tanggal lahir wajib diisi.',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'nisn.digits' => 'NISN harus terdiri dari 10 digit angka.',
            'child_order.required' => 'Informasi anak ke-berapa wajib diisi.',
            'child_order.integer' => 'Urutan anak harus berupa angka.',
            'child_order.min' => 'Urutan anak minimal 1.',
            'child_order.max' => 'Urutan anak maksimal 20.',
            'siblings_count.required' => 'Jumlah saudara kandung wajib diisi (isi 0 jika anak tunggal).',
            'siblings_count.integer' => 'Jumlah saudara harus berupa angka.',
            'siblings_count.min' => 'Jumlah saudara minimal 0.',
            'siblings_count.max' => 'Jumlah saudara maksimal 20.',
        ];
    }
}
