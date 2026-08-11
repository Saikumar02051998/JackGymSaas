<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $client = auth()->user()->clientProfile;
        $client->load(['user', 'healthProfile', 'trainer.user']);

        return view('client.profile', compact('client'));
    }

    public function update(Request $request)
    {
        $client = auth()->user()->clientProfile;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($client->user_id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $client->user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        $client->update([
            'phone' => $data['phone'] ?? $client->phone,
            'gender' => $data['gender'] ?? $client->gender,
            'dob' => $data['dob'] ?? $client->dob,
            'address' => $data['address'] ?? $client->address,
            'emergency_contact' => $data['emergency_contact'] ?? $client->emergency_contact,
            'emergency_phone' => $data['emergency_phone'] ?? $client->emergency_phone,
        ]);

        audit_log('client.profile_updated', 'clients', $client->id, "Client updated own profile");

        return back()->with('success', 'Profile updated.');
    }
}
