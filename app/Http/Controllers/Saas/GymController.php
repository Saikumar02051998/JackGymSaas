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

        $payments = $gym->saasPayments()
            ->with('subscriptionPlan')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('saas.gym-show', compact('gym', 'payments'));
    }

    public function toggleStatus(Request $request, Gym $gym)
    {
        abort_unless(auth()->user()->hasPermission('saas.gyms.manage'), 403);

        $data = $request->validate([
            'subscription_status' => ['required', 'in:active,trial,expired,suspended'],
        ]);

        $gym->subscription_status = $data['subscription_status'];
        $gym->save();

        audit_log('saas.gym.status', 'saas', $gym->id, "Gym {$gym->name} subscription status set to {$data['subscription_status']}");

        return back()->with('success', 'Gym subscription status updated.');
    }
}
