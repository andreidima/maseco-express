@csrf

{{-- @php
    dd($user, old('email', $user->email));

@endphp --}}

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0 mx-auto">
        <input type="hidden" name="id" value="{{ $user->id }}">

        @php
            $allowSuperAdminSelection = $user && $user->exists && $user->hasRole('super-admin');
            $availableRoles = $roles ?? collect();
            $availablePermissions = $permissions ?? collect();

            if ($availableRoles instanceof \Illuminate\Support\Collection) {
                if (! $allowSuperAdminSelection) {
                    $availableRoles = $availableRoles->reject(fn ($roleOption) => $roleOption->slug === 'super-admin');
                }

                if ($availableRoles->isEmpty()) {
                    $availableRoles = \App\Models\Role::query()
                        ->when(! $allowSuperAdminSelection, fn ($query) => $query->where('slug', '!=', 'super-admin'))
                        ->with('permissions')
                        ->orderBy('id')
                        ->get();
                }
            }

            if (! $availablePermissions instanceof \Illuminate\Support\Collection) {
                $availablePermissions = collect();
            }

            $selectedRoles = collect(old('roles', $user->roles->pluck('id')->all()))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $selectedPermissions = collect(old('permissions', $user->permissions->pluck('id')->all()))
                ->map(fn ($value) => (int) $value)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $permissionGroups = $availablePermissions->groupBy('module');
            $permissionDefinitions = collect(config('permissions.modules', []));
        @endphp

        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="name" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('name') ? 'is-invalid' : '' }}"
                    name="name"
                    placeholder=""
                    value="{{ old('name', $user->name) }}"
                    required>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="text-center">
                    <label class="mb-0 ps-3">Cont activ<span class="text-danger">*</span></label>
                    <div class="d-flex py-1 justify-content-center">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" value="1" name="activ" id="activ_da"
                                {{ old('activ', $user->activ) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activ_da">DA</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0" name="activ" id="activ_nu"
                                {{ old('activ', $user->activ) == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="activ_nu">NU</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="email" class="mb-0 ps-3">Email<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    name="email"
                    placeholder=""
                    value="{{ old('email', $user->email) }}"
                    autocomplete="off">
            </div>
            <div class="col-lg-6 mb-4">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $user->telefon) }}"
                    required>
            </div>
        </div>
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3">Roluri<span class="text-danger">*</span></label>
                <div class="border rounded-3 p-3 bg-white {{ $errors->has('roles') || $errors->has('roles.*') ? 'border-danger' : '' }}">
                    @forelse ($availableRoles as $roleOption)
                        @php
                            $roleId = (int) ($roleOption->id ?? 0);
                            $roleLabel = $roleOption->name ?? \App\Models\User::LEGACY_ROLE_LABELS[$roleId] ?? ($roleOption->slug ? ucwords(str_replace(['-', '_'], ' ', $roleOption->slug)) : '');
                        @endphp
                        <div class="mb-3">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $roleId }}"
                                    id="role_{{ $roleId }}"
                                    {{ in_array($roleId, $selectedRoles, true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label fw-semibold" for="role_{{ $roleId }}">
                                    {{ $roleLabel }}
                                </label>
                            </div>
                            @if (! empty($roleOption->description))
                                <div class="ms-4 text-muted small">{{ $roleOption->description }}</div>
                            @endif
                            @if ($roleOption->relationLoaded('permissions') && $roleOption->permissions && $roleOption->permissions->isNotEmpty())
                                <div class="ms-4 mt-2">
                                    <small class="text-muted">Permisiuni implicite:</small>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach ($roleOption->permissions as $rolePermission)
                                            @php
                                                $permissionLabel = $rolePermission->name ?? ($rolePermission->module ? ucwords(str_replace(['-', '_'], ' ', $rolePermission->module)) : $rolePermission->slug);
                                            @endphp
                                            <span class="badge bg-light text-dark border">{{ $permissionLabel }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nu există roluri disponibile.</p>
                    @endforelse
                </div>
                @error('roles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('roles.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-lg-6 mb-4">
                <label class="mb-0 ps-3">Permisiuni suplimentare</label>
                <div class="border rounded-3 p-3 bg-white {{ $errors->has('permissions') || $errors->has('permissions.*') ? 'border-danger' : '' }}">
                    @forelse ($permissionGroups as $module => $modulePermissions)
                        @php
                            $moduleDetails = $permissionDefinitions->get($module, []);
                            $moduleDescription = $moduleDetails['description'] ?? null;
                        @endphp
                        <div class="mb-4">
                            @foreach ($modulePermissions as $permission)
                                @php
                                    $permissionId = (int) ($permission->id ?? 0);
                                    $permissionLabel = $permission->name ?? ucwords(str_replace(['-', '_'], ' ', (string) $permission->slug));
                                @endphp
                                <div class="form-check form-switch mb-3">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permissionId }}"
                                        id="permission_{{ $permissionId }}"
                                        {{ in_array($permissionId, $selectedPermissions, true) ? 'checked' : '' }}
                                    >
                                    <label class="form-check-label fw-semibold" for="permission_{{ $permissionId }}">
                                        {{ $permissionLabel }}
                                    </label>
                                    @if ($moduleDescription)
                                        <div class="text-muted small ms-5">{{ $moduleDescription }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nu există permisiuni configurate.</p>
                    @endforelse
                </div>
                @error('permissions')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('permissions.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="password" class="mb-0 ps-3">Parola</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="new-password"
                        placeholder="{{ str_contains(url()->current(), '/modifica') ? '********' : '' }}"
                    >
            </div>
            <div class="col-lg-6 mb-4">
                <label for="password_confirmation" class="mb-0 ps-3">Confirmare parolă</label>
                <input id="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation"
                    placeholder="{{ str_contains(url()->current(), '/modifica') ? '********' : '' }}"
                >
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-0 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('userReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
