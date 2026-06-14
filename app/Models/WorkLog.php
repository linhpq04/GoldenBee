<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'user_id',
        'task_id',
        'task_type',
        'work_date',
        'hours',
        'description',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
