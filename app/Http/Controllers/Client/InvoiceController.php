<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $invoices = $client->invoices()->orderByDesc('invoice_date')->paginate(20);

        return view('client.invoices', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        abort_unless($invoice->client_id === auth()->user()->clientProfile?->id, 403);

        $invoice->load(['items', 'payment', 'membership.plan', 'gym']);

        return view('client.invoice-show', compact('invoice'));
    }
}
