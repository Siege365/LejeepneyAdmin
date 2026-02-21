<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\TicketNotification;
use App\Models\ActivityLog;
use App\Mail\TicketReplyMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
            $search = str_replace(['%', '_'], ['\%', '\_'], trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Combined filter
        $filter = $request->get('filter', 'all');

        // Type filters
        $typeFilters = ['general', 'technical', 'billing', 'feedback', 'other'];
        if (in_array($filter, $typeFilters)) {
            $query->filterByType($filter);
        }

        // Priority filters
        $priorityFilters = ['urgent', 'high', 'medium', 'low'];
        if (in_array($filter, $priorityFilters)) {
            $query->filterByPriority($filter);
        }

        // Status filters
        $statusFilters = ['pending', 'in-progress', 'resolved', 'cancelled'];
        if (in_array($filter, $statusFilters)) {
            $query->filterByStatus($filter);
        }

        // Flagged filter
        if ($filter === 'flagged') {
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

        // Sorting — default oldest to newest by ticket number
        switch ($filter) {
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            default:
                $query->orderBy('id', 'asc');
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
        Log::info('Reply attempt started', [
            'ticket_id' => $id,
            'has_message' => $request->has('message'),
            'has_status' => $request->has('status'),
            'has_send_email' => $request->has('send_email'),
        ]);

        try {
            $request->validate([
                'message' => 'required|string|min:10|max:5000',
                'status' => 'required|in:pending,in-progress,resolved,cancelled',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['error' => $e->getMessage()]);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            
            return back()->withErrors($e->validator->errors())->withInput();
        }

        $ticket = SupportTicket::findOrFail($id);
        $admin = Auth::user();

        Log::info('Creating reply', [
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
                'message' => strip_tags($request->message),
                'admin_name' => $admin->name,
                'email_sent' => $request->has('send_email') ? true : false
            ]);

            Log::info('Reply created', ['reply_id' => $reply->id]);

            $oldStatus = $ticket->status;

            // Update ticket with admin who handled it
            $ticket->update([
                'status' => $request->status,
                'admin_id' => $admin->id
            ]);

            Log::info('Ticket updated', ['new_status' => $request->status]);

            // Create notification for admin reply
            TicketNotification::createNotification(
                $ticket->id,
                $ticket->email,
                'admin_message',
                'New Reply from Support Team',
                "{$admin->name} replied to ticket '{$ticket->subject}'",
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
                'description' => "Replied to ticket '#{$ticket->id}: {$ticket->subject}'",
                'ip_address' => $request->ip()
            ]);

            if ($oldStatus !== $request->status) {
                // Create notification for status change
                TicketNotification::createNotification(
                    $ticket->id,
                    $ticket->email,
                    'status_changed',
                    'Ticket Status Updated',
                    "Ticket '{$ticket->subject}' status changed from {$oldStatus} to {$request->status}",
                    ['old_status' => $oldStatus, 'new_status' => $request->status]
                );

                ActivityLog::create([
                    'action' => 'ticket_status_change',
                    'model_type' => 'SupportTicket',
                    'model_id' => $ticket->id,
                    'model_name' => $ticket->subject,
                    'user_id' => $admin->id,
                    'user_name' => $admin->name,
                    'description' => "Changed status of ticket '#{$ticket->id}: {$ticket->subject}' from {$oldStatus} to {$request->status}",
                    'ip_address' => $request->ip()
                ]);
            }

            DB::commit();
            
            Log::info('Reply completed successfully');

            // Send email notification server-side if checkbox was checked
            if ($request->has('send_email') && $ticket->email) {
                try {
                    Mail::to($ticket->email)->send(
                        new TicketReplyMail($ticket, strip_tags($request->message), $admin->name)
                    );
                    Log::info('Ticket reply email sent', ['ticket_id' => $ticket->id, 'to' => $ticket->email]);
                } catch (\Exception $e) {
                    // Don't fail the request if email fails — reply was already saved
                    Log::warning('Failed to send ticket reply email', [
                        'ticket_id' => $ticket->id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Reply sent successfully!'
                ]);
            }

            return redirect()
                ->route('admin.customer-service.show', $ticket->id)
                ->with('success', 'Reply sent successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reply failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send reply. Please try again.'
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', 'Failed to send reply. Please try again.');
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in-progress,resolved,cancelled'
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
                "Ticket '{$ticket->subject}' status changed from {$oldStatus} to {$request->status}",
                ['old_status' => $oldStatus, 'new_status' => $request->status, 'admin_name' => $admin->name]
            );
        }

        ActivityLog::create([
            'action' => 'ticket_status_change',
            'model_type' => 'SupportTicket',
            'model_id' => $ticket->id,
            'model_name' => $ticket->subject,
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'description' => "Changed status of ticket '#{$ticket->id}: {$ticket->subject}' from {$oldStatus} to {$request->status}",
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
            'model_type' => 'SupportTicket',
            'model_id' => $ticket->id,
            'model_name' => $ticket->subject,
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'description' => ucfirst($action) . " ticket '#{$ticket->id}: {$ticket->subject}'",
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
            'model_type' => 'SupportTicket',
            'model_id' => $ticket->id,
            'model_name' => $ticket->subject,
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'description' => "Archived ticket '#{$ticket->id}: {$ticket->subject}'",
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
            'model_type' => 'SupportTicket',
            'model_id' => $ticket->id,
            'model_name' => $ticket->subject,
            'user_id' => $admin->id,
            'user_name' => $admin->name,
            'description' => "Restored ticket '#{$ticket->id}: {$ticket->subject}'",
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
                'model_type' => 'SupportTicket',
                'model_id' => 0,
                'model_name' => "Bulk {$action}",
                'user_id' => $admin->id,
                'user_name' => $admin->name,
                'description' => "Applied bulk action '{$action}' to {$count} ticket(s)",
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
