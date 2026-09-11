<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\User;
use App\Services\CurrentUserService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(
        protected CurrentUserService $currentUserService
    ) {}

    public function index()
    {
        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        // 1. Fetch user's own leaves cleanly
        $myLeaves = Leave::where('user_id', $user->id)
            ->where('organization_id', $org->id)
            ->orderByDesc('created_at')
            ->get();

        // 2. Fetch pending approvals if user has approval rights
        $pendingApprovals = collect();

        if ($this->currentUserService->hasPermission('leaves.manage')) {
            $pendingApprovals = Leave::where('organization_id', $org->id)
                ->where('user_id', '!=', $user->id)
                ->where('status', 'pending')
                ->with(['user.role', 'user.department'])
                ->orderByDesc('created_at')
                ->get();
        } elseif ($this->currentUserService->hasPermission('leaves.approve')) {
            $dept = $user->department;
            if ($dept) {
                $pendingApprovals = Leave::where('organization_id', $org->id)
                    ->where('user_id', '!=', $user->id)
                    ->where('status', 'pending')
                    ->whereHas('user', function($q) use ($dept) {
                        $q->where('department_id', $dept->id);
                    })
                    ->with(['user.role', 'user.department'])
                    ->orderByDesc('created_at')
                    ->get();
            }
        }

        // 3. Fetch employee leave balances (Managers and Employees of organization)
        $employeeBalances = collect();
        $todayLeaves = collect();
        $allOrgLeaves = collect();

        if ($this->currentUserService->hasPermission('leaves.manage') || $this->currentUserService->hasPermission('leaves.approve')) {
            $employeeBalances = User::where('organization_id', $org->id)
                ->whereHas('role', function($q) {
                    $q->whereIn('code', ['manager', 'employee']);
                })
                ->with(['role', 'department', 'leaves'])
                ->orderBy('first_name')
                ->get();

            // Today's On-Leave Employees
            $todayLeaves = Leave::where('organization_id', $org->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', now()->toDateString())
                ->whereDate('end_date', '>=', now()->toDateString())
                ->with(['user.role', 'user.department'])
                ->get();

            // All Org Leave History
            $allOrgLeaves = Leave::where('organization_id', $org->id)
                ->with(['user.role', 'user.department'])
                ->orderByDesc('created_at')
                ->take(100)
                ->get();
        }

        return view('leaves.index', compact('myLeaves', 'pendingApprovals', 'employeeBalances', 'todayLeaves', 'allOrgLeaves'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:full_day,half_day,first_half,second_half,short_leave',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        $leave = Leave::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Send notifications to Admin, HR, Manager
        $typeLabel = str_replace('_', ' ', $request->type);
        $approvers = User::where('organization_id', $org->id)
            ->where('id', '!=', $user->id)
            ->whereHas('role', function($q) {
                $q->whereIn('code', ['admin', 'owner', 'hr', 'manager']);
            })->get();

        foreach ($approvers as $approver) {
            \App\Models\Notification::create([
                'organization_id' => $org->id,
                'user_id' => $approver->id,
                'title' => 'New Leave Application',
                'message' => "{$user->full_name} has applied for a {$typeLabel} leave request.",
                'type' => 'leave_applied',
                'link' => route('leaves.index'),
            ]);
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function approve(Request $request, Leave $leave)
    {
        $this->ensureCanApproveOrReject($leave);

        $leave->update([
            'status' => 'approved',
            'approved_by' => $this->currentUserService->getUser()->id,
            'manager_remarks' => $request->input('manager_remarks'),
        ]);

        // Send notification to applicant employee
        $startDateStr = $leave->start_date ? $leave->start_date->format('M d, Y') : '';
        $remarksMsg = $request->filled('manager_remarks') ? " Note: " . $request->input('manager_remarks') : "";
        \App\Models\Notification::create([
            'organization_id' => $leave->organization_id,
            'user_id' => $leave->user_id,
            'title' => 'Leave Request Approved',
            'message' => "Your leave request for {$startDateStr} has been approved.{$remarksMsg}",
            'type' => 'leave_approved',
            'link' => route('leaves.index'),
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request approved successfully.');
    }

    public function reject(Request $request, Leave $leave)
    {
        $this->ensureCanApproveOrReject($leave);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $this->currentUserService->getUser()->id,
            'manager_remarks' => $request->input('manager_remarks'),
        ]);

        // Send notification to applicant employee
        $startDateStr = $leave->start_date ? $leave->start_date->format('M d, Y') : '';
        $remarksMsg = $request->filled('manager_remarks') ? " Reason: " . $request->input('manager_remarks') : "";
        \App\Models\Notification::create([
            'organization_id' => $leave->organization_id,
            'user_id' => $leave->user_id,
            'title' => 'Leave Request Rejected',
            'message' => "Your leave request for {$startDateStr} has been rejected.{$remarksMsg}",
            'type' => 'leave_rejected',
            'link' => route('leaves.index'),
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request rejected successfully.');
    }

    public function show(User $user)
    {
        $org = $this->currentUserService->getOrganization();

        if ($user->organization_id !== $org->id) {
            abort(404);
        }

        if (!$this->currentUserService->hasPermission('leaves.manage') && !$this->currentUserService->hasPermission('leaves.approve')) {
            abort(403, 'Unauthorized to view employee leave details.');
        }

        $user->load(['role', 'department', 'leaves' => function($q) {
            $q->where('status', '!=', 'cancelled')->orderByDesc('created_at');
        }]);

        return view('leaves.show', compact('user'));
    }

    public function cancel(Leave $leave)
    {
        $user = $this->currentUserService->getUser();

        if ($leave->user_id !== $user->id) {
            abort(403, 'You can only cancel your own leave requests.');
        }

        if ($leave->status !== 'pending') {
            return redirect()->route('leaves.index')->with('error', 'Only pending leaves can be cancelled.');
        }

        $leave->update([
            'status' => 'cancelled',
        ]);

        return redirect()->route('leaves.index')->with('success', 'Leave request cancelled successfully.');
    }

    protected function ensureCanApproveOrReject(Leave $leave): void
    {
        $user = $this->currentUserService->getUser();
        $org = $this->currentUserService->getOrganization();

        if ($leave->organization_id !== $org->id) {
            abort(404);
        }

        if ($this->currentUserService->hasPermission('leaves.manage')) {
            return;
        }

        if ($this->currentUserService->hasPermission('leaves.approve')) {
            $dept = $user->department;
            if ($dept && $leave->user->department_id === $dept->id) {
                return;
            }
        }

        abort(403, 'You do not have permission to approve or reject this leave request.');
    }
}
