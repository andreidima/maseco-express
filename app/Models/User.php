<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
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
        'role',
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
        'role' => 'integer',
        'activ' => 'integer',
    ];

    public const LEGACY_ROLE_LABELS = [
        1 => 'Admin',
        2 => 'Dispecer',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function hasRole(int|string $role): bool
    {
        if ($this->id === 1) {
            return true;
        }

        if (is_numeric($role)) {
            $roleId = (int) $role;

            if ((int) $this->role === $roleId) {
                return true;
            }

            return $this->roles->contains('id', $roleId);
        }

        $roles = $this->roles instanceof Collection ? $this->roles : $this->roles()->get();

        return $roles->contains(function ($assignedRole) use ($role) {
            return $assignedRole->slug === $role || $assignedRole->name === $role;
        });
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('admin') || $this->hasRole(1);
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
            $this->cachedPrimaryRole = $this->roles->first();
        } else {
            $this->cachedPrimaryRole = $this->roles()->first();
        }

        return $this->cachedPrimaryRole;
    }

    public function getPrimaryRoleIdAttribute(): ?int
    {
        $role = $this->resolvePrimaryRole();

        if ($role) {
            return (int) $role->id;
        }

        return $this->role ? (int) $this->role : null;
    }

    public function getDisplayRoleNameAttribute(): ?string
    {
        $role = $this->resolvePrimaryRole();

        if ($role) {
            return $role->name;
        }

        return self::LEGACY_ROLE_LABELS[$this->role] ?? null;
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
