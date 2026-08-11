<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = SupportTicket::with(['user', 'messages' => fn ($q) => $q->latest()->take(1)])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        $tickets = $query->orderByRaw("FIELD(status, 'open', 'in_progress', 'resolved', 'closed')")
            ->orderByDesc('updated_at')
            ->paginate(15)->withQueryString();

        $counts = [
            'open' => SupportTicket::where('gym_id', $gymId)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('gym_id', $gymId)->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('gym_id', $gymId)->where('status', 'resolved')->count(),
        ];

        return view('support.index', compact('tickets', 'counts'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'messages.user']);

        return view('support.show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless(auth()->user()->hasPermission('support.reply'), 403);

        $data = $request->validate([
            'message' => ['required', 'string'],
        ]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        audit_log('support.replied', 'support', $ticket->id, "Replied to support ticket #{$ticket->id}");

        return back()->with('success', 'Reply sent.');
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        abort_unless(auth()->user()->hasPermission('support.reply'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket->update(['status' => $data['status']]);

        return back()->with('success', 'Ticket status updated.');
    }
}
