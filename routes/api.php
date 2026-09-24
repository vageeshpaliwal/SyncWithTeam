<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\AttendanceScreenshotController;

Route::get('/v1/health', [HealthCheckController::class, 'index']);
Route::get('/v1/departments', [DepartmentController::class, 'index']);

// Desktop Tracker Companion APIs
Route::post('/v1/attendance/screenshot', [AttendanceScreenshotController::class, 'store'])->name('api.attendance.screenshot.store');
Route::get('/v1/attendance/active-session', [AttendanceScreenshotController::class, 'getActiveSession'])->name('api.attendance.active-session');

