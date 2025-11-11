<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected ?Role $cachedPrimaryRole = null;
    protected ?Collection $cachedPermissions = null;

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
        'role',
    ];

    protected $appends = [
        'primary_role_id',
        'display_role_name',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function valabilitati(): HasMany
    {
        return $this->hasMany(Valabilitate::class, 'sofer_id');
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

        $this->unsetRelation('roles');
        $this->loadMissing('roles.permissions');

        $this->forgetCachedPermissions();
    }

    /**
     * @deprecated Accesul utilizatorilor se gestionează prin roluri; metoda rămâne disponibilă pentru scenarii moștenite.
     */
    public function syncPermissions($permissions): void
    {
        $ids = $this->resolvePermissionIds($permissions);

        $this->permissions()->sync($ids);

        $this->forgetCachedPermissions();
    }

    public function hasPermission(int|string|Permission $permission): bool
    {
        $permissions = $this->getCachedPermissions();

        if (is_int($permission)) {
            return $permissions->contains(fn (Permission $assigned) => (int) $assigned->id === $permission);
        }

        if ($permission instanceof Permission) {
            return $permissions->contains(fn (Permission $assigned) => (int) $assigned->id === (int) $permission->id);
        }

        $needle = (string) $permission;

        return $permissions->contains(function (Permission $assigned) use ($needle) {
            return $assigned->slug === $needle || $assigned->module === $needle || $assigned->name === $needle;
        });
    }

    public function scopeOrderByPrimaryRole(Builder $query, string $direction = 'asc'): Builder
    {
        $primaryRoleNameSubquery = DB::table('role_user')
            ->selectRaw('COALESCE(roles.name, roles.slug, CAST(role_user.role_id AS CHAR))')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereColumn('role_user.user_id', 'users.id')
            ->orderBy('role_user.created_at')
            ->orderBy('role_user.id')
            ->limit(1);

        return $query->orderByRaw(
            'COALESCE((' . $primaryRoleNameSubquery->toSql() . '), ?) ' . $direction,
            array_merge($primaryRoleNameSubquery->getBindings(), [''])
        );
    }

    protected function resolvePermissionIds($permissions): array
    {
        $collection = collect($permissions instanceof Collection ? $permissions : (array) $permissions);

        if ($collection->isEmpty()) {
            return [];
        }

        return $collection
            ->map(function ($permission) {
                if ($permission instanceof Permission) {
                    return (int) $permission->id;
                }

                if (is_numeric($permission)) {
                    return (int) $permission;
                }

                return Permission::query()
                    ->where('slug', (string) $permission)
                    ->orWhere('module', (string) $permission)
                    ->value('id');
            })
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    protected function getCachedPermissions(): Collection
    {
        if ($this->cachedPermissions instanceof Collection) {
            return $this->cachedPermissions;
        }

        $this->loadMissing(['permissions', 'roles.permissions']);

        $direct = $this->permissions instanceof Collection ? $this->permissions : collect();
        $rolePermissions = $this->roles instanceof Collection
            ? $this->roles
                ->filter()
                ->flatMap(function (Role $role) {
                    $role->loadMissing('permissions');

                    return $role->permissions instanceof Collection ? $role->permissions : collect();
                })
            : collect();

        $this->cachedPermissions = $direct
            ->merge($rolePermissions)
            ->filter()
            ->unique('id')
            ->values();

        return $this->cachedPermissions;
    }

    public function forgetCachedPermissions(): void
    {
        $this->cachedPermissions = null;
        $this->unsetRelation('permissions');
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
