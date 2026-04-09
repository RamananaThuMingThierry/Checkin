<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkShift extends Model
{
    use HasFactory;

    protected $table = 'work_shifts';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'late_tolerance_minutes',
        'is_night_shift',
    ];

    protected function casts(): array
    {
        return [
            'is_night_shift' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
