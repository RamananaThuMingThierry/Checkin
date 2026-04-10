<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ListLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,approved,rejected,cancelled'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'employee_id' => ['nullable', 'string'],
        ];
    }
}
