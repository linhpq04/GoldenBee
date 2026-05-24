<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'customer_id',
        'code',
        'status',
        'valid_until',
        'title',
        'description',
        'subtotal',
        'tax_amount',
        'total',
        'note',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:0',
        'tax_amount' => 'decimal:0',
        'total' => 'decimal:0',
    ];

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
