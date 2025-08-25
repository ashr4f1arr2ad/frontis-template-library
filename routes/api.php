<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UpdateSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Create user from wordpress site
Route::post('/register', [AuthController::class, 'register'])->middleware('secret.key');
Route::post('/login', [AuthController::class, 'login'])->middleware('secret.key');;
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('secret.key');

// Update subscription for the user
Route::post('/update', [UpdateSubscription::class, 'update'])->middleware('secret.key');
