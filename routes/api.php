<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\UpdateSubscription;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\PageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sites', [SiteController::class, 'index']);
    Route::get('/patterns', [PatternController::class, 'index']);
    Route::get('/pages', [PageController::class, 'index']);

    // Create user from wordpress site
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Update subscription
    Route::post('/update', [UpdateSubscription::class, 'update']);

    // Import handler
    Route::post('/import/pattern', [ImportController::class, 'pattern']);
    Route::post('/import/page', [ImportController::class, 'page']);
});
