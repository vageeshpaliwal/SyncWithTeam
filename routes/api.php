<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\DepartmentController;

Route::get('/v1/health', [HealthCheckController::class, 'index']);
Route::get('/v1/departments', [DepartmentController::class, 'index']);
