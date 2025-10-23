@php
    $moduleDefinitions = collect($moduleDefinitions ?? config('permissions.modules', []));
    $moduleRoleMatrix = collect($moduleRoleMatrix ?? [])->map(function ($moduleRoles) {
        if ($moduleRoles instanceof \Illuminate\Support\Collection) {
            return $moduleRoles;
        }

        return collect($moduleRoles);
    });

    $normalizeRole = function ($role) {
        if ($role instanceof \App\Models\Role) {
            $slug = $role->slug ?? null;
            $name = $role->name ?? null;

            if (! $name && $slug) {
                $name = ucwords(str_replace(['-', '_'], ' ', $slug));
            }

            return array_filter([
                'id' => $role->id ?? null,
                'slug' => $slug,
                'name' => $name,
                'description' => $role->description ?? null,
            ]);
        }

        if (is_array($role)) {
            $slug = $role['slug'] ?? null;
            $name = $role['name'] ?? null;

            if (! $name && $slug) {
                $name = ucwords(str_replace(['-', '_'], ' ', $slug));
            }

            return array_filter([
                'id' => $role['id'] ?? null,
                'slug' => $slug,
                'name' => $name,
                'description' => $role['description'] ?? null,
            ]);
        }

        if (is_object($role) && isset($role->slug)) {
            $slug = $role->slug;
            $name = $role->name ?? null;

            if (! $name && $slug) {
                $name = ucwords(str_replace(['-', '_'], ' ', $slug));
            }

            return array_filter([
                'id' => $role->id ?? null,
                'slug' => $slug,
                'name' => $name,
                'description' => $role->description ?? null,
            ]);
        }

        if (is_string($role)) {
            return [
                'slug' => $role,
                'name' => ucwords(str_replace(['-', '_'], ' ', $role)),
            ];
        }

        return null;
    };

    $visibleRoles = collect($visibleRoles ?? $availableRoles ?? [])
        ->map($normalizeRole)
        ->filter(fn ($role) => is_array($role) && ! empty($role['slug']))
        ->unique('slug')
        ->values();

    if ($visibleRoles->isEmpty()) {
        $visibleRoles = $moduleRoleMatrix
            ->flatMap(function ($moduleRoles) use ($normalizeRole) {
                return $moduleRoles->map($normalizeRole);
            })
            ->filter(fn ($role) => is_array($role) && ! empty($role['slug']))
            ->unique('slug')
            ->values();
    }
@endphp

@if ($moduleDefinitions->isNotEmpty() && $moduleRoleMatrix->isNotEmpty())
    <div class="border rounded-3 p-3 bg-white shadow-sm">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h6 class="mb-1 fw-bold text-uppercase small">Legendă acces implicit pe roluri</h6>
                <p class="mb-0 text-muted small">
                    Rolurile stabilesc complet accesul utilizatorilor. Consultă tabelul pentru a vedea ce module sunt acordate automat de fiecare rol înainte să îl bifezi.
                </p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 permissions-matrix-table">
                <thead>
                    <tr class="table-light">
                        <th scope="col" class="module-name">Modul</th>
                        <th scope="col" class="module-description">Descriere</th>
                        @foreach ($visibleRoles as $roleDetails)
                            @php
                                $roleLabel = $roleDetails['name'] ?? ucwords(str_replace(['-', '_'], ' ', $roleDetails['slug'] ?? ''));
                            @endphp
                            <th scope="col" class="text-center matrix-role-col" title="{{ $roleDetails['description'] ?? $roleLabel }}">
                                {{ $roleLabel }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($moduleDefinitions as $moduleKey => $moduleDetails)
                        @php
                            $roleEntries = $moduleRoleMatrix->get($moduleKey, collect());
                            if (! ($roleEntries instanceof \Illuminate\Support\Collection)) {
                                $roleEntries = collect($roleEntries);
                            }

                            $rolesBySlug = $roleEntries->mapWithKeys(function ($role) use ($normalizeRole) {
                                $normalized = $normalizeRole($role);

                                if (! is_array($normalized) || empty($normalized['slug'])) {
                                    return [];
                                }

                                return [$normalized['slug'] => $normalized];
                            });
                        @endphp
                        <tr>
                            <td class="fw-semibold module-name">{{ $moduleDetails['name'] ?? ucwords(str_replace(['-', '_'], ' ', $moduleKey)) }}</td>
                            <td class="text-muted small module-description">{{ $moduleDetails['description'] ?? '—' }}</td>
                            @foreach ($visibleRoles as $roleDetails)
                                @php
                                    $roleSlug = $roleDetails['slug'] ?? null;
                                    $hasImplicitAccess = $roleSlug ? $rolesBySlug->has($roleSlug) : false;
                                    $roleLabel = $roleDetails['name'] ?? ucwords(str_replace(['-', '_'], ' ', $roleSlug ?? ''));
                                @endphp
                                <td class="matrix-role-col">
                                    @if ($hasImplicitAccess)
                                        <span class="matrix-role-indicator text-success" title="Rolul {{ $roleLabel }} acordă acces implicit">
                                            <i class="fa-solid fa-check"></i>
                                            <span class="visually-hidden">Acces implicit pentru rolul {{ $roleLabel }}</span>
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
