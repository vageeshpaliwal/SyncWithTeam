<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\UserController;


Route::middleware('guest.custom')->group(function () {
    Route::get('/', [AuthController::class, 'welcome'])->name('welcome');
    Route::get('/register-organization', [AuthController::class, 'registerOrganization'])->name('register.organization');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/store-organization', [AuthController::class, 'storeOrganization'])->name('store.organization');
    Route::post('/user-login', [AuthController::class, 'loginPost'])->name('user.login');

});

Route::middleware('auth.custom')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/settings', [OrganizationController::class, 'settings'])->name('settings.index');
    Route::get('/settings/edit', [OrganizationController::class, 'edit'])->name('organization.edit');
    Route::put('/settings', [OrganizationController::class, 'update'])->name('organization.update');
    Route::get('/users', [UserController::class, 'users'])->name('users.index');
    Route::get('/users/invite', [UserController::class, 'invite'])->name('users.invite');

});

