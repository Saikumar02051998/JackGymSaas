<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class MembershipController extends Controller
{
    public function show()
    {
        $client = auth()->user()->clientProfile;

        $client->load([
            'activeMembership.plan',
            'memberships.plan',
            'memberships.histories' => fn ($q) => $q->latest()->take(10),
        ]);

        return view('client.membership', compact('client'));
    }
}
