<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => ['required', 'integer'],
            'offer_id' => ['required', 'integer'],
            'billing_cycle' => ['nullable', 'in:monthly,quarterly,semiannual,yearly'],
            'status' => ['nullable', 'in:trial,active,past_due,unpaid,cancelled,expired,suspended'],
            'trial_ends_at' => ['nullable', 'date'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'next_billing_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
