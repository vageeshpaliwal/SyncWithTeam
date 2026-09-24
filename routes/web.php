<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\AttendanceScreenshotController;


Route::middleware('guest.custom')->group(function () {
    Route::get('/', [AuthController::class, 'welcome'])->name('welcome');
    Route::get('/register-organization', [AuthController::class, 'registerOrganization'])->name('register.organization');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/store-organization', [AuthController::class, 'storeOrganization'])->name('store.organization');
    Route::post('/user-login', [AuthController::class, 'loginPost'])->name('user.login');
    Route::get('/invitation/{token}', [AuthController::class, 'activateInvitation'])->name('invitation.accept');
    Route::post('/invitation/{token}', [AuthController::class, 'storePassword'])->name('invitation.store');
});

Route::middleware('auth.custom')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::get('/attendance/user/{user}', [AttendanceController::class, 'showUserLogs'])
        ->middleware('permission:attendance.view')
        ->name('attendance.user-logs');

    Route::get('/attendance/export-csv', [AttendanceController::class, 'exportCsv'])
        ->name('attendance.export-csv');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.clock-in');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.clock-out');

    Route::post('/attendance/switch', [AttendanceController::class, 'switchProject'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.switch');

    Route::put('/attendance/{attendance}', [AttendanceController::class, 'updateLog'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.update');

    Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroyLog'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.destroy');

    // Attendance Screenshots
    Route::post('/attendance/screenshot', [AttendanceScreenshotController::class, 'store'])
        ->middleware('permission:attendance.manage')
        ->name('attendance.screenshot.store');

    Route::get('/attendance/screenshots', [AttendanceScreenshotController::class, 'getScreenshots'])
        ->name('attendance.screenshots.get');

    Route::delete('/attendance/screenshots/{screenshot}', [AttendanceScreenshotController::class, 'destroy'])
        ->name('attendance.screenshots.destroy');

    Route::get('/attendance/download-tracker', [AttendanceScreenshotController::class, 'downloadTrackerPackage'])
        ->name('attendance.download-tracker');

    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware('permission:projects.view')
        ->name('projects.index');

    Route::get('/projects/create', [ProjectController::class, 'create'])
        ->middleware('permission:projects.manage')
        ->name('projects.create');

    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('permission:projects.manage')
        ->name('projects.store');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->middleware('permission:projects.view')
        ->name('projects.show');

    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])
        ->middleware('permission:projects.manage')
        ->name('projects.edit');

    Route::put('/projects/{project}', [ProjectController::class, 'update'])
        ->middleware('permission:projects.manage')
        ->name('projects.update');

    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('permission:projects.manage')
        ->name('projects.destroy');

    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])
        ->middleware('permission:tasks.manage')
        ->name('tasks.store');

    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->middleware('permission:tasks.manage')
        ->name('tasks.update');

    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->name('tasks.update-status');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('permission:tasks.manage')
        ->name('tasks.destroy');

    Route::post('/tasks/{task}/comments', [TaskCommentController::class, 'store'])
        ->middleware('permission:tasks.manage')
        ->name('tasks.comments.store');

    Route::delete('/tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])
        ->name('tasks.comments.destroy');

    // Task Subtasks / Checklists
    Route::post('/tasks/{task}/checklists', [TaskController::class, 'storeChecklist'])
        ->name('tasks.checklists.store');
    Route::patch('/checklists/{checklist}/toggle', [TaskController::class, 'toggleChecklist'])
        ->name('tasks.checklists.toggle');
    Route::delete('/checklists/{checklist}', [TaskController::class, 'destroyChecklist'])
        ->name('tasks.checklists.destroy');

    // Bugs & Issues Tracking
    Route::post('/projects/{project}/bugs', [\App\Http\Controllers\BugController::class, 'store'])
        ->name('projects.bugs.store');
    Route::get('/bugs/{bug}', [\App\Http\Controllers\BugController::class, 'show'])
        ->name('bugs.show');
    Route::patch('/bugs/{bug}/status', [\App\Http\Controllers\BugController::class, 'updateStatus'])
        ->name('bugs.update-status');
    Route::delete('/bugs/{bug}', [\App\Http\Controllers\BugController::class, 'destroy'])
        ->name('bugs.destroy');

    Route::post('/projects/{project}/files', [\App\Http\Controllers\ProjectFileController::class, 'store'])
        ->name('projects.files.store');

    Route::delete('/projects/{project}/files/{file}', [\App\Http\Controllers\ProjectFileController::class, 'destroy'])
        ->name('projects.files.destroy');

    Route::get('/billing', [BillingController::class, 'index'])
        ->middleware('permission:billing.view')
        ->name('billing.index');

    Route::post('/billing/upgrade', [BillingController::class, 'upgrade'])
        ->middleware('permission:billing.view')
        ->name('billing.upgrade');

    Route::get('/leaves', [LeaveController::class, 'index'])
        ->middleware('permission:leaves.request')
        ->name('leaves.index');

    Route::post('/leaves', [LeaveController::class, 'store'])
        ->middleware('permission:leaves.request')
        ->name('leaves.store');

    Route::patch('/leaves/{leave}/approve', [LeaveController::class, 'approve'])
        ->middleware('permission:leaves.approve')
        ->name('leaves.approve');

    Route::patch('/leaves/{leave}/reject', [LeaveController::class, 'reject'])
        ->middleware('permission:leaves.approve')
        ->name('leaves.reject');

    Route::get('/leaves/user/{user}', [LeaveController::class, 'show'])
        ->middleware('permission:leaves.approve')
        ->name('leaves.show');

    Route::patch('/leaves/{leave}/cancel', [LeaveController::class, 'cancel'])
        ->middleware('permission:leaves.request')
        ->name('leaves.cancel');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/settings', [OrganizationController::class, 'settings'])
        ->middleware('permission:settings.view')
        ->name('settings.index');

    Route::get('/settings/edit', [OrganizationController::class, 'edit'])
        ->middleware('permission:settings.edit')
        ->name('organization.edit');

    Route::put('/settings', [OrganizationController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('organization.update');

    Route::get('/users', [UserController::class, 'users'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('/users/invite', [UserController::class, 'invite'])
        ->middleware('permission:users.invite')
        ->name('users.invite');

    Route::post('/users/Add', [UserController::class, 'addUser'])
        ->middleware('permission:users.invite')
        ->name('users.store');

    Route::post('/invitations/{invitation}/resend', [UserController::class, 'resendInvitation'])
        ->middleware('permission:users.invite')
        ->name('invitations.resend');

    Route::delete('/invitations/{invitation}', [UserController::class, 'cancelInvitation'])
        ->middleware('permission:users.invite')
        ->name('invitations.cancel');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->middleware('permission:users.view')
        ->name('users.show');

    Route::get('/users/{user}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.manage')
        ->name('users.edit');

    Route::put('/users/{user}', [UserController::class, 'update'])
        ->middleware('permission:users.manage')
        ->name('users.update');

    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->middleware('permission:users.manage')
        ->name('users.toggle-status');

    Route::delete('/users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.manage')
        ->name('users.destroy');

    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('/roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.manage')
        ->name('roles.create');

    Route::post('/roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.manage')
        ->name('roles.store');

    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.manage')
        ->name('roles.edit');

    Route::put('/roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.manage')
        ->name('roles.update');

    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.manage')
        ->name('roles.destroy');

    Route::get('/my-department', [DepartmentController::class, 'myDepartment'])
        ->name('my-department');

    Route::get('/departments', [DepartmentController::class, 'index'])
        ->middleware('permission:departments.view')
        ->name('departments.index');

    Route::get('/departments/create', [DepartmentController::class, 'create'])
        ->middleware('permission:departments.manage')
        ->name('departments.create');

    Route::post('/departments', [DepartmentController::class, 'store'])
        ->middleware('permission:departments.manage')
        ->name('departments.store');

    Route::get('/departments/{department}', [DepartmentController::class, 'show'])
        ->middleware('permission:departments.view')
        ->name('departments.show');

    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])
        ->middleware('permission:departments.manage')
        ->name('departments.edit');

    Route::put('/departments/{department}', [DepartmentController::class, 'update'])
        ->middleware('permission:departments.manage')
        ->name('departments.update');

    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])
        ->middleware('permission:departments.manage')
        ->name('departments.destroy');

    // Notifications
    Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    // Holidays
    Route::get('/holidays', [\App\Http\Controllers\HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [\App\Http\Controllers\HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{holiday}', [\App\Http\Controllers\HolidayController::class, 'destroy'])->name('holidays.destroy');

    // Announcements
    Route::get('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Team Chat & Discussions
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/messages', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/chat/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');

    // Automated Payroll & Salary Slips
    Route::get('/payrolls', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payrolls.index');
    Route::post('/payrolls/generate', [\App\Http\Controllers\PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::get('/payrolls/my-payslips', [\App\Http\Controllers\PayrollController::class, 'myPayslips'])->name('payrolls.my-payslips');
    Route::post('/payrolls/download-by-period', [\App\Http\Controllers\PayrollController::class, 'downloadByPeriod'])->name('payrolls.download-by-period');
    Route::get('/payrolls/{payroll}/slip', [\App\Http\Controllers\PayrollController::class, 'slip'])->name('payrolls.slip');
    Route::get('/payrolls/{payroll}/pdf', [\App\Http\Controllers\PayrollController::class, 'downloadPdf'])->name('payrolls.pdf');
    Route::get('/payrolls/{payroll}/edit', [\App\Http\Controllers\PayrollController::class, 'edit'])->name('payrolls.edit');
    Route::put('/payrolls/{payroll}', [\App\Http\Controllers\PayrollController::class, 'update'])->name('payrolls.update');
    Route::patch('/payrolls/{payroll}/mark-paid', [\App\Http\Controllers\PayrollController::class, 'markPaid'])->name('payrolls.mark-paid');
    Route::delete('/payrolls/{payroll}', [\App\Http\Controllers\PayrollController::class, 'destroy'])->name('payrolls.destroy');
});

