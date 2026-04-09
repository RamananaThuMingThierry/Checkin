<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'color',
        'entity_type',
        'entity_id',
        'method',
        'url',
        'route',
        'status_code',
        'message',
        'metadata',
        'tenant_id',
        'branch_id',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    protected $appends = ['encrypted_id'];

    public function getEncryptedIdAttribute(): string
    {
        return Crypt::encryptString($this->id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(){
        return $this->belongsTo(Tenant::class);
    }
}
