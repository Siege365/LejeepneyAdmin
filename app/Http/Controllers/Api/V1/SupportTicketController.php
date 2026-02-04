<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\TicketNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    /**
     * Create a new support ticket (Mobile → Admin)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'type' => 'nullable|in:general,technical,billing,feedback,other',
            'priority' => 'nullable|in:low,medium,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = SupportTicket::create([
            'user_id' => $request->user()?->id,
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'subject' => strip_tags($request->subject),
            'message' => strip_tags($request->message),
            'type' => $request->type ?? 'general',
            'priority' => $request->priority ?? 'medium',
            'status' => 'pending',
            'is_flagged' => false,
            'is_archived' => false
        ]);

        // Create notification for ticket creation
        TicketNotification::createNotification(
            $ticket->id,
            $ticket->email,
            'created',
            'Support Ticket Created',
            "Your support ticket '{$ticket->subject}' has been submitted and is pending review.",
            ['ticket_id' => $ticket->id, 'subject' => $ticket->subject]
        );

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => [
                'ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at->toIso8601String()
            ]
        ], 201);
    }

    /**
     * Get all tickets for the authenticated user (Mobile ← Admin)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = SupportTicket::query();

        // If user is authenticated, get their tickets by user_id or email
        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            });
        } elseif ($request->filled('email')) {
            // Allow fetching by email for guests (with rate limiting)
            $query->where('email', $request->email);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Exclude archived by default
        $query->active();

        $tickets = $query->orderByDesc('created_at')
                         ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total()
            ]
        ]);
    }

    /**
     * Get a specific ticket with replies (Mobile ← Admin)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $query = SupportTicket::with(['replies' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        // Verify ownership
        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            });
        } elseif ($request->filled('email')) {
            $query->where('email', $request->email);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        $ticket = $query->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found or access denied'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'email' => $ticket->email,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'type' => $ticket->type,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'status_label' => ucfirst(str_replace('-', ' ', $ticket->status)),
                'created_at' => $ticket->created_at->toIso8601String(),
                'updated_at' => $ticket->updated_at->toIso8601String(),
                'replies' => $ticket->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'message' => $reply->message,
                        'admin_name' => $reply->admin_name,
                        'created_at' => $reply->created_at->toIso8601String()
                    ];
                })
            ]
        ]);
    }

    /**
     * Add a follow-up message to an existing ticket (Mobile → Admin)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:2000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        $query = SupportTicket::query();

        // Verify ownership
        if ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('email', $user->email);
            });
        } elseif ($request->filled('email')) {
            $query->where('email', $request->email);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required or email must be provided'
            ], 401);
        }

        $ticket = $query->active()->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found, archived, or access denied'
            ], 404);
        }

        // Append message to the ticket
        $ticket->update([
            'message' => $ticket->message . "\n\n---\n**Follow-up ({$ticket->created_at->format('M d, Y H:i')}):**\n" . strip_tags($request->message),
            'status' => 'pending' // Reopen if resolved
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up message added successfully',
            'data' => [
                'ticket_id' => $ticket->id,
                'status' => $ticket->status
            ]
        ]);
    }

    /**
     * Get ticket statistics for the user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $baseQuery = SupportTicket::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('email', $user->email);
        })->active();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->pending()->count(),
                'in_progress' => (clone $baseQuery)->inProgress()->count(),
                'resolved' => (clone $baseQuery)->resolved()->count()
            ]
        ]);
    }
}
