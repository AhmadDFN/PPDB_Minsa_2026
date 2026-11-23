<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdminAuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view_activity_logs') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date_from' => $this->filled('date_from') ? trim((string) $this->input('date_from')) : null,
            'date_to' => $this->filled('date_to') ? trim((string) $this->input('date_to')) : null,
            'source' => $this->filled('source') ? trim((string) $this->input('source')) : null,
            'action' => $this->filled('action') ? trim((string) $this->input('action')) : null,
            'actor_id' => $this->filled('actor_id') ? $this->integer('actor_id') : null,
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'source' => ['nullable', 'in:admin_configuration,system_settings,document_verification,registration'],
            'action' => ['nullable', 'string', 'max:50', Rule::in([
                'created', 'updated', 'status_changed', 'required_status_changed', 'permissions_updated',
                'admin_report_export_requested', 'verification_PENDING', 'verification_APPROVED', 'verification_REJECTED', 'verification_REVISION_REQUIRED',
                'submitted', 'updated_after_submission', 'updated_draft', 'reopen_window_opened', 'reopen_window_closed', 'pdf_generated', 'pdf_generation_failed', 'setting_updated',
                'password_reset_requested', 'password_reset_completed', 'password_reset_notification_dispatch_failed', 'invitation_requested', 'invitation_notification_dispatch_failed',
            ])],
            'actor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('account_type', AccountType::ADMIN->value)],
            'search' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('date_from') && $this->filled('date_to') && $this->input('date_to') < $this->input('date_from')) {
                $validator->errors()->add('date_to', 'Tanggal akhir harus sama atau setelah tanggal mulai.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'date_from.date_format' => 'Tanggal mulai harus berformat YYYY-MM-DD.',
            'date_to.date_format' => 'Tanggal akhir harus berformat YYYY-MM-DD.',
            'source.in' => 'Sumber aktivitas tidak valid.',
            'action.in' => 'Jenis aktivitas tidak valid.',
            'actor_id.exists' => 'Aktor tidak ditemukan.',
            'search.max' => 'Pencarian maksimal 80 karakter.',
        ];
    }
}
