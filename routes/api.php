<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PatternController;
use App\Http\Controllers\Api\SiteController;
use App\Http\Controllers\Api\UpdateSubscription;
use Illuminate\Support\Facades\Route;

Route::middleware('secret.key')->group(function () {
    Route::post('/sites', [SiteController::class, 'index']);
    Route::post('/patterns', [PatternController::class, 'index']);
    Route::post('/pages', [PageController::class, 'index']);

    // Create user from wordpress site
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Update subscription
    Route::post('/update', [UpdateSubscription::class, 'update']);

    // Import handler
    Route::post('/import/pattern', [ImportController::class, 'pattern']);
    Route::post('/import/page', [ImportController::class, 'page']);
    Route::post('/import/site', [ImportController::class, 'site']);
});
