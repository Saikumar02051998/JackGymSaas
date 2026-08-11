<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $tickets = $user->supportTickets()->orderByDesc('created_at')->paginate(15);

        return view('client.support', compact('tickets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create(array_merge($data, [
            'gym_id' => current_gym()?->id,
            'user_id' => auth()->id(),
            'status' => 'open',
            'priority' => $data['priority'] ?? 'medium',
        ]));

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        audit_log('support.ticket_created', 'support', $ticket->id, 'Support ticket created');

        return back()->with('success', 'Support ticket submitted.');
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        $ticket->load(['messages.user']);

        return view('client.support-show', compact('ticket'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === auth()->id(), 403);

        $data = $request->validate(['message' => ['required', 'string']]);

        SupportTicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        $ticket->update(['status' => 'open']);

        return back()->with('success', 'Reply sent.');
    }
}
