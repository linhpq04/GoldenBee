<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'customer_id',
        'project_id',
        'code',
        'title',
        'type',
        'status',
        'description',
        'contract_value',
        'has_vat',
        'vat_percent',
        'total',
        'signed_at',
        'start_date',
        'end_date',
        'file_path',
        'note',
    ];

    protected $casts = [
        'signed_at' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:0',
        'vat_percent' => 'decimal:2',
        'total' => 'decimal:0',
        'has_vat' => 'boolean',
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
}
