<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\LandmarkController;
use App\Http\Controllers\Admin\CustomerServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
});

// Protected routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', function () {
        $totalLandmarks = \App\Models\Landmark::count();
        $totalRoutes = \App\Models\JeepneyRoute::count();
        $activeUsers = \App\Models\User::count();
        $pendingRequests = 0; // Placeholder
        $recentActivities = \App\Models\ActivityLog::latest()->paginate(5);
        
        return view('admin.dashboard', compact(
            'totalLandmarks',
            'totalRoutes',
            'activeUsers',
            'pendingRequests',
            'recentActivities'
        ));
    })->name('dashboard');
    
    // Landmarks Management
    Route::prefix('landmarks')->name('admin.landmarks.')->group(function () {
        Route::get('/', [LandmarkController::class, 'index'])->name('index');
        Route::get('/create', [LandmarkController::class, 'create'])->name('create');
        Route::post('/', [LandmarkController::class, 'store'])->name('store');
        Route::get('/{landmark}/edit', [LandmarkController::class, 'edit'])->name('edit');
        Route::put('/{landmark}', [LandmarkController::class, 'update'])->name('update');
        Route::delete('/{landmark}', [LandmarkController::class, 'destroy'])->name('destroy');
    });
    
    // Routes Management
    Route::prefix('routes')->name('admin.routes.')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('index');
        Route::get('/create', [RouteController::class, 'create'])->name('create');
        Route::post('/', [RouteController::class, 'store'])->name('store');
        Route::get('/{route}/edit', [RouteController::class, 'edit'])->name('edit');
        Route::put('/{route}', [RouteController::class, 'update'])->name('update');
        Route::delete('/{route}', [RouteController::class, 'destroy'])->name('destroy');
        Route::post('/{route}/toggle-status', [RouteController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{route}/show', [RouteController::class, 'show'])->name('show');
    });
    
    // Customer Service
    Route::prefix('customer-service')->name('customer-service.')->group(function () {
        Route::get('/', [CustomerServiceController::class, 'index'])->name('index');
        Route::get('/{id}', [CustomerServiceController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [CustomerServiceController::class, 'reply'])->name('reply');
        Route::post('/{id}/status', [CustomerServiceController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [CustomerServiceController::class, 'archive'])->name('archive');
    });
});
