<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Invoice::with(['client.user'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('client.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->orderByDesc('invoice_date')->paginate(15)->withQueryString();

        $summary = [
            'total' => (float) Invoice::where('gym_id', $gymId)->sum('grand_total'),
            'paid' => (float) Invoice::where('gym_id', $gymId)->where('status', 'paid')->sum('grand_total'),
            'pending' => (float) Invoice::where('gym_id', $gymId)->whereIn('status', ['issued', 'overdue'])->sum('grand_total'),
        ];

        return view('invoices.index', compact('invoices', 'summary'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client.user', 'items', 'payment', 'membership.plan', 'creator']);

        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['client.user', 'gym', 'items']);

        return view('invoices.print', compact('invoice'));
    }

    public function email(Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('invoices.manage'), 403);

        $client = $invoice->client;

        if (! $client?->user?->email) {
            return back()->withErrors(['invoice' => 'This client has no email address on file.']);
        }

        $this->notifications->toUser(
            $client->user,
            'Invoice ' . $invoice->invoice_no,
            "Your invoice of " . money($invoice->grand_total) . " is available.",
            'info',
            route('client.invoices.show', $invoice)
        );

        return back()->with('success', 'Invoice sent to client.');
    }

    public function markPaid(Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('invoices.manage'), 403);

        $invoice->update(['status' => 'paid', 'paid_at' => now()]);

        return back()->with('success', 'Invoice marked as paid.');
    }

    public function void(Invoice $invoice)
    {
        abort_unless(auth()->user()->hasPermission('invoices.manage'), 403);

        $invoice->update(['status' => 'void']);

        return back()->with('success', 'Invoice voided.');
    }
}
