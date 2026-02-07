<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share notification count with admin layout
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            $unreadCount = 0;
            if (auth()->check()) {
                $unreadCount = \App\Models\TicketNotification::unread()->count();
                $notifications = \App\Models\TicketNotification::with('ticket')
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get();
            }
            $view->with('notificationCount', $unreadCount)
                 ->with('headerNotifications', $notifications ?? collect());
        });
    }
}
