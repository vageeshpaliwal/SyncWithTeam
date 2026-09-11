<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate or recalculate payroll for a single user for a given month and year.
     */
    public function generateForUser(
        User $user,
        int $month,
        int $year,
        ?array $customOverrides = null,
        ?int $creatorId = null
    ): Payroll {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;
        $salaryMonth = $startDate->format('Y-m');

        // 1. Calculate Attendance logs (Present days and total logged hours)
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNotNull('clock_in_at')
            ->get();

        $presentDays = (float)$attendances->pluck('date')->unique()->count();

        $totalHoursWorked = 0;
        foreach ($attendances as $att) {
            if ($att->clock_in_at && $att->clock_out_at) {
                $totalHoursWorked += $att->clock_in_at->diffInMinutes($att->clock_out_at) / 60;
            } elseif ($att->clock_in_at) {
                $totalHoursWorked += 8; // standard 8 hours default if not yet clocked out
            }
        }
        $totalHoursWorked = round($totalHoursWorked, 2);

        // 2. Calculate Approved Leaves in this month
        $leaves = Leave::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            })
            ->get();

        $paidLeaves = 0;
        foreach ($leaves as $leave) {
            $lStart = Carbon::parse($leave->start_date)->max($startDate);
            $lEnd = Carbon::parse($leave->end_date)->min($endDate);
            $paidLeaves += ($lStart->diffInDays($lEnd) + 1);
        }

        // Standard 30 days working standard or actual days
        $workingDays = $daysInMonth;
        
        // Base salary from user profile (fallback to 25000 if not set)
        $baseSalary = (float)($user->basic_salary > 0 ? $user->basic_salary : 35000);

        // Calculate unpaid leaves / absent days
        // Note: If attendance logging is partial, we ensure present + leaves are tracked
        $unpaidLeaves = max(0, $workingDays - ($presentDays + $paidLeaves));
        // For standard monthly payroll, if attendance is optional or not strictly enforced, allow override
        if (isset($customOverrides['unpaid_leaves'])) {
            $unpaidLeaves = (float)$customOverrides['unpaid_leaves'];
        }

        // Allowances calculation
        $allowances = [];
        if (isset($customOverrides['allowances']) && is_array($customOverrides['allowances'])) {
            $allowances = $customOverrides['allowances'];
        } else {
            $hra = round($baseSalary * 0.40, 2); // 40% HRA
            $special = round($baseSalary * 0.15, 2); // 15% Special Allowance
            $medical = 1250.00;
            $allowances = [
                ['name' => 'House Rent Allowance (HRA)', 'amount' => $hra],
                ['name' => 'Special Allowance', 'amount' => $special],
                ['name' => 'Medical & Conveyance', 'amount' => $medical],
            ];
        }

        $totalAllowances = array_reduce($allowances, fn($carry, $item) => $carry + (float)($item['amount'] ?? 0), 0);
        $totalAllowances = round($totalAllowances, 2);

        // Deductions calculation
        $deductions = [];
        if (isset($customOverrides['deductions']) && is_array($customOverrides['deductions'])) {
            $deductions = $customOverrides['deductions'];
        } else {
            $pf = round($baseSalary * 0.12, 2); // 12% PF
            $profTax = 200.00; // Professional Tax
            $deductions = [
                ['name' => 'Provident Fund (PF - 12%)', 'amount' => $pf],
                ['name' => 'Professional Tax (PT)', 'amount' => $profTax],
            ];

            // If there are unpaid leaves, calculate Loss of Pay (LOP)
            if ($unpaidLeaves > 0 && isset($customOverrides['apply_lop']) && $customOverrides['apply_lop']) {
                $perDayRate = round($baseSalary / $workingDays, 2);
                $lopAmount = round($unpaidLeaves * $perDayRate, 2);
                $deductions[] = ['name' => "Loss of Pay ({$unpaidLeaves} days LOP)", 'amount' => $lopAmount];
            }
        }

        $totalDeductions = array_reduce($deductions, fn($carry, $item) => $carry + (float)($item['amount'] ?? 0), 0);
        $totalDeductions = round($totalDeductions, 2);

        $grossSalary = round($baseSalary + $totalAllowances, 2);
        $netSalary = max(0, round($grossSalary - $totalDeductions, 2));

        // Format unique payslip number: PAY-202609-0012
        $payslipNumber = sprintf('PAY-%s%02d-%04d', $year, $month, $user->id);

        return DB::transaction(function () use (
            $user,
            $salaryMonth,
            $month,
            $year,
            $payslipNumber,
            $workingDays,
            $presentDays,
            $paidLeaves,
            $unpaidLeaves,
            $totalHoursWorked,
            $baseSalary,
            $allowances,
            $totalAllowances,
            $deductions,
            $totalDeductions,
            $grossSalary,
            $netSalary,
            $creatorId
        ) {
            return Payroll::updateOrCreate(
                [
                    'organization_id' => $user->organization_id,
                    'user_id' => $user->id,
                    'salary_month' => $salaryMonth,
                ],
                [
                    'month' => $month,
                    'year' => $year,
                    'payslip_number' => $payslipNumber,
                    'working_days' => $workingDays,
                    'present_days' => $presentDays,
                    'paid_leaves' => $paidLeaves,
                    'unpaid_leaves' => $unpaidLeaves,
                    'total_hours_worked' => $totalHoursWorked,
                    'overtime_hours' => 0,
                    'basic_salary' => $baseSalary,
                    'allowances' => $allowances,
                    'total_allowances' => $totalAllowances,
                    'deductions' => $deductions,
                    'total_deductions' => $totalDeductions,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                    'created_by' => $creatorId,
                ]
            );
        });
    }

    /**
     * Batch generate payroll for all active users in an organization.
     */
    public function generateBatchForOrganization(int $organizationId, int $month, int $year, int $creatorId): array
    {
        $users = User::where('organization_id', $organizationId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhere('status', true);
            })
            ->get();

        $processed = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                $this->generateForUser($user, $month, $year, null, $creatorId);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Failed for {$user->full_name}: " . $e->getMessage();
            }
        }

        return [
            'processed' => $processed,
            'total' => $users->count(),
            'errors' => $errors,
        ];
    }

    /**
     * Mark a payroll record as paid.
     */
    public function markAsPaid(Payroll $payroll, array $data): bool
    {
        return $payroll->update([
            'payment_status' => 'paid',
            'payment_date' => $data['payment_date'] ?? now()->toDateString(),
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'remarks' => $data['remarks'] ?? $payroll->remarks,
        ]);
    }
}