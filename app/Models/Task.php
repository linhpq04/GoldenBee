<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'parent_task_id',
        'assignee_id',
        'title',
        'description',
        'task_type',
        'status',
        'priority',
        'estimated_hours',
        'start_date',
        'due_date',
        'completed_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_date' => 'date',
        'estimated_hours' => 'decimal:2',
    ];

    // Relations
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks()
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
