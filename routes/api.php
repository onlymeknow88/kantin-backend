<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\ProductCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('logout', [UserController::class, 'logout']);

    Route::get('transactions', [TransactionController::class, 'all']);
    Route::get('transactions-status', [TransactionController::class, 'getOrderByStatus']);
    Route::get('transactions-history', [TransactionController::class, 'getHistoryOrder']);
    Route::get('report-order', [TransactionController::class, 'getReportOrderByDate']);
    Route::post('checkout', [TransactionController::class, 'checkout']);
    Route::post('cancel-order', [TransactionController::class, 'cancelOrder']);
    Route::post('confirm-order', [TransactionController::class, 'confirmOrder']);
});


Route::post('add-products', [ProductController::class, 'addProduct']);
Route::post('delete-products', [ProductController::class, 'deleteProduct']);
Route::get('products', [ProductController::class, 'all']);
Route::get('products-category', [ProductController::class, 'getProductByCategory']);
Route::get('products-search', [ProductController::class, 'getSearchProductByCategory']);
Route::get('categories', [ProductCategoryController::class, 'all']);

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);
