<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\TicketNotification;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerServiceController extends Controller
{
    /**
     * Display a listing of support tickets with filtering
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user', 'admin']);

        // Search by name, email, subject, message, or ticket ID
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->filterByStatus($request->status);
        }

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->filterByType($request->type);
        }

        // Filter by priority
        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->filterByPriority($request->priority);
        }

        // Filter flagged only
        if ($request->boolean('flagged')) {
            $query->flagged();
        }

        // Filter archived/active
        if ($request->filled('archived')) {
            if ($request->archived === 'archived') {
                $query->archived();
            } elseif ($request->archived === 'active') {
                $query->active();
            }
        } else {
            // Default: show only active tickets
            $query->active();
        }

        // Sorting
        $sortBy = $request->get('sort', 'newest');
        
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'id_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'id_desc':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        $tickets = $query->paginate(10)
                         ->withQueryString();

        // Get counts for the sidebar/stats
        $stats = [
            'total' => SupportTicket::active()->count(),
            'pending' => SupportTicket::active()->pending()->count(),
            'in_progress' => SupportTicket::active()->inProgress()->count(),
            'resolved' => SupportTicket::active()->resolved()->count(),
            'flagged' => SupportTicket::active()->flagged()->count(),
            'archived' => SupportTicket::archived()->count(),
        ];
        
        return view('admin.customer-service.index', compact('tickets', 'stats'));
    }

    /**
     * Display the specified ticket with replies
     */
    public function show($id)
    {
        $ticket = SupportTicket::with(['replies.admin', 'user', 'admin'])->findOrFail($id);
        $replies = $ticket->replies()->orderBy('created_at', 'asc')->get();
        
        return view('admin.customer-service.show', compact('ticket', 'replies'));
    }

    /**
     * Store a reply to a ticket
     */
    public function reply(Request $request, $id)
    {
        \Log::info('Reply attempt started', [
            'ticket_id' => $id,
            'has_message' => $request->has('message'),
            'has_status' => $request->has('status'),
            'has_send_email' => $request->has('send_email'),
            'all_data' => $request->all()
        ]);

        try {
            $request->validate([
                'message' => 'required|string|min:10|max:5000',
                'status' => 'required|in:pending,in-progress,resolved',
            ]);
        } catch (\Exception $e) {
            \Log::error('Validation failed', ['error' => $e->getMessage()]);
            return back()->withErrors($e->validator->errors())->withInput();
        }

        $ticket = SupportTicket::findOrFail($id);
        $admin = Auth::user();

        \Log::info('Creating reply', [
            'ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'admin_name' => $admin->name
        ]);

        DB::beginTransaction();
        try {
            // Create the reply
            $reply = TicketReply::create([
                'support_ticket_id' => $ticket->id,
                'admin_id' => $admin->id,
                'message' => strip_tags($request->message, '<p><br><strong><em><ul><ol><li>'),
                'admin_name' => $admin->name,
                'email_sent' => $request->has('send_email') ? true : false
            ]);

            \Log::info('Reply created', ['reply_id' => $reply->id]);

            $oldStatus = $ticket->status;

            // Update ticket with admin who handled it
            $ticket->update([
                'status' => $request->status,
                'admin_id' => $admin->id
            ]);

            \Log::info('Ticket updated', ['new_status' => $request->status]);

            // Create notification for admin reply
            TicketNotification::createNotification(
                $ticket->id,
                $ticket->email,
                'admin_message',
                'New Reply from Support Team',
                "{$admin->name} replied to your ticket '{$ticket->subject}'",
                ['admin_name' => $admin->name, 'reply_preview' => substr(strip_tags($request->message), 0, 100)]
            );

            // Log activity
            ActivityLog::create([
                'action' => 'ticket_reply',
                'model_type' => 'SupportTicket',
                'model_id' => $ticket->id,
                'model_name' => $ticket->subject,
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'description' => "Replied to ticket #{$ticket->id}: {$ticket->subject}",
                'ip_address' => $request->ip()
            ]);

            if ($oldStatus !== $request->status) {
                // Create notification for status change
                TicketNotification::createNotification(
                    $ticket->id,
                    $ticket->email,
                    'status_changed',
                    'Ticket Status Updated',
                    "Your ticket status changed from {$oldStatus} to {$request->status}",
                    ['old_status' => $oldStatus, 'new_status' => $request->status]
                );

                ActivityLog::create([
                    'action' => 'ticket_status_change',
                    'model_type' => 'SupportTicket',
                    'model_id' => $ticket->id,
                    'model_name' => $ticket->subject,
                    'user_id' => $admin->id,
                    'user_name' => $admin->name,
                    'description' => "Changed ticket #{$ticket->id} status from {$oldStatus} to {$request->status}",
                    'ip_address' => $request->ip()
                ]);
            }

            DB::commit();
            
            \Log::info('Reply completed successfully');

            return redirect()
                ->route('admin.customer-service.show', $ticket->id)
                ->with('success', 'Reply sent successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Reply failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Failed to send reply: ' . $e->getMessage());
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in-progress,resolved'
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $oldStatus = $ticket->status;
        $admin = Auth::user();

        $ticket->update([
            'status' => $request->status,
            'admin_id' => $admin->id
        ]);

        // Create notification for status change
        if ($oldStatus !== $request->status) {
            TicketNotification::createNotification(
                $ticket->id,
                $ticket->email,
                $request->status === 'resolved' ? 'resolved' : 'status_changed',
                $request->status === 'resolved' ? 'Ticket Resolved' : 'Ticket Status Updated',
                "Your ticket '{$ticket->subject}' status changed from {$oldStatus} to {$request->status}",
                ['old_status' => $oldStatus, 'new_status' => $request->status, 'admin_name' => $admin->name]
            );
        }

        ActivityLog::create([
            'action' => 'ticket_status_change',
            'description' => "Changed ticket #{$ticket->id} status from {$oldStatus} to {$request->status}",
            'performed_by' => $admin->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()
            ->route('admin.customer-service.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully!');
    }

    /**
     * Toggle flag on a ticket
     */
    public function toggleFlag(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->toggleFlag();
        $admin = Auth::user();

        $action = $ticket->is_flagged ? 'flagged' : 'unflagged';
        
        ActivityLog::create([
            'action' => 'ticket_flag_toggle',
            'description' => ucfirst($action) . " ticket #{$ticket->id}: {$ticket->subject}",
            'performed_by' => $admin->name,
            'ip_address' => $request->ip()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'is_flagged' => $ticket->is_flagged,
                'message' => "Ticket {$action} successfully!"
            ]);
        }

        return back()->with('success', "Ticket {$action} successfully!");
    }

    /**
     * Archive a ticket
     */
    public function archive(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->archive();
        $admin = Auth::user();

        ActivityLog::create([
            'action' => 'ticket_archived',
            'description' => "Archived ticket #{$ticket->id}: {$ticket->subject}",
            'performed_by' => $admin->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()
            ->route('admin.customer-service.index')
            ->with('success', 'Ticket archived successfully!');
    }

    /**
     * Restore an archived ticket
     */
    public function restore(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->restore();
        $admin = Auth::user();

        ActivityLog::create([
            'action' => 'ticket_restored',
            'description' => "Restored ticket #{$ticket->id}: {$ticket->subject}",
            'performed_by' => $admin->name,
            'ip_address' => $request->ip()
        ]);

        return redirect()
            ->route('admin.customer-service.show', $ticket->id)
            ->with('success', 'Ticket restored successfully!');
    }

    /**
     * Bulk update tickets
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:support_tickets,id',
            'action' => 'required|in:archive,restore,flag,unflag,mark_resolved'
        ]);

        $ticketIds = $request->ticket_ids;
        $action = $request->action;
        $admin = Auth::user();
        $count = count($ticketIds);

        DB::beginTransaction();
        try {
            switch ($action) {
                case 'archive':
                    SupportTicket::whereIn('id', $ticketIds)->update([
                        'is_archived' => true,
                        'archived_at' => now()
                    ]);
                    $message = "{$count} ticket(s) archived successfully!";
                    break;

                case 'restore':
                    SupportTicket::whereIn('id', $ticketIds)->update([
                        'is_archived' => false,
                        'archived_at' => null
                    ]);
                    $message = "{$count} ticket(s) restored successfully!";
                    break;

                case 'flag':
                    SupportTicket::whereIn('id', $ticketIds)->update(['is_flagged' => true]);
                    $message = "{$count} ticket(s) flagged successfully!";
                    break;

                case 'unflag':
                    SupportTicket::whereIn('id', $ticketIds)->update(['is_flagged' => false]);
                    $message = "{$count} ticket(s) unflagged successfully!";
                    break;

                case 'mark_resolved':
                    SupportTicket::whereIn('id', $ticketIds)->update([
                        'status' => 'resolved',
                        'admin_id' => $admin->id
                    ]);
                    $message = "{$count} ticket(s) marked as resolved!";
                    break;
            }

            ActivityLog::create([
                'action' => 'ticket_bulk_' . $action,
                'description' => "Bulk {$action}: {$count} tickets",
                'performed_by' => $admin->name,
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Operation failed'], 500);
            }
            return back()->with('error', 'Operation failed. Please try again.');
        }
    }
}
