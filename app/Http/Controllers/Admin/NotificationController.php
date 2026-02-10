<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications page
     */
    public function index()
    {
        $notifications = TicketNotification::with('ticket')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Get notifications for the bell dropdown (AJAX)
     */
    public function dropdown()
    {
        $notifications = TicketNotification::with('ticket')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $unreadCount = TicketNotification::unread()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        $notification = TicketNotification::findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        TicketNotification::unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}
