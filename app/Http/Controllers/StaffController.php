<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $gymId = current_gym()?->id;

        $query = User::with(['roles', 'staffProfile'])
            ->where('gym_id', $gymId)
            ->whereHas('roles', fn ($q) => $q->where('slug', '!=', 'client'));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('staffProfile', fn ($sq) => $sq->where('employee_id', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $request->input('role')));
        }

        $staff = $query->orderBy('name')->paginate(15)->withQueryString();

        $roles = Role::where(function ($q) use ($gymId) {
            $q->whereNull('gym_id')->orWhere('gym_id', $gymId);
        })->where('slug', '!=', 'client')->orderBy('name')->get();

        return view('staff.index', compact('staff', 'roles'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('staff.create'), 403);

        $roles = Role::where(function ($q) {
            $q->whereNull('gym_id')->orWhere('gym_id', current_gym()?->id);
        })->where('slug', '!=', 'client')->orderBy('name')->get();

        return view('staff.create', compact('roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('staff.create'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'salary_type' => ['nullable', 'in:fixed,commission,contract'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'gym_id' => current_gym()->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $role = Role::findOrFail($data['role_id']);
            $user->roles()->attach($role->id);

            StaffProfile::create([
                'gym_id' => current_gym()->id,
                'user_id' => $user->id,
                'employee_id' => $data['employee_id'] ?? 'EMP-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
                'designation' => $data['designation'] ?? $role->name,
                'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                'salary_type' => $data['salary_type'] ?? 'fixed',
                'basic_salary' => $data['basic_salary'] ?? 0,
                'allowances' => $data['allowances'] ?? 0,
                'commission_rate' => $data['commission_rate'] ?? 0,
                'status' => 'active',
            ]);

            audit_log('staff.created', 'staff', $user->id, "Created staff member {$data['name']}");

            return $user;
        });

        return redirect()->route('staff.show', $user)->with('success', 'Staff member created.');
    }

    public function show(User $user)
    {
        $this->assertStaff($user);

        $user->load([
            'roles',
            'staffProfile',
            'staffProfile.salaries' => fn ($q) => $q->orderByDesc('period'),
            'staffProfile.attendance' => fn ($q) => $q->orderByDesc('attendance_date')->take(30),
            'staffProfile.leaves' => fn ($q) => $q->orderByDesc('start_date'),
        ]);

        return view('staff.show', compact('user'));
    }

    public function edit(User $user)
    {
        abort_unless(auth()->user()->hasPermission('staff.edit'), 403);
        $this->assertStaff($user);

        $roles = Role::where(function ($q) {
            $q->whereNull('gym_id')->orWhere('gym_id', current_gym()?->id);
        })->where('slug', '!=', 'client')->orderBy('name')->get();

        return view('staff.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        abort_unless(auth()->user()->hasPermission('staff.edit'), 403);
        $this->assertStaff($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'designation' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'salary_type' => ['nullable', 'in:fixed,commission,contract'],
            'basic_salary' => ['nullable', 'numeric', 'min:0'],
            'allowances' => ['nullable', 'numeric', 'min:0'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'employee_id' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $user->status,
                'password' => $data['password'] ?? $user->password,
            ]);

            $user->roles()->sync([$data['role_id']]);

            $user->staffProfile?->update([
                'employee_id' => $data['employee_id'] ?? $user->staffProfile->employee_id,
                'designation' => $data['designation'] ?? $user->staffProfile->designation,
                'joining_date' => $data['joining_date'] ?? $user->staffProfile->joining_date,
                'salary_type' => $data['salary_type'] ?? $user->staffProfile->salary_type,
                'basic_salary' => $data['basic_salary'] ?? $user->staffProfile->basic_salary,
                'allowances' => $data['allowances'] ?? $user->staffProfile->allowances,
                'commission_rate' => $data['commission_rate'] ?? $user->staffProfile->commission_rate,
            ]);

            audit_log('staff.updated', 'staff', $user->id, "Updated staff member {$user->name}");
        });

        return redirect()->route('staff.show', $user)->with('success', 'Staff member updated.');
    }

    public function toggleStatus(User $user)
    {
        abort_unless(auth()->user()->hasPermission('staff.edit'), 403);
        $this->assertStaff($user);

        $user->update(['status' => $user->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', 'Staff status updated.');
    }

    public function destroy(User $user)
    {
        abort_unless(auth()->user()->hasPermission('staff.delete'), 403);
        $this->assertStaff($user);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['staff' => 'You cannot delete your own account.']);
        }

        DB::transaction(function () use ($user) {
            $user->roles()->detach();
            $user->staffProfile?->delete();
            $user->delete();
        });

        audit_log('staff.deleted', 'staff', $user->id, "Deleted staff member {$user->name}");

        return redirect()->route('staff.index')->with('success', 'Staff member removed.');
    }

    private function assertStaff(User $user): void
    {
        abort_unless($user->staffProfile || $user->isOwner(), 404, 'Not a staff member.');
    }
}
