<?php

namespace App\Http\Controllers\Saas;

use App\Http\Controllers\Controller;
use App\Models\Gym;
use App\Models\SaasPayment;

class DashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasPermission('saas.dashboard.view'), 403);

        $stats = [
            'gyms' => Gym::withTrashed()->count(),
            'active' => Gym::where('subscription_status', 'active')->count(),
            'trial' => Gym::where('subscription_status', 'trial')->count(),
            'expired' => Gym::whereIn('subscription_status', ['expired', 'suspended'])->count(),
            'revenue' => (float) SaasPayment::where('status', 'paid')->sum('amount'),
        ];

        $expiringSoon = Gym::with('subscriptionPlan')
            ->whereIn('subscription_status', ['active', 'trial'])
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<=', now()->addDays(15))
            ->orderBy('subscription_expires_at')
            ->take(10)
            ->get();

        $recentPayments = SaasPayment::with(['gym', 'subscriptionPlan'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('saas.dashboard', compact('stats', 'expiringSoon', 'recentPayments'));
    }
}
