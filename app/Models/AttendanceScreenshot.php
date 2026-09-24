<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceScreenshot extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'attendance_id',
        'project_id',
        'task_id',
        'screenshot_path',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->screenshot_path);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
