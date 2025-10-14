@extends('layouts.app')

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-user-secret me-1"></i>Impersonare utilizatori
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ route('tech.impersonation.index') }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="search" name="search"
                                placeholder="Nume sau email" value="{{ $search }}">
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                        <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3"
                            href="{{ route('tech.impersonation.index') }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                        </a>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-end">
                @if ($isImpersonating)
                    <span class="badge bg-warning text-dark rounded-3 px-3 py-2">
                        <i class="fa-solid fa-person-running me-1"></i>Impersonare activă
                    </span>
                @endif
            </div>
        </div>

        <div class="card-body px-0 py-3">
            @include('errors')

            @if (session('impersonation_status'))
                <div class="alert alert-info mx-3" role="alert">
                    {{ session('impersonation_status') }}
                </div>
            @endif

            @if ($isImpersonating)
                <div class="alert alert-warning mx-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    Ești autentificat temporar ca <strong>{{ auth()->user()->name }}</strong>.
                    Folosește butonul „Stop impersonating” din meniu pentru a reveni la contul tău.
                </div>
            @endif

            <div class="table-responsive rounded-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th class="culoare2 text-white">#</th>
                            <th class="culoare2 text-white">Nume</th>
                            <th class="culoare2 text-white">Rol</th>
                            <th class="culoare2 text-white">Telefon</th>
                            <th class="culoare2 text-white">Email</th>
                            <th class="culoare2 text-white">Stare Cont</th>
                            <th class="culoare2 text-white text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $rowNumber = ($users->currentPage() - 1) * $users->perPage() + $loop->index + 1;
                                $roleNames = $user->roles->pluck('name')->filter()->all();

                                if (empty($roleNames) && $user->display_role_name) {
                                    $roleNames = [$user->display_role_name];
                                }
                            @endphp
                            <tr>
                                <td>{{ $rowNumber }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ implode(', ', $roleNames) ?: '—' }}</td>
                                <td>{{ $user->telefon ?: '—' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->activ == 0)
                                        <span class="text-danger">Închis</span>
                                    @else
                                        <span class="text-success">Deschis</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-end">
                                        <form method="POST" action="{{ route('tech.impersonation.start') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <button type="submit" class="badge bg-primary border-0"
                                                @if ($user->id === $activeUserId) disabled @endif>
                                                <i class="fa-solid fa-user-secret me-1"></i>Impersonare
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Nu am găsit niciun utilizator pentru criteriile de căutare introduse.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $users->appends(['search' => $search])->links() }}
            </div>
        </div>
    </div>
@endsection
