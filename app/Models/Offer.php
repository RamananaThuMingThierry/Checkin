<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    use HasFactory;

    protected $table = 'offers';

    protected $fillable = [
        'name',
        'code',
        'description',
        'monthly_price',
        'yearly_price',
        'currency',
        'max_users',
        'max_branches',
        'max_employees',
        'max_devices',
        'is_public',
        'is_active',
        'is_custom',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'is_custom' => 'boolean',
        ];
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'offer_modules')
            ->withPivot('is_included')
            ->withTimestamps();
    }
}
