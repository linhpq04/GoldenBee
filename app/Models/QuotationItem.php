<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'quotation_id',
        'service_type',
        'name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'tax_percent',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:0',
        'discount_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'total' => 'decimal:0',
    ];

    // Relations
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}
