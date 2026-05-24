<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    // Relations
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_service_permissions')
            ->withPivot('service_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'role_service_permissions')
            ->withPivot('permission_id');
    }
}
