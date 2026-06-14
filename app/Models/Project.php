<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\GeneratesCode;

class Project extends Model
{
    use GeneratesCode;
    protected static string $codePrefix = 'DA';

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
        'manager_id',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'warranty_expires_at' => 'date',
        'contract_value' => 'decimal:0',
        'budget' => 'decimal:0',
        'warranty_lifetime' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->code)) {
                $project->code = static::generateCode();
            }
        });
    }

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

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
