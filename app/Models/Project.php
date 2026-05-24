<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'quotation_id',
        'code',
        'name',
        'description',
        'type',
        'status',
        'priority',
        'start_date',
        'end_date',
        'contract_value',
        'budget',
        'warranty_lifetime',
        'warranty_months',
        'warranty_expires_at',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'warranty_expires_at' => 'date',
        'contract_value' => 'decimal:0',
        'budget' => 'decimal:0',
        'warranty_lifetime' => 'boolean',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function hostings()
    {
        return $this->hasMany(Hosting::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
