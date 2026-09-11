<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if a user has an active approved leave at a given time and determine if tracking is blocked.
     *
     * @param int $userId
     * @param mixed $dateTime
     * @return array|null
     */
    public static function getActiveLeaveForUser($userId, $dateTime = null): ?array
    {
        $now = $dateTime ? \Carbon\Carbon::parse($dateTime) : now();
        $todayStr = $now->toDateString();

        $leaves = self::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $todayStr)
            ->whereDate('end_date', '>=', $todayStr)
            ->get();

        foreach ($leaves as $leave) {
            $type = $leave->type;
            $currentHour = (int)$now->format('G'); // 0-23
            $currentMinute = (int)$now->format('i');
            $currentTimeDecimal = $currentHour + ($currentMinute / 60);

            if ($type === 'full_day') {
                return [
                    'leave' => $leave,
                    'blocked' => true,
                    'reason' => 'You are on an approved Full Day leave today.',
                    'label' => 'Full Day Leave',
                ];
            }

            if ($type === 'first_half' || $type === 'half_day') {
                // First half leave is active until 2:00 PM (14:00)
                if ($currentTimeDecimal < 14.0) {
                    return [
                        'leave' => $leave,
                        'blocked' => true,
                        'reason' => 'You are on approved First Half leave (until 2:00 PM).',
                        'label' => 'First Half Leave',
                    ];
                }
            }

            if ($type === 'second_half') {
                // Second half leave is active from 1:30 PM (13.5) onwards
                if ($currentTimeDecimal >= 13.5) {
                    return [
                        'leave' => $leave,
                        'blocked' => true,
                        'reason' => 'You are on approved Second Half leave (from 1:30 PM onwards).',
                        'label' => 'Second Half Leave',
                    ];
                }
            }

            if (!in_array($type, ['first_half', 'second_half', 'half_day'])) {
                return [
                    'leave' => $leave,
                    'blocked' => true,
                    'reason' => 'You are on an approved ' . ucfirst(str_replace('_', ' ', $type)) . ' today.',
                    'label' => ucfirst(str_replace('_', ' ', $type)),
                ];
            }
        }

        return null;
    }
}
