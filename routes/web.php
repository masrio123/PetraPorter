<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PorterController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BankUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryPointController;

Route::post('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
});

Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.'], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('/tenants', TenantController::class);
    Route::resource('/porters', PorterController::class);
    Route::resource('/delivery-points', DeliveryPointController::class);
    Route::resource('/bank-users', BankUserController::class);
    Route::get('/activities', [ActivityController::class, 'index'])->name('activity.activity');
});

Route::patch('/dashboard/delivery-points/{id}/toggle-status', [DeliveryPointController::class, 'toggleStatus'])
    ->name('dashboard.delivery-points.toggle-status');
