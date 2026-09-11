<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get Projects associated with employees in this department
     */
    public function getProjectsAttribute()
    {
        $userIds = $this->users()->pluck('id');
        return Project::whereHas('users', function ($q) use ($userIds) {
            $q->whereIn('users.id', $userIds);
        })->get();
    }

    public function getProjectsCountAttribute(): int
    {
        $userIds = $this->users()->pluck('id');
        return Project::whereHas('users', function ($q) use ($userIds) {
            $q->whereIn('users.id', $userIds);
        })->count();
    }
}
