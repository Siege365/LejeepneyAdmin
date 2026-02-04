<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TicketNotification;
use Illuminate\Http\Request;

class TicketNotificationController extends Controller
{
    /**
     * Get all notifications for a user (by email or auth)
     * GET /api/v1/support/notifications
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Determine email to fetch notifications for
        if ($user) {
            $email = $user->email;
        } elseif ($request->filled('email')) {
            $email = $request->email;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        $query = TicketNotification::forEmail($email)
            ->with('ticket:id,subject,status')
            ->recent($request->days ?? 30);

        // Filter by read status
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        $notifications = $query->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => TicketNotification::forEmail($email)->unread()->count()
            ]
        ]);
    }

    /**
     * Mark notification(s) as read
     * PUT /api/v1/support/notifications/{id}/read
     * PUT /api/v1/support/notifications/mark-all-read
     */
    public function markAsRead(Request $request, $id = null)
    {
        $user = $request->user();
        $email = $user ? $user->email : $request->email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        if ($id) {
            // Mark single notification as read
            $notification = TicketNotification::forEmail($email)->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } else {
            // Mark all notifications as read
            $updated = TicketNotification::forEmail($email)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} notification(s) marked as read",
                'updated_count' => $updated
            ]);
        }
    }

    /**
     * Get unread notification count
     * GET /api/v1/support/notifications/unread-count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $email = $user ? $user->email : $request->email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        $count = TicketNotification::forEmail($email)->unread()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Delete a notification
     * DELETE /api/v1/support/notifications/{id}
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $email = $user ? $user->email : $request->email;

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        $notification = TicketNotification::forEmail($email)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }
}
