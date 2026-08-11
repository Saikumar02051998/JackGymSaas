<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\MembershipPlan;
use App\Models\StaffProfile;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(protected LeadService $leads) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Lead::with(['interestedPlan', 'assignedTo'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('source') && $request->input('source') !== 'all') {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (auth()->user()->hasRole('sales') && ! auth()->user()->isOwner()) {
            $query->where('assigned_to', auth()->id());
        }

        $leads = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $counts = [
            'new' => Lead::where('gym_id', $gymId)->where('status', 'new')->count(),
            'contacted' => Lead::where('gym_id', $gymId)->where('status', 'contacted')->count(),
            'interested' => Lead::where('gym_id', $gymId)->where('status', 'interested')->count(),
            'trial' => Lead::where('gym_id', $gymId)->where('status', 'trial')->count(),
            'converted' => Lead::where('gym_id', $gymId)->where('status', 'converted')->count(),
            'lost' => Lead::where('gym_id', $gymId)->whereIn('status', ['not_interested', 'lost'])->count(),
        ];

        return view('leads.index', compact('leads', 'counts'));
    }

    public function trials(Request $request)
    {
        $gymId = current_gym()?->id;

        $trials = \App\Models\Trial::with(['client.user', 'lead', 'assignedTrainer.user'])
            ->where('gym_id', $gymId)
            ->when($request->filled('status') && $request->input('status') !== 'all', fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('trial_end')
            ->paginate(15)->withQueryString();

        return view('leads.trials', compact('trials'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('leads.create'), 403);

        $plans = MembershipPlan::where('gym_id', current_gym()?->id)->where('status', 'active')->get();
        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)->get();

        return view('leads.create', compact('plans', 'staff'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('leads.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => ['nullable', 'string', 'max:50'],
            'interested_plan_id' => ['nullable', 'exists:membership_plans,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:new,contacted,interested,trial,converted,not_interested,lost'],
            'follow_up_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['assigned_to'] = $data['assigned_to'] ?? auth()->id();

        $lead = $this->leads->create($data, current_gym()->id);

        if ($request->filled('follow_up_date')) {
            $this->leads->createFollowup($lead, [
                'follow_up_date' => $request->input('follow_up_date'),
                'type' => 'phone',
                'notes' => 'Initial follow-up',
            ]);
        }

        return redirect()->route('leads.show', $lead)->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['interestedPlan', 'assignedTo', 'followups.creator']);

        $staff = \App\Models\User::with('roles')
            ->where('gym_id', current_gym()?->id)
            ->whereHas('roles', fn ($q) => $q->where('slug', '!=', 'client'))
            ->orderBy('name')
            ->get();

        return view('leads.show', compact('lead', 'staff'));
    }

    public function edit(Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.edit'), 403);

        $plans = MembershipPlan::where('gym_id', current_gym()?->id)->where('status', 'active')->get();
        $staff = StaffProfile::with('user')->where('gym_id', current_gym()?->id)->get();

        return view('leads.edit', compact('lead', 'plans', 'staff'));
    }

    public function update(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.edit'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'source' => ['nullable', 'string', 'max:50'],
            'interested_plan_id' => ['nullable', 'exists:membership_plans,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'in:new,contacted,interested,trial,converted,not_interested,lost'],
            'follow_up_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->leads->update($lead, $data);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead updated.');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.edit'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:new,contacted,interested,trial,converted,not_interested,lost'],
        ]);

        $lead->update(['status' => $data['status']]);

        return back()->with('success', 'Lead status updated.');
    }

    public function assign(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.manage'), 403);

        $data = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        $lead->update(['assigned_to' => $data['assigned_to']]);

        return back()->with('success', 'Lead assigned.');
    }

    public function convert(Request $request, Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.manage'), 403);

        $data = $request->validate([
            'start_trial' => ['nullable', 'boolean'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $result = $this->leads->convertToClient($lead, [
            'start_trial' => $request->boolean('start_trial'),
            'trial_days' => $request->input('trial_days', 7),
        ]);

        if (! $result['success']) {
            return back()->withErrors(['lead' => 'Could not convert this lead.']);
        }

        return redirect()->route('clients.show', $result['client'])->with('success', 'Lead converted to client.');
    }

    public function destroy(Lead $lead)
    {
        abort_unless(auth()->user()->hasPermission('leads.edit'), 403);

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }
}
