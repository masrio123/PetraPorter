<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BankUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryPointController;
use App\Http\Controllers\PorterController;

Route::get('/', function () {
    return to_route('login');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::resource('/tenants', TenantController::class)->except(['show']);;
    Route::resource('/porters', controller: PorterController::class)->except(['show']);
    Route::resource('/delivery-points', DeliveryPointController::class);
    Route::get('/activities', action: [ActivityController::class, 'index'])->name('activity.activity');
    Route::get('/tenants/trashed', [TenantController::class, 'trashed'])->name('tenants.trashed');
    Route::post('/tenants/{id}/restore', [TenantController::class, 'restore'])->name('tenants.restore');
    Route::get('/porters/trashed', [PorterController::class, 'trashed'])->name('porters.trashed');
    Route::post('/porters/{id}/restore', [PorterController::class, 'restore'])->name('porters.restore');
    
});

Route::patch('/dashboard/delivery-points/{id}/toggle-status', [DeliveryPointController::class, 'toggleStatus'])
    ->name('dashboard.delivery-points.toggle-status');
