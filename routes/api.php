<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\Api\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\Api\TenantController as ApiTenantController;

use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\CartItemController as ApiCartItemController;
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
Route::post('/cart/add', [ApiCartController::class, 'addProductToCart']);
Route::get('/carts/{cartId}', [ApiCartItemController::class, 'showCart']);
Route::post('/cart/{cart}/checkout', [ApiCartController::class, 'checkout']); // checkout cart

// CART ITEMS
Route::get('/carts/{cartId}', [ApiCartItemController::class, 'showCart']);
Route::delete('/cart-items/{id}', [ApiCartItemController::class, 'deleteItem']);

//Tenants
Route::get('/tenants', [ApiTenantController::class, 'index']);
Route::post('/tenants', [ApiTenantController::class, 'store']);
Route::get('/tenants/{id}', [ApiTenantController::class, 'show']);
Route::put('/tenants/{id}', [ApiTenantController::class, 'update']);
Route::delete('/tenants/{id}', [ApiTenantController::class, 'destroy']);
Route::patch('/tenants/{id}/toggle-is-open', [ApiTenantController::class, 'toggleIsOpen']);

//Customer
Route::prefix('customers')->group(function () {
    Route::get('/', [ApiCustomerController::class, 'index']);         // GET semua customer
    Route::post('/', [ApiCustomerController::class, 'store']);        // POST tambah customer
    Route::get('/{id}', [ApiCustomerController::class, 'show']);      // GET detail customer
    Route::put('/{id}', [ApiCustomerController::class, 'update']);    // PUT update customer
    Route::delete('/{id}', [ApiCustomerController::class, 'destroy']); // DELETE customer
});


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
