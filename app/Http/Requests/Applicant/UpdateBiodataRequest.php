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

    protected function prepareForValidation(): void
    {
        $applicant = $this->user()?->applicant;
        if (! $applicant) {
            return;
        }

        $stored = [
            'nisn' => $applicant->nisn,
            'student_phone' => $applicant->student_phone,
            'family_card_number' => $applicant->family_card_number,
            'religion' => $applicant->religion,
            'hobby' => $applicant->hobby,
            'hobby_other' => $applicant->hobby_other,
            'aspiration' => $applicant->aspiration,
            'aspiration_other' => $applicant->aspiration_other,
            'residence_status' => $applicant->residence_status,
            'travel_time' => $applicant->travel_time,
            'transportation' => $applicant->transportation,
            'social_assistance_number' => $applicant->social_assistance_number,
            'nisn_unavailable' => (bool) $applicant->nisn_unavailable_reason,
        ];

        $this->merge(array_filter(
            array_diff_key($stored, $this->all()),
            static fn (mixed $value): bool => $value !== null,
        ));
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
            'student_phone' => ['required', 'string', 'regex:/^[0-9+()\-\s]{8,25}$/'],
            'nisn' => ['required_unless:nisn_unavailable,1', 'nullable', 'string', 'digits:10'],
            'nisn_unavailable' => ['nullable', 'boolean'],
            'family_card_number' => ['required', 'string', 'regex:/^\d{16}$/'],
            'religion' => ['required', 'string', 'in:Islam,Kristen Protestan,Katolik,Hindu,Buddha,Konghucu'],
            'child_order' => ['required', 'integer', 'between:1,9'],
            'siblings_count' => ['required', 'integer', 'between:0,20'],
            'hobby' => ['required', 'string', 'in:Olahraga,Kesenian,Membaca,Menulis,Jalan-jalan,Lainnya'],
            'hobby_other' => ['required_if:hobby,Lainnya', 'nullable', 'string', 'max:100'],
            'aspiration' => ['required', 'string', 'in:PNS,TNI/POLRI,Guru/Dosen,Dokter,Politikus,Wiraswasta,Seniman/Artis,Ilmuwan,Agamawan,Lainnya'],
            'aspiration_other' => ['required_if:aspiration,Lainnya', 'nullable', 'string', 'max:100'],
            'residence_status' => ['required', 'string', 'in:Rumah orang tua,Rumah Saudara/ kerabat,Rumah Dinas,Rumah Sewa / kontrak,Lainnya'],
            'travel_time' => ['required', 'string', 'in:1 - 10 menit,10 - 19 menit,20 - 29 menit,30 - 39 menit,> 1 jam'],
            'transportation' => ['required', 'string', 'in:Jalan Kaki,Sepeda,Antar Jemput Sekolah'],
            'social_assistance_number' => ['required', 'string', 'max:100'],
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
            'student_phone.required' => 'Nomor handphone calon siswa wajib diisi.',
            'student_phone.regex' => 'Nomor handphone calon siswa harus berisi 8–25 karakter angka atau tanda telepon yang valid.',
            'nisn.digits' => 'NISN harus terdiri dari 10 digit angka.',
            'family_card_number.required' => 'Nomor Kartu Keluarga wajib diisi.',
            'family_card_number.regex' => 'Nomor Kartu Keluarga harus terdiri dari 16 digit angka.',
            'religion.required' => 'Agama wajib dipilih.',
            'religion.in' => 'Pilihan agama tidak valid.',
            'siblings_count.between' => 'Jumlah saudara harus diisi dari 0 sampai 20.',
            'child_order.between' => 'Urutan anak harus dipilih dari 1 sampai 9.',
            'hobby.required' => 'Hobi wajib dipilih.',
            'hobby_other.required_if' => 'Tuliskan hobi lain yang dipilih.',
            'aspiration.required' => 'Cita-cita wajib dipilih.',
            'aspiration_other.required_if' => 'Tuliskan cita-cita lain yang dipilih.',
            'residence_status.required' => 'Status tempat tinggal wajib dipilih.',
            'travel_time.required' => 'Waktu tempuh wajib dipilih.',
            'transportation.required' => 'Transportasi ke sekolah wajib dipilih.',
            'social_assistance_number.required' => 'Isi nomor KIP/PKH/KKS atau tulis Tidak Ada.',
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
