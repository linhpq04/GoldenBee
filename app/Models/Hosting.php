<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hosting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'project_id',
        'server_id',
        'provider_name',
        'hosting_name',
        'hosting_type',
        'status',
        'primary_name',
        'server_config',
        'purchase_price',
        'service_fee',
        'selling_price',
        'billing_cycle',
        'setup_at',
        'expires_at',
        'auto_renew',
        'remind_days',
        'cpanel_username',
        'cpanel_password',
        'ftp_host',
        'ftp_username',
        'ftp_password',
        'db_host',
        'db_name',
        'db_username',
        'db_password',
        'note',
    ];

    protected $hidden = [
        'cpanel_password',
        'ftp_password',
        'db_password',
    ];

    protected $casts = [
        'setup_at' => 'date',
        'expires_at' => 'date',
        'auto_renew' => 'boolean',
        'purchase_price' => 'decimal:0',
        'service_fee' => 'decimal:0',
        'selling_price' => 'decimal:0',
    ];

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at
            && optional($this->expires_at)->isPast()
            && !in_array($this->status, ['Ngừng hoạt động', 'Hết hạn']);
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at
            && $this->expires_at->isFuture()
            && $this->expires_at->diffInDays(now()) <= 30
            && $this->status === 'Hoạt động';
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

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
}
