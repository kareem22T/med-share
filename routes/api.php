<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;

// Users endpoints
Route::post("/user/register", [AuthController::class, "register"]);
Route::get('/user/ask-email-verfication-code', [AuthController::class, "askEmailCode"])->middleware('auth:sanctum');
Route::post('/user/verify-email', [AuthController::class, "verifyEmail"])->middleware('auth:sanctum');
Route::post('/user/change-password', [AuthController::class, "changePassword"])->middleware('auth:sanctum');
Route::post('/user/ask-for-forgot-password-email-code', [AuthController::class, "askEmailCodeForgot"]);
Route::post('/user/forgot-password', [AuthController::class, "forgetPassword"]);
Route::get('/user/get', [AuthController::class, "getUser"])->middleware('auth:sanctum');
Route::post('/user/login', [AuthController::class, "login"]);
Route::post('/user/update', [AuthController::class, "update"])->middleware('auth:sanctum');
Route::get('/user/logout', [AuthController::class, "logout"])->middleware('auth:sanctum');
