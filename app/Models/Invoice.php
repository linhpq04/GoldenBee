<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'code',
        'invoice_type',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'vat_percent',
        'vat_amount',
        'total',
        'paid_amount',
        'paid_date',
        'payment_method',
        'provider_name',
        'customer_id',
        'invoice_email',
        'service_type',
        'invoice_content',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:0',
        'vat_percent' => 'decimal:2',
        'vat_amount' => 'decimal:0',
        'total' => 'decimal:0',
        'paid_amount' => 'decimal:0',
    ];

    const UPDATED_AT = 'updated_at';

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
