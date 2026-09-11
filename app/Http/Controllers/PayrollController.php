<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use App\Services\CurrentUserService;
use App\Services\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService,
        protected PayrollService $payrollService
    ) {}

    /**
     * Display Payroll Management Dashboard & Listing (HR / Admin / Owner).
     */
    public function index(Request $request)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        // If user doesn't have organization-wide payroll permission, send them to their own payslips
        if (!$this->currentUserService->hasPermission('payroll.view') && !$this->currentUserService->hasPermission('payroll.manage')) {
            return redirect()->route('payrolls.my-payslips');
        }

        $orgId = $currentUser->organization_id;
        $selectedMonth = (int)$request->input('month', now()->month);
        $selectedYear = (int)$request->input('year', now()->year);
        $salaryMonth = sprintf('%04d-%02d', $selectedYear, $selectedMonth);
        $selectedStatus = $request->input('status');
        $search = $request->input('search');

        $query = Payroll::where('organization_id', $orgId)
            ->where('salary_month', $salaryMonth)
            ->with(['user.department', 'user.role', 'creator']);

        if ($selectedStatus && in_array($selectedStatus, ['pending', 'paid', 'processing', 'cancelled'])) {
            $query->where('payment_status', $selectedStatus);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->orderByDesc('created_at')->get();

        // Calculate Overview Summary Stats
        $allMonthPayrolls = Payroll::where('organization_id', $orgId)
            ->where('salary_month', $salaryMonth)
            ->get();

        $stats = [
            'total_net' => (float)$allMonthPayrolls->sum('net_salary'),
            'total_gross' => (float)$allMonthPayrolls->sum('gross_salary'),
            'total_deductions' => (float)$allMonthPayrolls->sum('total_deductions'),
            'total_count' => $allMonthPayrolls->count(),
            'paid_count' => $allMonthPayrolls->where('payment_status', 'paid')->count(),
            'paid_amount' => (float)$allMonthPayrolls->where('payment_status', 'paid')->sum('net_salary'),
            'pending_count' => $allMonthPayrolls->where('payment_status', 'pending')->count(),
            'pending_amount' => (float)$allMonthPayrolls->where('payment_status', 'pending')->sum('net_salary'),
        ];

        // List of all active employees in organization for quick individual generate
        $employees = User::where('organization_id', $orgId)
            ->where(function ($q) {
                $q->where('status', 1)->orWhere('status', true);
            })
            ->orderBy('first_name')
            ->get();

        return view('payroll.index', compact(
            'payrolls',
            'stats',
            'selectedMonth',
            'selectedYear',
            'selectedStatus',
            'search',
            'employees'
        ));
    }

    /**
     * Batch or Single Payroll Generator.
     */
    public function generate(Request $request)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser || !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $month = (int)$request->input('month');
        $year = (int)$request->input('year');
        $userId = $request->input('user_id');

        if ($userId) {
            $user = User::where('id', $userId)->where('organization_id', $currentUser->organization_id)->firstOrFail();
            
            if ($request->filled('basic_salary')) {
                $user->basic_salary = (float)$request->input('basic_salary');
                $user->save();
            }

            $this->payrollService->generateForUser($user, $month, $year, null, $currentUser->id);
            return redirect()->route('payrolls.index', ['month' => $month, 'year' => $year])
                ->with('success', "Payroll generated successfully for {$user->full_name}.");
        }

        $result = $this->payrollService->generateBatchForOrganization(
            $currentUser->organization_id,
            $month,
            $year,
            $currentUser->id
        );

        return redirect()->route('payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', "Processed {$result['processed']} employee salary records for " . Carbon::createFromDate($year, $month, 1)->format('F Y') . ".");
    }

    /**
     * Display printable enterprise salary slip.
     */
    public function slip(Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        // Must be employee themselves OR have payroll.view permission
        if ($payroll->user_id !== $currentUser->id && !$this->currentUserService->hasPermission('payroll.view') && !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'You do not have permission to view this salary slip.');
        }

        $payroll->load(['user.department', 'user.role', 'organization', 'creator']);

        return view('payroll.slip', compact('payroll'));
    }

    /**
     * Download or stream PDF salary slip.
     */
    public function downloadPdf(Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        if ($payroll->user_id !== $currentUser->id && !$this->currentUserService->hasPermission('payroll.view') && !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'You do not have permission to download this salary slip.');
        }

        $payroll->load(['user.department', 'user.role', 'organization']);

        $pdf = Pdf::loadView('payroll.pdf', compact('payroll'))->setPaper('a4', 'portrait');
        $filename = "Salary_Slip_{$payroll->payslip_number}_{$payroll->salary_month}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Show edit form to adjust custom allowances / deductions.
     */
    public function edit(Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser || !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $payroll->load(['user.department', 'user.role']);

        return view('payroll.edit', compact('payroll'));
    }

    /**
     * Update custom salary allowances and deductions.
     */
    public function update(Request $request, Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser || !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'working_days' => 'required|numeric|min:1',
            'present_days' => 'required|numeric|min:0',
            'paid_leaves' => 'required|numeric|min:0',
            'unpaid_leaves' => 'required|numeric|min:0',
            'allowance_names' => 'nullable|array',
            'allowance_amounts' => 'nullable|array',
            'deduction_names' => 'nullable|array',
            'deduction_amounts' => 'nullable|array',
            'payment_status' => 'required|in:pending,processing,paid,cancelled',
            'remarks' => 'nullable|string',
        ]);

        $allowances = [];
        $names = $request->input('allowance_names', []);
        $amounts = $request->input('allowance_amounts', []);
        foreach ($names as $idx => $name) {
            if (trim($name) !== '' && isset($amounts[$idx])) {
                $allowances[] = [
                    'name' => trim($name),
                    'amount' => (float)$amounts[$idx]
                ];
            }
        }
        $totalAllowances = array_reduce($allowances, fn($c, $i) => $c + (float)$i['amount'], 0);

        $deductions = [];
        $dNames = $request->input('deduction_names', []);
        $dAmounts = $request->input('deduction_amounts', []);
        foreach ($dNames as $idx => $name) {
            if (trim($name) !== '' && isset($dAmounts[$idx])) {
                $deductions[] = [
                    'name' => trim($name),
                    'amount' => (float)$dAmounts[$idx]
                ];
            }
        }
        $totalDeductions = array_reduce($deductions, fn($c, $i) => $c + (float)$i['amount'], 0);

        $basic = (float)$request->input('basic_salary');
        $gross = round($basic + $totalAllowances, 2);
        $net = max(0, round($gross - $totalDeductions, 2));

        $payroll->update([
            'basic_salary' => $basic,
            'working_days' => $request->input('working_days'),
            'present_days' => $request->input('present_days'),
            'paid_leaves' => $request->input('paid_leaves'),
            'unpaid_leaves' => $request->input('unpaid_leaves'),
            'allowances' => $allowances,
            'total_allowances' => $totalAllowances,
            'deductions' => $deductions,
            'total_deductions' => $totalDeductions,
            'gross_salary' => $gross,
            'net_salary' => $net,
            'payment_status' => $request->input('payment_status'),
            'remarks' => $request->input('remarks'),
        ]);

        return redirect()->route('payrolls.index', ['month' => $payroll->month, 'year' => $payroll->year])
            ->with('success', "Salary slip #{$payroll->payslip_number} updated successfully.");
    }

    /**
     * Mark salary as paid.
     */
    public function markPaid(Request $request, Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser || !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);

        $this->payrollService->markAsPaid($payroll, $request->only([
            'payment_date',
            'payment_method',
            'transaction_reference',
            'remarks'
        ]));

        return back()->with('success', "Salary slip #{$payroll->payslip_number} marked as Paid.");
    }

    /**
     * Employee Self-Service: My Payslips.
     */
    public function myPayslips(Request $request)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $selectedYear = (int)$request->input('year', now()->year);

        $payslips = Payroll::where('user_id', $currentUser->id)
            ->where('year', $selectedYear)
            ->orderByDesc('month')
            ->get();

        $stats = [
            'total_earned' => (float)$payslips->where('payment_status', 'paid')->sum('net_salary'),
            'slips_count' => $payslips->count(),
            'latest_slip' => $payslips->first(),
        ];

        return view('payroll.my_payslips', compact('payslips', 'stats', 'selectedYear', 'currentUser'));
    }

    /**
     * Download or view payslip by selecting month and year.
     */
    public function downloadByPeriod(Request $request)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
            'action' => 'required|in:download,view',
        ]);

        $month = (int)$request->input('month');
        $year = (int)$request->input('year');
        $action = $request->input('action');

        $payroll = Payroll::where('user_id', $currentUser->id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if (!$payroll) {
            $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
            return back()->with('error', "Salary slip for {$monthName} has not been released yet.");
        }

        if ($action === 'view') {
            return redirect()->route('payrolls.slip', $payroll->id);
        }

        return $this->downloadPdf($payroll);
    }

    /**
     * Delete a payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        $currentUser = $this->currentUserService->getUser();
        if (!$currentUser || !$this->currentUserService->hasPermission('payroll.manage')) {
            abort(403, 'Unauthorized action.');
        }

        $payroll->delete();

        return back()->with('success', 'Payroll entry removed successfully.');
    }
}