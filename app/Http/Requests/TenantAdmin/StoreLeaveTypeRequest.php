<?php

namespace App\Http\Requests\TenantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'alpha_dash'],
            'is_paid' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
