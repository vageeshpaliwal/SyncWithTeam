<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'project_id',
        'task_id',
        'date',
        'clock_in_at',
        'clock_out_at',
        'clock_in_ip',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_in_address',
        'clock_out_ip',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_out_address',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'clock_in_latitude' => 'float',
        'clock_in_longitude' => 'float',
        'clock_out_latitude' => 'float',
        'clock_out_longitude' => 'float',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function screenshots()
    {
        return $this->hasMany(AttendanceScreenshot::class);
    }
}
