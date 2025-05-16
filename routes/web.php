<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix'=> 'auth'], function () {
    Route::get('/login', [AuthController::class,'index'])->name('login');
});

Route::group(['prefix'=> 'dashboard', 'as'=>'dashboard.'], function () {
    Route::get('/', [DashboardController::class,'index'])->name('index');
    Route::resource('/tenants', TenantController::class);
});


