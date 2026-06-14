<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'location',
        'provider_name',
        'status',
        'ram_gb',
        'cpu_cores',
        'disk_gb',
        'bandwidth',
        'os',
        'ssh_host',
        'ssh_port',
        'ssh_username',
        'ssh_password',
        'ssh_key',
        'note',
    ];

    protected $hidden = [
        'ssh_password',
        'ssh_key',
    ];

    // Relations
    public function hostings()
    {
        return $this->hasMany(Hosting::class);
    }
}
