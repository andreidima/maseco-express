@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="culoare2 border border-secondary p-2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-users me-1"></i>Utilizatori / {{ $user->name }}
                    </span>
                </div>

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >

            @include ('errors')

                    <div class="table-responsive col-md-12 mx-auto">
                        <table class="table table-striped table-hover"
                        >
                            <tr>
                                <td class="pe-4">
                                    Nume
                                </td>
                                <td>
                                    {{ $user->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Roluri
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse ($user->roles as $role)
                                            @php
                                                $roleLabel = $role->name ?? \App\Models\User::LEGACY_ROLE_LABELS[$role->id] ?? ($role->slug ? ucwords(str_replace(['-', '_'], ' ', $role->slug)) : '');
                                            @endphp
                                            <span class="badge bg-primary">{{ $roleLabel }}</span>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Acces suplimentar
                                </td>
                                <td>
                                    @php
                                        $legacyPermissions = $user->permissions?->unique('id');
                                    @endphp
                                    <div class="text-muted">
                                        Accesul se stabilește exclusiv prin rolurile active ale utilizatorului.
                                    </div>
                                    @if ($legacyPermissions && $legacyPermissions->isNotEmpty())
                                        <div class="alert alert-warning mt-2 py-2 px-3 small" role="status">
                                            Acest cont păstrează permisiuni directe moștenite ({{ $legacyPermissions->count() }}). Recomandăm revizuirea rolurilor pentru a integra accesul necesar.
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Stare cont
                                </td>
                                <td>
                                    @if ($user->activ == 0)
                                        <span class="text-danger">Închis</span>
                                    @else
                                        <span class="text-success">Deschis</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Telefon
                                </td>
                                <td>
                                    {{ $user->telefon }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Email
                                </td>
                                <td>
                                    {{ $user->email }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-row mb-2 px-2">
                        <div class="col-lg-12 d-flex justify-content-center">
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('userReturnUrl') }}">Înapoi</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
