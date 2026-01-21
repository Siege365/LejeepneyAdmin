<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    /**
     * Display a listing of support tickets
     */
    public function index()
    {
        $tickets = SupportTicket::latest()->paginate(10);
        
        return view('admin.customer-service.index', compact('tickets'));
    }

    /**
     * Display the specified ticket
     */
    public function show($id)
    {
        $ticket = SupportTicket::with('replies')->findOrFail($id);
        $replies = $ticket->replies;
        
        return view('admin.customer-service.show', compact('ticket', 'replies'));
    }

    /**
     * Store a reply to a ticket
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|min:10',
            'status' => 'required|in:pending,in-progress,resolved',
            'send_email' => 'nullable|boolean'
        ]);

        $ticket = SupportTicket::findOrFail($id);

        // Create the reply
        $reply = TicketReply::create([
            'support_ticket_id' => $ticket->id,
            'message' => $request->message,
            'admin_name' => 'Admin Support', // You can get from auth()->user() later
            'email_sent' => $request->boolean('send_email')
        ]);

        // Update ticket status
        $ticket->update([
            'status' => $request->status
        ]);

        // TODO: Send email notification if requested
        if ($request->boolean('send_email')) {
            // Mail::to($ticket->customer_email)->send(new TicketReplyMail($ticket, $reply));
        }

        return redirect()
            ->route('customer-service.show', $ticket->id)
            ->with('success', 'Reply sent successfully!');
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
        $ticket->update(['status' => $request->status]);

        return redirect()
            ->route('customer-service.show', $ticket->id)
            ->with('success', 'Ticket status updated successfully!');
    }

    /**
     * Archive a ticket (soft delete or mark as archived)
     */
    public function archive($id)
    {
        $ticket = SupportTicket::findOrFail($id);
        $ticket->delete();

        return redirect()
            ->route('customer-service.index')
            ->with('success', 'Ticket archived successfully!');
    }
}
