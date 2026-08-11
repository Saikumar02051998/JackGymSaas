<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Gym;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $gym = Gym::where('slug', 'jack-gym')->first() ?? current_gym();

        $plans = MembershipPlan::where('gym_id', $gym?->id)
            ->where('status', 'active')
            ->orderBy('price')
            ->get();

        $announcements = Announcement::where('gym_id', $gym?->id)
            ->where('status', 'published')
            ->where('audience', 'public')
            ->latest()
            ->take(3)
            ->get();

        return view('landing.index', compact('gym', 'plans', 'announcements'));
    }
}
