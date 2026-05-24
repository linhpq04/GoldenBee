<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'salary_type',
        'payment_date',
        'status',
        'period',
        'from_date',
        'to_date',
        'work_hours',
        'hourly_rate',
        'work_days',
        'base_salary',
        'bonus',
        'allowance',
        'deduction',
        'advance',
        'net_salary',
        'payment_method',
        'transaction_code',
        'detail_description',
        'note',
        'file_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'work_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:0',
        'base_salary' => 'decimal:0',
        'bonus' => 'decimal:0',
        'allowance' => 'decimal:0',
        'deduction' => 'decimal:0',
        'advance' => 'decimal:0',
        'net_salary' => 'decimal:0',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
