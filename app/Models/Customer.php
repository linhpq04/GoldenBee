<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\GeneratesCode;

class Customer extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'KH';

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
