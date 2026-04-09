<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class RejectAttendanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'in:invalid_scan,unresolved_employee,unauthorized_device,duplicate_scan'],
            'message' => ['nullable', 'string', 'max:255'],
        ];
    }
}
