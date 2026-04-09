<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'offer_id',
        'subscription_number',
        'billing_cycle',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'next_billing_date',
        'cancelled_at',
        'base_amount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'external_provider',
        'external_subscription_id',
        'notes',
    ];

    protected $appends = ['encrypted_id'];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'next_billing_date' => 'date',
            'cancelled_at' => 'date',
            'base_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
