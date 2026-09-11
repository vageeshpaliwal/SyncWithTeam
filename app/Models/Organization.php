<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'website',
        'email',
        'phone',
        'status',
        'allowed_ips',
        'geofence_latitude',
        'geofence_longitude',
        'geofence_radius_meters',
        'enforce_ip_restriction',
        'enforce_geofence',
    ];

    protected $casts = [
        'status' => 'boolean',
        'enforce_ip_restriction' => 'boolean',
        'enforce_geofence' => 'boolean',
        'geofence_latitude' => 'float',
        'geofence_longitude' => 'float',
        'geofence_radius_meters' => 'integer',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}