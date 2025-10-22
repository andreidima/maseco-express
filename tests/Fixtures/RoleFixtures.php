<?php

namespace Tests\Fixtures;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RoleFixtures
{
    public static function createRole(string $slug, ?array $modules = null, array $attributes = []): Role
    {
        $payload = array_merge([
            'name' => Str::headline($slug),
            'description' => Str::headline($slug) . ' role.',
        ], $attributes);

        $role = Role::firstOrCreate(['slug' => $slug], $payload);

        if (! $role->wasRecentlyCreated && ! empty($attributes)) {
            $role->fill($payload);
            $role->save();
        }

        $assignedModules = $modules;

        if ($assignedModules === null) {
            $defaults = config('permissions.role_defaults', []);
            $assignedModules = Arr::get($defaults, $slug, []);
        }

        $role->syncPermissions(static::resolvePermissionIds($assignedModules));

        return $role->fresh();
    }

    /**
     * @param  null|array|string  $modules
     */
    protected static function resolvePermissionIds(null|array|string $modules): array
    {
        if ($modules === '*' || (is_array($modules) && in_array('*', $modules, true))) {
            return Permission::query()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $moduleList = collect((array) $modules)
            ->filter()
            ->map(fn ($module) => (string) $module)
            ->values();

        if ($moduleList->isEmpty()) {
            return [];
        }

        return Permission::query()
            ->whereIn('module', $moduleList)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
