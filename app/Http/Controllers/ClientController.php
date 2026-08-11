<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientHealthProfile;
use App\Models\StaffProfile;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct(protected ClientService $clients) {}

    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Client::with(['user', 'activeMembership.plan', 'healthProfile'])
            ->where('gym_id', $gymId);

        if (! auth()->user()->isOwner() && auth()->user()->hasRole('coach')) {
            $query->where('assigned_trainer_id', auth()->user()->staffProfile?->id);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('member_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'active') {
                $query->where('status', 'active');
            } elseif ($request->input('status') === 'expiring') {
                $query->whereHas('activeMembership', fn ($q) => $q->where('end_date', '>=', now()->toDateString())->where('end_date', '<=', now()->addDays(30)->toDateString()));
            } elseif ($request->input('status') === 'expired') {
                $query->whereDoesntHave('memberships', fn ($q) => $q->where('status', 'active')->where('end_date', '>=', now()->toDateString()));
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('coach')) {
            $query->where('assigned_trainer_id', $request->input('coach'));
        }

        $clients = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $coaches = StaffProfile::where('gym_id', $gymId)
            ->whereHas('user.roles', fn ($q) => $q->where('slug', 'coach'))
            ->with('user')
            ->get();

        $counts = [
            'all' => Client::where('gym_id', $gymId)->count(),
            'active' => Client::where('gym_id', $gymId)->where('status', 'active')->count(),
            'expiring' => Client::where('gym_id', $gymId)->whereHas('activeMembership', fn ($q) => $q->where('end_date', '>=', now()->toDateString())->where('end_date', '<=', now()->addDays(30)->toDateString()))->count(),
            'expired' => Client::where('gym_id', $gymId)->whereDoesntHave('memberships', fn ($q) => $q->where('status', 'active')->where('end_date', '>=', now()->toDateString()))->count(),
        ];

        return view('clients.index', compact('clients', 'coaches', 'counts'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('clients.create'), 403);

        $coaches = StaffProfile::where('gym_id', current_gym()?->id)
            ->whereHas('user.roles', fn ($q) => $q->where('slug', 'coach'))
            ->with('user')
            ->get();

        return view('clients.create', compact('coaches'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('clients.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'lead_source' => ['nullable', 'string', 'max:50'],
            'assigned_trainer_id' => ['nullable', 'exists:staff_profiles,id'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'goal_weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'fitness_goal' => ['nullable', 'string', 'max:50'],
            'activity_level' => ['nullable', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'important_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['password'] = Str::random(8);

        $client = $this->clients->create($data, current_gym()->id, $data['assigned_trainer_id'] ?? null);

        if ($request->boolean('start_trial')) {
            \App\Models\Trial::create([
                'gym_id' => current_gym()->id,
                'client_id' => $client->id,
                'trial_start' => now()->toDateString(),
                'trial_end' => now()->addDays((int) ($request->input('trial_days', 7)))->toDateString(),
                'status' => 'active',
            ]);
        }

        if ($request->has('create_membership') && $request->boolean('create_membership')) {
            $plan = \App\Models\MembershipPlan::findOrFail($request->input('plan_id'));
            app(\App\Services\MembershipService::class)->create($client, $plan);

            if ($request->filled('amount')) {
                app(\App\Services\PaymentService::class)->completePayment(
                    \App\Models\Payment::create([
                        'gym_id' => current_gym()->id,
                        'payment_no' => next_sequence(\App\Models\Payment::class, 'payment_no', 'PAY-'),
                        'client_id' => $client->id,
                        'plan_id' => $plan->id,
                        'amount' => $plan->price,
                        'discount' => $plan->discount,
                        'tax' => $plan->tax,
                        'final_amount' => $plan->final_amount,
                        'payment_method' => 'cash',
                        'status' => 'pending',
                        'payment_date' => now()->toDateString(),
                        'created_by' => auth()->id(),
                    ]),
                    [],
                    'reception'
                );
            }
        }

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully.')
            ->with('client_password', $data['password']);
    }

    public function show(Client $client)
    {
        abort_unless($this->canViewClient($client), 403);

        $client->load([
            'user',
            'healthProfile',
            'trainer.user',
            'activeMembership.plan',
            'memberships.plan',
            'attendance' => fn ($q) => $q->orderByDesc('attendance_date')->take(30),
            'payments' => fn ($q) => $q->orderByDesc('payment_date')->take(10),
            'invoices' => fn ($q) => $q->orderByDesc('created_at')->take(10),
            'weightRecords' => fn ($q) => $q->orderByDesc('record_date')->take(30),
            'bodyMeasurements' => fn ($q) => $q->orderByDesc('record_date')->take(10),
            'fitnessGoals' => fn ($q) => $q->where('status', '!=', 'completed'),
            'workoutPlans' => fn ($q) => $q->where('status', 'active'),
            'dietPlans' => fn ($q) => $q->where('status', 'active'),
            'followups' => fn ($q) => $q->latest(),
            'appointments' => fn ($q) => $q->orderByDesc('appointment_date')->take(10),
            'ptSessions' => fn ($q) => $q->orderByDesc('session_date')->take(10),
            'documents',
        ]);

        $totalAttendance = $client->attendance()->where('status', 'present')->count();
        $lastVisit = $client->attendance()->orderByDesc('attendance_date')->first();

        return view('clients.show', compact('client', 'totalAttendance', 'lastVisit'));
    }

    public function edit(Client $client)
    {
        abort_unless(auth()->user()->hasPermission('clients.edit'), 403);
        abort_unless($this->canViewClient($client), 403);

        $coaches = StaffProfile::where('gym_id', current_gym()?->id)
            ->whereHas('user.roles', fn ($q) => $q->where('slug', 'coach'))
            ->with('user')
            ->get();

        return view('clients.edit', compact('client', 'coaches'));
    }

    public function update(Request $request, Client $client)
    {
        abort_unless(auth()->user()->hasPermission('clients.edit'), 403);
        abort_unless($this->canViewClient($client), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($client->user_id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', 'in:male,female,other'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'emergency_phone' => ['nullable', 'string', 'max:20'],
            'lead_source' => ['nullable', 'string', 'max:50'],
            'assigned_trainer_id' => ['nullable', 'exists:staff_profiles,id'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'goal_weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'fitness_goal' => ['nullable', 'string', 'max:50'],
            'activity_level' => ['nullable', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'important_notes' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $this->clients->update($client, $data);

        return redirect()->route('clients.show', $client)->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        abort_unless(auth()->user()->hasPermission('clients.delete'), 403);

        DB::transaction(function () use ($client) {
            $client->user->roles()->detach(\App\Models\Role::where('slug', 'client')->value('id'));
            $client->delete();
        });

        audit_log('client.deleted', 'clients', $client->id, "Deleted client {$client->display_name}");

        return redirect()->route('clients.index')->with('success', 'Client removed.');
    }

    public function toggleStatus(Client $client)
    {
        abort_unless(auth()->user()->hasPermission('clients.edit'), 403);

        $client->update(['status' => $client->status === 'active' ? 'inactive' : 'active']);

        audit_log('client.status_changed', 'clients', $client->id, "Client status changed to {$client->status}");

        return back()->with('success', 'Client status updated.');
    }

    public function updateHealth(Request $request, Client $client)
    {
        abort_unless(auth()->user()->hasPermission('clients.health'), 403);

        $data = $request->validate([
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'body_fat' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'goal_weight' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'fitness_goal' => ['nullable', 'string', 'max:50'],
            'activity_level' => ['nullable', 'string', 'max:30'],
            'medical_notes' => ['nullable', 'string'],
            'injuries' => ['nullable', 'string'],
            'limitations' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'important_notes' => ['nullable', 'string'],
        ]);

        $height = $data['height'] ?? $client->healthProfile?->height;
        $weight = $data['weight'] ?? $client->healthProfile?->weight;

        $data['bmi'] = ($height && $weight && $height > 0)
            ? round($weight / (($height / 100) ** 2), 1)
            : null;

        if ($client->healthProfile) {
            $client->healthProfile->update($data);
        } else {
            $client->healthProfile()->create($data);
        }

        if (! empty($data['weight'])) {
            $client->weightRecords()->create([
                'gym_id' => current_gym()?->id,
                'weight' => $data['weight'],
                'height' => $height,
                'bmi' => $data['bmi'],
                'record_date' => now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        }

        audit_log('client.health_updated', 'clients', $client->id, "Health profile updated for {$client->display_name}");

        return back()->with('success', 'Health profile updated.');
    }

    public function export(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = Client::with(['user', 'activeMembership.plan'])
            ->where('gym_id', $gymId);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $clients = $query->get();

        $filename = 'clients-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($clients) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Member ID', 'Name', 'Email', 'Phone', 'Gender', 'Joined', 'Plan', 'Expiry', 'Status']);

            foreach ($clients as $client) {
                fputcsv($handle, [
                    $client->member_id,
                    $client->user?->name,
                    $client->user?->email,
                    $client->phone,
                    ucfirst((string) $client->gender),
                    $client->joining_date,
                    $client->activeMembership?->plan?->name,
                    $client->activeMembership?->end_date,
                    $client->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function canViewClient(Client $client): bool
    {
        $user = auth()->user();

        if ($user->isOwner()) {
            return true;
        }

        if ($user->hasRole('coach')) {
            return $client->assigned_trainer_id === $user->staffProfile?->id;
        }

        return true;
    }
}
