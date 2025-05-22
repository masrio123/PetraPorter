<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\DeliveryPointController as ApiDeliveryPointController;
use App\Http\Controllers\Api\ProductController as ApiProductController; 


Route::get('/products', [ApiProductController::class,'fetchAllProducts']);
Route::get('/products/{id}', [ApiProductController::class,'getProductByTenant']);
Route::put('/products/toggle/{id}', [ApiProductController::class,'toggleAvailability']);
Route::post('/products/store', [ApiProductController::class, 'storeProduct']);
Route::put('products/{id}/edit', [ApiProductController::class, 'updateProduct']);
Route::delete('products/{id}/delete', [ApiProductController::class, 'deleteProduct']);


Route::get('/delivery-points', [ApiDeliveryPointController::class, 'fetchDeliveryPoint']);
Route::post('/delivery-points/store', [ApiDeliveryPointController::class, 'store']);
Route::put('/delivery-points/{id}/edit', [ApiDeliveryPointController::class, 'edit']);
Route::delete('/delivery-points/{id}/delete', [ApiDeliveryPointController::class, 'destroy']);



// Route::get('/categories', [CategoryController::class, 'fetchCategories']);


// Route::get('/products', [ProductController::class, 'fetchAllProducts']);


// Route::get('/tenants', [TenantController::class, 'index']);
// Route::post('/tenants/add', [TenantController::class, 'store']);
// Route::get('/tenants/show/{id}', [TenantController::class, 'show']);
// Route::put('/tenants/edit/{id}', [TenantController::class, 'update']);
// Route::delete('/tenants/delete/{id}', [TenantController::class, 'destroy']);
// Route::resource('tenants', TenantController::class);

// Route::get('/admin', [TenantController::class, 'viewTable'])->name(name: 'tenants.admin');
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::get('/base', function () {
//     return view('layouts/app');
// });
