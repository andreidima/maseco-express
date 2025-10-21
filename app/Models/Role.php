<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    protected ?Collection $cachedPermissions = null;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
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

    public function syncPermissions($permissions): void
    {
        $ids = $this->resolvePermissionIds($permissions);

        $this->permissions()->sync($ids);

        $this->forgetCachedPermissions();
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

        $this->cachedPermissions = $this->relationLoaded('permissions') && $this->permissions instanceof Collection
            ? $this->permissions
            : $this->permissions()->get();

        return $this->cachedPermissions ?? collect();
    }

    public function forgetCachedPermissions(): void
    {
        $this->cachedPermissions = null;
        $this->unsetRelation('permissions');
    }
}
