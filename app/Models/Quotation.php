<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\GeneratesCode;

class Quotation extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'BG';

    protected $fillable = [
        'customer_id',
        'code',
        'version',
        'status',
        'valid_until',
        'title',
        'description',
        'subtotal',
        'tax_amount',
        'total',
        'note',
        'created_by',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'subtotal' => 'decimal:0',
        'tax_amount' => 'decimal:0',
        'total' => 'decimal:0',
        'discount_percent' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->code)) {
                $quotation->code = static::generateCode();
            }
            if (empty($quotation->created_by)) {
                $quotation->created_by = Auth::id();
            }
        });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until
            && $this->valid_until->isPast()
            && !in_array($this->status, ['Chấp nhận', 'Đã chuyển DA']);
    }

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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}