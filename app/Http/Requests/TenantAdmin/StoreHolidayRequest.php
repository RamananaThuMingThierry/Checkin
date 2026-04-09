<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'string'],
            'branch_id' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'holiday_date' => ['required', 'date_format:Y-m-d'],
            'is_recurring' => ['nullable', 'boolean'],
        ];
    }
}
