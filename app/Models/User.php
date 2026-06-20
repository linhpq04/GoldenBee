<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @mixin \Spatie\Permission\Traits\HasRoles
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'position',
        'status',
        'id_number',
        'id_issued_date',
        'id_issued_place',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact',
        'base_salary',
        'hourly_rate',
        'tax_code',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'bank_account_name',
        'joined_at',
        'left_at',
        'note',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id_issued_date' => 'date',
        'date_of_birth' => 'date',
        'joined_at' => 'date',
        'left_at' => 'date',
        'base_salary' => 'decimal:0',
        'hourly_rate' => 'decimal:0',
    ];

    // Relations
    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(SupportTicket::class, 'assignee_id');
    }

    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }
}
