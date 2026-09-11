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
        'department_id',
        'first_name',
        'last_name',
        'designation',
        'email',
        'username',
        'password',
        'phone',
        'avatar',
        'status',
        'last_login_at',
        'joining_date',
        'probation_months',
        'dob',
        'father_name',
        'mother_name',
        'aadhaar_number',
        'pan_number',
        'basic_salary',
        'pay_frequency',
        'bank_name',
        'account_number',
        'ifsc_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'status' => 'boolean',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'joining_date' => 'date',
        'dob' => 'date',
        'probation_months' => 'integer',
        'basic_salary' => 'decimal:2',
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class, 'invited_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Helpers
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get User Display Designation with fallbacks for Owner, Admin, etc.
     */
    public function getDisplayDesignationAttribute(): string
    {
        if (!empty($this->designation)) {
            return $this->designation;
        }

        $roleCode = strtolower($this->role?->code ?? '');
        $roleName = strtolower($this->role?->name ?? '');

        if ($roleCode === 'owner' || $roleName === 'owner') {
            return 'Owner';
        }

        if ($roleCode === 'admin' || $roleName === 'admin') {
            return 'Admin / HR';
        }

        if ($roleCode === 'manager' || $roleName === 'manager') {
            return 'Manager';
        }

        return $this->role?->name ?? 'Employee';
    }

    /**
     * Get Total Days of Approved Leaves Taken (Current Year)
     */
    public function getApprovedLeavesDaysAttribute(): float
    {
        $approvedLeaves = $this->leaves()
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->get();

        $days = 0.0;
        foreach ($approvedLeaves as $leave) {
            if (in_array($leave->type, ['half_day', 'first_half', 'second_half', 'short_leave'])) {
                $days += 0.5;
            } else {
                $days += $leave->start_date->diffInDays($leave->end_date) + 1;
            }
        }

        return $days;
    }

    /**
     * Get Remaining Leaves Days (Base Quota 14 Days)
     */
    public function getRemainingLeavesDaysAttribute(): float
    {
        $totalQuota = 14.0;
        $taken = $this->approved_leaves_days;
        return max(0, $totalQuota - $taken);
    }

    /**
     * Get Quarterly Leave Quota Breakdown (14 yearly / 4 quarters = 3.5 days/quarter)
     */
    public function getQuarterlyLeaveStatsAttribute(): array
    {
        $currentYear = now()->year;
        $quarterQuota = 3.5;
        $quarters = [
            'Q1 (Jan-Mar)' => ['start' => "$currentYear-01-01", 'end' => "$currentYear-03-31"],
            'Q2 (Apr-Jun)' => ['start' => "$currentYear-04-01", 'end' => "$currentYear-06-30"],
            'Q3 (Jul-Sep)' => ['start' => "$currentYear-07-01", 'end' => "$currentYear-09-30"],
            'Q4 (Oct-Dec)' => ['start' => "$currentYear-10-01", 'end' => "$currentYear-12-31"],
        ];

        $result = [];
        foreach ($quarters as $qName => $dates) {
            $taken = 0.0;
            $approvedLeaves = $this->leaves()
                ->where('status', 'approved')
                ->whereDate('start_date', '>=', $dates['start'])
                ->whereDate('start_date', '<=', $dates['end'])
                ->get();

            foreach ($approvedLeaves as $leave) {
                if (in_array($leave->type, ['half_day', 'first_half', 'second_half', 'short_leave'])) {
                    $taken += 0.5;
                } else {
                    $taken += $leave->start_date->diffInDays($leave->end_date) + 1;
                }
            }

            $result[$qName] = [
                'quota' => $quarterQuota,
                'taken' => $taken,
                'remaining' => max(0, $quarterQuota - $taken),
            ];
        }

        return $result;
    }

    /**
     * Get Current Quarter Leave Stats
     */
    public function getCurrentQuarterLeaveStatsAttribute(): array
    {
        $currentQuarterNum = ceil(now()->month / 3);
        $qName = "Q{$currentQuarterNum}";
        $allStats = $this->quarterly_leave_stats;

        foreach ($allStats as $key => $stat) {
            if (str_starts_with($key, $qName)) {
                return array_merge(['name' => $key, 'quarter_num' => $currentQuarterNum], $stat);
            }
        }

        return [
            'name' => "Q{$currentQuarterNum}",
            'quarter_num' => $currentQuarterNum,
            'quota' => 3.5,
            'taken' => 0.0,
            'remaining' => 3.5,
        ];
    }

    /**
     * Get Tenure in Organization
     */
    public function getTenureAttribute(): string
    {
        $startDate = $this->joining_date ? \Illuminate\Support\Carbon::parse($this->joining_date) : ($this->created_at ?? now());
        $diff = $startDate->diff(now());
        
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y === 1 ? 'Year' : 'Years');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m === 1 ? 'Month' : 'Months');
        }
        if (empty($parts)) {
            $parts[] = max(1, $diff->d) . ' ' . ($diff->d === 1 ? 'Day' : 'Days');
        }
        
        return implode(' ', $parts);
    }

    /**
     * Get Probation Status (Based on HR set joining_date & probation_months)
     */
    public function getProbationStatusAttribute(): array
    {
        $startDate = $this->joining_date ? \Illuminate\Support\Carbon::parse($this->joining_date) : ($this->created_at ?? now());
        $probationMonths = (int) ($this->probation_months ?? 3);
        $probationEndDate = $startDate->copy()->addMonths($probationMonths);
        
        $now = now();
        if ($now->lt($probationEndDate)) {
            $daysLeft = (int) ceil($now->diffInDays($probationEndDate));
            return [
                'is_probation' => true,
                'label' => 'On Probation',
                'badge' => 'bg-warning-subtle text-warning border border-warning-subtle',
                'detail' => 'On probation until ' . $probationEndDate->format('d M Y') . ' (' . ($daysLeft <= 0 ? 'Last day' : $daysLeft . ' days left') . ')'
            ];
        }
        
        return [
            'is_probation' => false,
            'label' => 'Probation Completed',
            'badge' => 'bg-success-subtle text-success border border-success-subtle',
            'detail' => 'Probation completed on ' . $probationEndDate->format('d M Y')
        ];
    }
}