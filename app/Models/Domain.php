<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'project_id',
        'hosting_id',
        'provider_name',
        'domain_name',
        'status',
        'purchase_price',
        'service_fee',
        'selling_price',
        'registered_at',
        'expires_at',
        'last_renewed_at',
        'auto_renew',
        'remind_days',
        'servername_1',
        'servername_2',
        'servername_3',
        'servername_4',
        'login_username',
        'login_password',
    ];

    protected $hidden = [
        'login_password',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_renewed_at' => 'datetime',
        'auto_renew' => 'boolean',
        'purchase_price' => 'decimal:0',
        'service_fee' => 'decimal:0',
        'selling_price' => 'decimal:0',
    ];

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30
            && $this->status === 'Hoạt động';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->expires_at
            && $this->expires_at->isPast()
            && $this->status !== 'Ngừng hoạt động';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at
            && optional($this->expires_at)->isPast()
            && $this->status !== 'Hết hạn';
    }

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function hosting()
    {
        return $this->belongsTo(Hosting::class);
    }
}
