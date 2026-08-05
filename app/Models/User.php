<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'role_id',
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'phone',
        'avatar',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}