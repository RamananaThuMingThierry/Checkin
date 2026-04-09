<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class ListAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'branch_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
        ];
    }
}
