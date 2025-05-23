<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TenantController as ApiTenantController;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\Api\CartItemController as ApiCartItemController;

use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\DeliveryPointController as ApiDeliveryPointController;


Route::get('/products', [ApiProductController::class, 'fetchAllProducts']);
Route::get('/products/{id}', [ApiProductController::class, 'getProductByTenant']);
Route::put('/products/toggle/{id}', [ApiProductController::class, 'toggleAvailability']);
Route::post('/products/store', [ApiProductController::class, 'storeProduct']);
Route::put('products/{id}/edit', [ApiProductController::class, 'updateProduct']);
Route::delete('products/{id}/delete', [ApiProductController::class, 'deleteProduct']);


Route::get('/delivery-points', [ApiDeliveryPointController::class, 'fetchDeliveryPoint']);
Route::post('/delivery-points/store', [ApiDeliveryPointController::class, 'store']);
Route::put('/delivery-points/{id}/edit', [ApiDeliveryPointController::class, 'edit']);
Route::delete('/delivery-points/{id}/delete', [ApiDeliveryPointController::class, 'destroy']);


// CART
Route::get('/cart', [ApiCartController::class, 'getOrCreateCart']); // dapetin cart aktif
Route::post('/cart/{cart}/checkout', [ApiCartController::class, 'checkout']); // checkout cart

// CART ITEMS
Route::post('/cart-items', [ApiCartItemController::class, 'store']); // tambah item
Route::put('/cart-items/{id}', [ApiCartItemController::class, 'update']); // ubah qty
Route::delete('/cart-items/{id}', [ApiCartItemController::class, 'destroy']); // hapus item

Route::get('/tenants', [ApiTenantController::class, 'index']);
Route::post('/tenants', [ApiTenantController::class, 'store']);
Route::get('/tenants/{id}', [ApiTenantController::class, 'show']);
Route::put('/tenants/{id}', [ApiTenantController::class, 'update']);
Route::delete('/tenants/{id}', [ApiTenantController::class, 'destroy']);
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
