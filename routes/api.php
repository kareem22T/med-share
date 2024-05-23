<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\ProductsController;
use App\Http\Controllers\User\WishlistController;

// Users endpoints
Route::post("/user/register", [AuthController::class, "register"]);
Route::get('/user/ask-email-verfication-code', [AuthController::class, "askEmailCode"])->middleware('auth:sanctum');
Route::post('/user/verify-email', [AuthController::class, "verifyEmail"])->middleware('auth:sanctum');
Route::post('/user/change-password', [AuthController::class, "changePassword"])->middleware('auth:sanctum');
Route::post('/user/ask-for-forgot-password-email-code', [AuthController::class, "askEmailCodeForgot"]);
Route::post('/user/forgot-password', [AuthController::class, "forgetPassword"]);
Route::post('/user/forgot-password-check-code', [AuthController::class, "forgetPasswordCheckCode"]);
Route::get('/user/get', [AuthController::class, "getUser"])->middleware('auth:sanctum');
Route::post('/user/login', [AuthController::class, "login"]);
Route::post('/user/update', [AuthController::class, "update"])->middleware('auth:sanctum');
Route::get('/user/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');
Route::get('/user/delete-all', [AuthController::class, "deleteAllUsers"]);

// Products endpoints
Route::post('/products/create', [ProductsController::class, "create"])->middleware(['auth:sanctum', App\Http\Middleware\IsUserVerified::class]);
Route::post('/products/update', [ProductsController::class, "update"])->middleware('auth:sanctum');
Route::post('/products/delete', [ProductsController::class, "delete"])->middleware('auth:sanctum');
Route::get("/products/get-all-products", [ProductsController::class, "getAll"]);
Route::get("/products/get-products-pagination", [ProductsController::class, "get"]);
Route::get("/products/get-products-search", [ProductsController::class, "search"]);
Route::get("/products/get-products-per-user-all", [ProductsController::class, "getProductsPerUserAll"]);
Route::get("/products/get-products-per-user-pagination", [ProductsController::class, "getProductsPerUserPagination"]);
Route::get("/products/get-product-by-id", [ProductsController::class, "getProduct"]);

// Cart endpoints
Route::post("/cart/put-product", [CartController::class, "addProductToCart"])->middleware('auth:sanctum');
Route::post("/cart/remove-product", [CartController::class, "removeProductFromCart"])->middleware('auth:sanctum');
Route::post("/cart/update-product-quantity", [CartController::class, "updateProductQuantityAtCart"])->middleware('auth:sanctum');
Route::get("/cart/get", [CartController::class, "getCartDetails"])->middleware('auth:sanctum');

// Wishlist endpoints
Route::post("/wishlist/add-or-remove-product", [WishlistController::class, "addOrDeleteProductWishlist"])->middleware('auth:sanctum');
Route::get("/wishlist/get", [WishlistController::class, "getWishlist"])->middleware('auth:sanctum');
