<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Gym;
use Illuminate\Http\Request;

class GymController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('saas.gyms.view'), 403);

        $query = Gym::with('subscriptionPlan')->withCount('users');

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('subscription_status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $gyms = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => Gym::count(),
            'active' => Gym::where('subscription_status', 'active')->count(),
            'trial' => Gym::where('subscription_status', 'trial')->count(),
            'expired' => Gym::whereIn('subscription_status', ['expired', 'suspended'])->count(),
        ];

        return view('saas.gyms', compact('gyms', 'summary'));
    }

    public function show(Gym $gym)
    {
        abort_unless(auth()->user()->hasPermission('saas.gyms.view'), 403);

        $gym->load(['subscriptionPlan', 'users.roles']);

        $owner = $gym->users()
            ->whereHas('roles', fn ($q) => $q->where('slug', 'owner'))
            ->first();

        $payments = $gym->saasPayments()
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('saas.gym-show', compact('gym', 'payments', 'owner'));
    }

    public function resetOwnerPassword(Request $request, Gym $gym)
    {
        abort_unless(auth()->user()->hasPermission('saas.gyms.manage'), 403);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $owner = $gym->users()
            ->whereHas('roles', fn ($q) => $q->where('slug', 'owner'))
            ->first();

        if (! $owner) {
            return back()->withErrors(['password' => 'No gym owner found for this gym.']);
        }

        $owner->update(['password' => $data['password']]);

        audit_log('saas.gym.owner_password', 'saas', $owner->id, "Password reset for gym owner {$owner->name} ({$gym->name})");

        return back()->with('success', "Password updated for {$owner->name}.");
    }

    public function toggleStatus(Request $request, Gym $gym)
    {
        abort_unless(auth()->user()->hasPermission('saas.gyms.manage'), 403);

        $data = $request->validate([
            'subscription_status' => ['required', 'in:active,trial,expired,suspended'],
        ]);

        $gym->subscription_status = $data['subscription_status'];
        $gym->save();

        if (in_array($data['subscription_status'], ['trial', 'active'], true) && ! $gym->subscription_expires_at) {
            $gym->subscription_expires_at = now()->addDays((int) saas_setting('trial_days', \App\Services\GymService::TRIAL_DAYS));
            $gym->save();
        }

        audit_log('saas.gym.status', 'saas', $gym->id, "Gym {$gym->name} subscription status set to {$data['subscription_status']}");

        return back()->with('success', 'Gym subscription status updated.');
    }
}
