<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UpdateSubscription;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\PageController;

Route::get('/sites', [SiteController::class, 'index'])->middleware('secret.key');
Route::get('/patterns', [PatternController::class, 'index'])->middleware('secret.key');
Route::get('/pages', [PageController::class, 'index'])->middleware('secret.key');

// Create user from wordpress site
Route::post('/register', [AuthController::class, 'register'])->middleware('secret.key');
Route::post('/login', [AuthController::class, 'login'])->middleware('secret.key');;
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('secret.key');

// Update subscription for the user
Route::post('/update', [UpdateSubscription::class, 'update'])->middleware('secret.key');
