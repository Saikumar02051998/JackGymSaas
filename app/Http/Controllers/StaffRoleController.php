<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Support\PermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffRoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')
            ->where(function ($q) {
                $q->whereNull('gym_id')->orWhere('gym_id', current_gym()?->id);
            })
            ->where('slug', '!=', 'client')
            ->orderBy('name')
            ->paginate(15);

        $permissions = PermissionRegistry::all();

        return view('staff.roles', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', Rule::unique('roles', 'slug')->whereNull('gym_id')],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create([
            'gym_id' => null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        audit_log('role.created', 'staff', $role->id, "Created role {$role->name}");

        return back()->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be edited directly.');
        }

        $role->load('permissions');
        $permissions = PermissionRegistry::all();

        return view('staff.roles', [
            'roles' => Role::with('permissions')->withCount('users')
                ->where(function ($q) {
                    $q->whereNull('gym_id')->orWhere('gym_id', current_gym()?->id);
                })
                ->where('slug', '!=', 'client')
                ->orderBy('name')
                ->paginate(15),
            'permissions' => $permissions,
            'editingRole' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be edited.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        audit_log('role.updated', 'staff', $role->id, "Updated role {$role->name}");

        return redirect()->route('staff.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'This role is assigned to users and cannot be deleted.']);
        }

        $role->permissions()->detach();
        $role->delete();

        audit_log('role.deleted', 'staff', $role->id, "Deleted role {$role->name}");

        return back()->with('success', 'Role deleted.');
    }
}
