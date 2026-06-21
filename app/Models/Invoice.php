<?php

namespace App\Models;

use App\Traits\GeneratesCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'HD';

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
        'contract_id',
        'file_path',
        'note',
        'created_by',
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

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->code)) {
                $invoice->code = static::generateCode();
            }
            if (empty($invoice->created_by)) {
                $invoice->created_by = Auth::id();
            }
        });
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['Đã thanh toán', 'Đã hủy']);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return (float) $this->paid_amount >= (float) $this->total && (float) $this->total > 0;
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->paid_amount);
    }

    // Relations
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
