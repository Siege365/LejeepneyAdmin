<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\RouteController;
use App\Http\Controllers\Admin\LandmarkController;
use App\Http\Controllers\Admin\CustomerServiceController;
use App\Http\Controllers\Admin\AccountSettingsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
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
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    
    // Password Reset Routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])->name('password.update')->middleware('throttle:3,1');
});

// Protected routes (only accessible when logged in as admin)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Admin Registration (only admins can create new admins)
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Dashboard
    Route::get('/dashboard', function () {
        $totalLandmarks = \App\Models\Landmark::count();
        $totalRoutes = \App\Models\JeepneyRoute::count();
        $activeUsers = \App\Models\User::count();
        $pendingRequests = \App\Models\SupportTicket::where('status', 'pending')->count();
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
        Route::post('/batch-delete', [LandmarkController::class, 'batchDelete'])->name('batch-delete');
    });
    
    // Routes Management
    Route::prefix('routes')->name('admin.routes.')->group(function () {
        Route::get('/', [RouteController::class, 'index'])->name('index');
        Route::get('/create', [RouteController::class, 'create'])->name('create');
        Route::post('/', [RouteController::class, 'store'])->name('store');
        Route::get('/{route}/edit', [RouteController::class, 'edit'])->name('edit');
        Route::put('/{route}', [RouteController::class, 'update'])->name('update');
        Route::delete('/{route}', [RouteController::class, 'destroy'])->name('destroy');
        Route::post('/batch-delete', [RouteController::class, 'batchDelete'])->name('batch-delete');
        Route::post('/{route}/toggle-status', [RouteController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{route}/show', [RouteController::class, 'show'])->name('show');
    });
    
    // Customer Service
    Route::prefix('customer-service')->name('admin.customer-service.')->group(function () {
        Route::get('/', [CustomerServiceController::class, 'index'])->name('index');
        Route::post('/bulk-action', [CustomerServiceController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{id}', [CustomerServiceController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [CustomerServiceController::class, 'reply'])->name('reply');
        Route::post('/{id}/status', [CustomerServiceController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{id}/flag', [CustomerServiceController::class, 'toggleFlag'])->name('toggleFlag');
        Route::post('/{id}/archive', [CustomerServiceController::class, 'archive'])->name('archive');
        Route::post('/{id}/restore', [CustomerServiceController::class, 'restore'])->name('restore');
    });

    // Account Settings
    Route::prefix('account')->name('admin.account.')->group(function () {
        Route::get('/settings', [AccountSettingsController::class, 'index'])->name('settings');
        Route::put('/profile', [AccountSettingsController::class, 'updateProfile'])->name('update-profile');
        Route::put('/password', [AccountSettingsController::class, 'updatePassword'])->name('update-password');
        Route::delete('/delete', [AccountSettingsController::class, 'deleteAccount'])->name('delete');
    });

    // Notifications
    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/dropdown', [NotificationController::class, 'dropdown'])->name('dropdown');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // Audit Trail
    Route::prefix('audit-trail')->name('admin.audit-trail.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AuditTrailController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\AuditTrailController::class, 'show'])->name('show');
        Route::get('/export/csv', [\App\Http\Controllers\Admin\AuditTrailController::class, 'export'])->name('export');
    });

    // Settings
    Route::prefix('settings')->name('admin.settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
    });
});
