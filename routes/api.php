<?php

use App\Http\Controllers\Api\RouteApiController;
use App\Http\Controllers\Api\LandmarkApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Authentication Routes (Public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// API v1 Routes (Public - for Flutter App)
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    
    // Routes
    Route::get('/routes', [RouteApiController::class, 'index']);
    Route::get('/routes/paths', [RouteApiController::class, 'getAllPaths']);
    Route::get('/routes/{id}', [RouteApiController::class, 'show']);
    Route::post('/routes/find', [RouteApiController::class, 'findRoutes']);
    
    // Landmarks
    Route::get('/landmarks', [LandmarkApiController::class, 'index']);
    Route::get('/landmarks/featured', [LandmarkApiController::class, 'featured']);
    Route::get('/landmarks/category/{category}', [LandmarkApiController::class, 'byCategory']);
    Route::get('/landmarks/{id}', [LandmarkApiController::class, 'show']);
    Route::post('/landmarks/nearby', [LandmarkApiController::class, 'nearby']);
    
});
