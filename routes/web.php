<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AjaxController;

Route::post('ajax', [AjaxController::class, 'index'])->name('ajax');
Route::get('/', [PageController::class, 'login'])->name('login');
Route::get('logout', [PageController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth']], function () {
    Route::get('dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('users', [PageController::class, 'users'])->name('users');
    Route::get('users/add', [PageController::class, 'adduser'])->name('adduser');
    Route::get('settings', [PageController::class, 'settings'])->name('settings');
    Route::get('reporting', [PageController::class, 'reporting'])->name('reporting');
    Route::get('staffs', [PageController::class, 'staffs'])->name('staffs');
    Route::get('staffs/create', [PageController::class, 'staffCreate'])->name('staffs.create');
    Route::get('staffs/{id}/edit', [PageController::class, 'staffEdit'])->name('staff.edit');
    Route::get('payroll-report', [PageController::class, 'payrollReport'])->name('payrollReport');
    Route::get('leave-management', [PageController::class, 'leaveManagement'])->name('leaveManagement');
    Route::get('attendance', [PageController::class, 'attendance'])->name('attendance');
    Route::get('holidays', [PageController::class, 'holidays'])->name('holidays');

});
