<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['clientProfile', 'staffProfile', 'roles']);

        return view('profile.show', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:new_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        if (isset($data['avatar']) && $data['avatar']) {
            $data['avatar'] = $data['avatar']->store('avatars', 'public');
        }

        if (isset($data['current_password']) && $data['current_password']) {
            if (! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }

            $data['password'] = Hash::make($data['new_password']);
        }

        unset($data['current_password'], $data['new_password']);

        $user->update($data);

        audit_log('profile.updated', 'profile', $user->id, 'Profile updated');

        return back()->with('success', 'Profile updated successfully.');
    }
}
