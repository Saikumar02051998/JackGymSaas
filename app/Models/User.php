<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected ?\Illuminate\Support\Collection $cachedRoleSlugs = null;

    protected ?array $cachedPermissionSlugs = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function markEmailAsVerified(): void
    {
        $this->forceFill(['email_verified_at' => now()])->save();
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function clientProfile()
    {
        return $this->hasOne(Client::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    protected function roleSlugs(): \Illuminate\Support\Collection
    {
        if ($this->cachedRoleSlugs === null) {
            $this->cachedRoleSlugs = $this->roles()->pluck('slug');
        }

        return $this->cachedRoleSlugs;
    }

    public function hasRole(string|array $roles): bool
    {
        $slugs = $this->roleSlugs();

        if (is_array($roles)) {
            return $slugs->intersect($roles)->isNotEmpty();
        }

        return $slugs->contains($roles);
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    public function isStaff(): bool
    {
        return $this->roleSlugs()->contains(fn ($slug) => $slug !== 'owner' && $slug !== 'client');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return in_array($permission, $this->permissionSlugs(), true);
    }

    public function permissionSlugs(): array
    {
        if ($this->cachedPermissionSlugs !== null) {
            return $this->cachedPermissionSlugs;
        }

        return $this->cachedPermissionSlugs = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn (Role $role) => $role->permissions->pluck('slug'))
            ->unique()
            ->values()
            ->all();
    }

    public function getAllPermissions(): array
    {
        if ($this->isOwner()) {
            return $this->allPermissionSlugs();
        }

        return $this->permissionSlugs();
    }

    public function allPermissionSlugs(): array
    {
        if ($this->cachedPermissionSlugs !== null) {
            return $this->cachedPermissionSlugs;
        }

        return $this->cachedPermissionSlugs = Permission::pluck('slug')->all();
    }

    public function homeRoute(): string
    {
        if ($this->hasRole('saas_owner') && is_saas()) {
            return route('saas.dashboard');
        }

        if ($this->isClient()) {
            return route('client.dashboard');
        }

        if ($this->roleSlugs()->isNotEmpty()) {
            return route('dashboard');
        }

        return route('login');
    }
}
