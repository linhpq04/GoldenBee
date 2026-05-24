<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
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
        'registered_at' => 'date',
        'expires_at' => 'date',
        'last_renewed_at' => 'date',
        'auto_renew' => 'boolean',
        'purchase_price' => 'decimal:0',
        'service_fee' => 'decimal:0',
        'selling_price' => 'decimal:0',
    ];

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
