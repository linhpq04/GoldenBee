<?php

namespace App\Models;

use App\Traits\GeneratesCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SupportTicket extends Model
{
    use SoftDeletes, GeneratesCode;
    protected static string $codePrefix = 'HT';

    protected $fillable = [
        'customer_id',
        'project_id',
        'assignee_id',
        'code',
        'title',
        'description',
        'file_path',
        'category',
        'priority',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'file_path' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket) {
            if (empty($ticket->code)) {
                $ticket->code = static::generateCode();
            }
            if (empty($ticket->assignee_id)) {
                // Tự assign cho người tạo nếu là nhân viên
                $ticket->assignee_id = Auth::id();
            }
        });

        static::updating(function (SupportTicket $ticket) {
            if ($ticket->isDirty('status')) {
                if ($ticket->status === 'Đang xử lý' && !$ticket->started_at) {
                    $ticket->started_at = now();
                }
                if (in_array($ticket->status, ['Đã giải quyết', 'Đóng']) && !$ticket->ended_at) {
                    $ticket->ended_at = now();
                }
            }
        });
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

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class, 'ticket_id');
    }
}
