<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected ?Role $cachedPrimaryRole = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telefon',
        'email',
        'password',
        'activ',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'activ' => 'integer',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(int|string $role): bool
    {
        $roles = $this->relationLoaded('roles') && $this->roles instanceof Collection
            ? $this->roles
            : $this->roles()->get();

        if (! $roles instanceof Collection) {
            return false;
        }

        if (is_numeric($role)) {
            $roleId = (int) $role;

            return $roles->contains(fn (Role $assignedRole) => (int) $assignedRole->id === $roleId);
        }

        $needle = (string) $role;

        return $roles->contains(function (Role $assignedRole) use ($needle) {
            return $assignedRole->slug === $needle || $assignedRole->name === $needle;
        });
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('admin') || $this->hasRole(1) || $this->hasRole('super-admin');
    }

    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    protected function resolvePrimaryRole(): ?Role
    {
        if ($this->cachedPrimaryRole instanceof Role) {
            return $this->cachedPrimaryRole;
        }

        if (! $this->exists) {
            return $this->cachedPrimaryRole = null;
        }

        if ($this->relationLoaded('roles') && $this->roles instanceof Collection) {
            $this->cachedPrimaryRole = $this->roles
                ->sortBy(function (Role $role) {
                    $pivot = $role->pivot;

                    $timestamp = $pivot?->created_at?->getTimestamp() ?? PHP_INT_MAX;
                    $roleId = $role->id ?? PHP_INT_MAX;

                    return sprintf('%020d-%020d', $timestamp, $roleId);
                })
                ->first();
        } else {
            $this->cachedPrimaryRole = $this->roles()
                ->orderBy('role_user.created_at')
                ->orderBy('role_user.id')
                ->first();
        }

        return $this->cachedPrimaryRole ?: null;
    }

    public function getPrimaryRoleIdAttribute(): ?int
    {
        $role = $this->resolvePrimaryRole();

        if ($role) {
            return (int) $role->id;
        }

        return null;
    }

    public function getDisplayRoleNameAttribute(): ?string
    {
        $role = $this->resolvePrimaryRole();

        if ($role) {
            if ($role->name) {
                return $role->name;
            }

            if ($role->slug) {
                return (string) Str::of($role->slug)
                    ->replace(['-', '_'], ' ')
                    ->title();
            }
        }

        return null;
    }

    public function path()
    {
        return "/utilizatori/{$this->id}";
    }

    public function comenzi()
    {
        return $this->hasMany(Comanda::class, 'user_id');
    }
}
