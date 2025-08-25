<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\PageController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/sites', [SiteController::class, 'index']);
Route::get('/patterns', [PatternController::class, 'index']);
Route::get('/pages', [PageController::class, 'index']);

// Create user from wordpress site
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
