<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string'],
            'settings' => ['required', 'array'],
            'settings.attendance.grace_minutes' => ['nullable', 'integer', 'min:0', 'max:180'],
            'settings.attendance.default_timezone' => ['nullable', 'string', 'max:64'],
            'settings.reporting.include_weekends' => ['nullable', 'boolean'],
            'settings.reporting.default_period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }
}
