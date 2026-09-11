<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'task_id',
        'user_id',
        'comment',
    ];

    public function getContentAttribute()
    {
        return $this->comment;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
