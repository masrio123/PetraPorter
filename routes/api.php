<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderHistoryController as ApiOrderHistoryController;
use App\Http\Controllers\Api\CartController as ApiCartController;

use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\TenantController as ApiTenantController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\CartItemController as ApiCartItemController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\CustomerController as ApiCustomerController;
use App\Http\Controllers\Api\OrderItemController as ApiOrderItemController;
use App\Http\Controllers\Api\DeliveryPointController as ApiDeliveryPointController;
use App\Http\Controllers\Api\TenantLocationController as ApiTenantLocationController;
use App\Http\Controllers\Api\PorterController as ApiPorterController;



Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

    //Product
    Route::get('/products', [ApiProductController::class, 'fetchAllProducts']);
    Route::get('/products/{id}/tenants-products', [ApiProductController::class, 'getProductsByTenantLocation']);
    Route::post('/products/store', [ApiProductController::class, 'storeProduct']);
    Route::put('products/{id}', [ApiProductController::class, 'updateProduct']);
    Route::delete('/products/{productId}', action: [ApiProductController::class, 'deleteProduct']);

    //Delivery Points
    Route::get('/delivery-points', [ApiDeliveryPointController::class, 'fetchDeliveryPoint']);
    Route::post('/delivery-points/store', [ApiDeliveryPointController::class, 'store']);
    Route::put('/delivery-points/{id}/edit', [ApiDeliveryPointController::class, 'edit']);
    Route::delete('/delivery-points/{id}/delete', [ApiDeliveryPointController::class, 'destroy']);

    //Cart
    Route::post('/cart', [ApiCartController::class, 'createCart']);
    Route::get('/cart/{id}', [ApiCartController::class, 'showCart']);
    Route::delete('/cart/{id}', [ApiCartController::class, 'deleteCart']);
    Route::post('/cart/{id}/checkout', action: [ApiCartController::class, 'checkoutCart']);
    Route::get('/allCarts/{tenantId}', [ApiCartController::class, 'getAllCarts']);

    //Cart Item
    Route::post('/cart-items', [ApiCartItemController::class, 'addItems']);
    Route::delete('/cart-items/{tenantId}/{productId}', action: [ApiCartItemController::class, 'deleteByTenantAndProduct']);

    //Tenants
    Route::get('/tenants', [ApiTenantController::class, 'index']);
    Route::post('/tenants', [ApiTenantController::class, 'store']);
    Route::get('/tenants/{id}', [ApiTenantController::class, 'show']);
    Route::put('/tenants/{id}', [ApiTenantController::class, 'update']);
    Route::delete('/tenants/{id}', [ApiTenantController::class, 'destroy']);
    Route::patch('/tenants/{id}/toggle-is-open', [ApiTenantController::class, 'toggleIsOpen']);
    Route::get('/location/{id}/tenants', [ApiTenantController::class, 'fetchTenantsByLocation']);
    Route::get('/tenants/{tenantId}/order-notifications', [ApiOrderItemController::class, 'getTenantOrderNotifications']);



    //Customer
    Route::prefix('customers')->group(function () {
        Route::get('/', [ApiCustomerController::class, 'index']);         // GET semua customer
        Route::post('/', [ApiCustomerController::class, 'store']);        // POST tambah customer
        Route::get('/{id}', [ApiCustomerController::class, 'show']);      // GET detail customer
        Route::put('/{id}', [ApiCustomerController::class, 'update']);    // PUT update customer
        Route::delete('/{id}', [ApiCustomerController::class, 'destroy']); // DELETE customer
    });

    Route::get('/categories', [ApiCategoryController::class, 'fetchCategories']);
    Route::get('/tenants/{id}/categories-with-menus', [ApiCategoryController::class, 'fetchCategoriesWithMenusByTenant']);

    Route::prefix('orders')->group(function () {
        Route::get('/', [ApiOrderController::class, 'showAll']);
        Route::get('{id}', [ApiOrderController::class, 'fetchOrderById']);
        Route::get('search-porter/{id}', [ApiOrderItemController::class, 'searchPorter']);
        Route::post('/cancel/{id}', [ApiOrderItemController::class, 'cancelOrder']);
        Route::get('/canceled', [ApiOrderItemController::class, 'showCanceledOrders']);
        Route::post('/rate-porter/{orderId}', [ApiOrderItemController::class, 'ratePorter']);
        Route::get('/activity/{customerId}', [ApiOrderController::class, 'getCustomerActivity']);
    });

    Route::get('/delivery-points', [ApiOrderController::class, 'getDeliverypoints']);

    //Tenant Location
    Route::get('/tenant-locations', [ApiTenantLocationController::class, 'index']);

    Route::get('/porters/{porterId}/orderList', [ApiPorterController::class, 'orderList']);
    Route::post('/porters/{orderId}/accept', [ApiPorterController::class, 'acceptOrder']);
    Route::post('/porters/{orderId}/reject', [ApiPorterController::class, 'rejectOrder']);
    Route::post('/porters/{porterId}/accepted-orders', [ApiPorterController::class, 'viewAcceptedOrders']);
    Route::put('/porters/{orderId}/deliver', [ApiPorterController::class, 'deliverOrder']);
    Route::post('/porters/{orderId}/finish', [ApiPorterController::class, 'finishOrder']);
    Route::post('/porters/{porterId}/workSummary', [ApiPorterController::class, 'workSummary']);
    Route::post('/porters/{porterId}', [ApiPorterController::class, 'getPorterActivity']);
    Route::post('/porters/profile/{porterId}', [ApiPorterController::class, 'profileApi']);
    Route::post('/porter/{id}/toggle-is-open', [ApiPorterController::class, 'getToggleIsOpen']);
    Route::put('/porter/{id}/toggle-is-open', [ApiPorterController::class, 'UpdateToggleIsOpen']);

    // di routes/api.php
    Route::get('/orders/summary/finished', [ApiOrderController::class, 'getFinishedOrdersSummary']);
    
});
