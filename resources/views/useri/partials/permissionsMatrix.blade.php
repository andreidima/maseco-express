@php
    $moduleDefinitions = collect($moduleDefinitions ?? config('permissions.modules', []));
    $moduleRoleMatrix = collect($moduleRoleMatrix ?? []);
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
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th scope="col" style="min-width: 200px;">Modul</th>
                        <th scope="col">Descriere</th>
                        <th scope="col" style="min-width: 220px;">Roluri cu acces implicit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($moduleDefinitions as $moduleKey => $moduleDetails)
                        @php
                            $roleEntries = $moduleRoleMatrix->get($moduleKey, collect());
                            if (! ($roleEntries instanceof \Illuminate\Support\Collection)) {
                                $roleEntries = collect($roleEntries);
                            }
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $moduleDetails['name'] ?? ucwords(str_replace(['-', '_'], ' ', $moduleKey)) }}</td>
                            <td class="text-muted small">{{ $moduleDetails['description'] ?? '—' }}</td>
                            <td>
                                @if ($roleEntries->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($roleEntries as $role)
                                            @php
                                                $roleName = is_array($role) ? ($role['name'] ?? null) : ($role->name ?? null);
                                                $roleName = $roleName ?: ucwords(str_replace(['-', '_'], ' ', is_array($role) ? ($role['slug'] ?? '') : ($role->slug ?? '')));
                                            @endphp
                                            <span class="badge bg-light text-dark border">{{ $roleName }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
