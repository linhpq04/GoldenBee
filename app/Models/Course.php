<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'format',
        'status',
        'max_student',
        'duration_hours',
        'price',
    ];

    protected $casts = [
        'duration_hours' => 'decimal:2',
        'price' => 'decimal:0',
    ];

    // Relations
    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Customer::class, CourseEnrollment::class, 'course_id', 'id', 'id', 'customer_id');
    }
}
