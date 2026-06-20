<?php

namespace App\Models;

use App\Traits\GeneratesCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Contract extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'HD';

    protected $fillable = [
        'customer_id',
        'project_id',
        'parent_id',
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
        'created_by',
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

    protected static function booted(): void
    {
        static::creating(function (Contract $contract) {
            if (empty($contract->code)) {
                $contract->code = static::generateCode();
            }
            if (empty($contract->created_by)) {
                $contract->created_by = Auth::id();
            }
        });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date
            && $this->end_date->isPast()
            && !in_array($this->status, ['Hết hạn', 'Đã hủy']);
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

    public function parent()
    {
        return $this->belongsTo(Contract::class, 'parent_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function children()
    {
        return $this->hasMany(Contract::class, 'parent_id');
    }
}
