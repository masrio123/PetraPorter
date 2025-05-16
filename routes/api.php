<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;

Route::get('/categories', [CategoryController::class, 'fetchCategories']);


Route::get('/products', [ProductController::class, 'fetchAllProducts']);
Route::post('/products/add', action: [ProductController::class, 'storeProduct']);
Route::put('/products/{id}/toggle', [ProductController::class, 'toggleAvailability']);
Route::put('products/{id}/edit', [ProductController::class, 'updateProduct']);
Route::delete('products/{id}/delete', [ProductController::class, 'deleteProduct']);


Route::get('/tenants', [TenantController::class, 'index']);
Route::post('/tenants/add', [TenantController::class, 'store']);
Route::get('/tenants/show/{id}', [TenantController::class, 'show']);
Route::put('/tenants/edit/{id}', [TenantController::class, 'update']);
Route::delete('/tenants/delete/{id}', [TenantController::class, 'destroy']);
Route::resource('tenants', TenantController::class);

Route::get('/admin', [TenantController::class, 'viewTable'])->name(name: 'tenants.admin');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/base', function () {
    return view('layouts/app');
});
