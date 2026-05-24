<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_service_permissions')
            ->withPivot('service_id');
    }
}
