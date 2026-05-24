<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'course_id',
        'customer_id',
        'enrolled_at',
        'status',
        'paid_amount',
        'note',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'paid_amount' => 'decimal:0',
    ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
