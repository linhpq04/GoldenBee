<?php

namespace App\Models;

use App\Traits\GeneratesCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'LH';

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

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (empty($course->code)) {
                $course->code = static::generateCode();
            }
        });
    }

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
