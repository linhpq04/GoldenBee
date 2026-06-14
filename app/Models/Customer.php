<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    public $timestamps = true;

    protected $fillable = [
        'code',
        'name',
        'company_name',
        'email',
        'phone',
        'tax_code',
        'website',
        'address',
        'type',
        'status',
        'source',
        'source_detail',
        'industry',
        'region',
        'note',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->code)) {
                $customer->code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $datePart = now()->format('dmy'); // ddmmyy
        $prefix = 'KH-' . $datePart;

        $lastToday = static::where('code', 'like', $prefix . '-%')
            ->orderByDesc('code')
            ->value('code');

        if ($lastToday) {
            $lastNumber = (int) substr($lastToday, -3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // Relations
    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
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

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
