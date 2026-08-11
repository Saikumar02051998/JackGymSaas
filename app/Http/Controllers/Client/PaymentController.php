<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $client = auth()->user()->clientProfile;

        $payments = $client->payments()
            ->with('plan', 'invoice')
            ->orderByDesc('payment_date')
            ->paginate(20);

        $stats = [
            'total_paid' => (float) $client->payments()->where('status', 'success')->sum('final_amount'),
            'payments_count' => $client->payments()->where('status', 'success')->count(),
            'pending' => (float) $client->payments()->whereIn('status', ['pending', 'processing'])->sum('final_amount'),
        ];

        return view('client.payments', compact('payments', 'stats'));
    }
}
