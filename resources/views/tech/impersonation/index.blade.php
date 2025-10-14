@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
            <div>
                <h1 class="h3 mb-0">Impersonare utilizatori</h1>
                <p class="text-muted mb-0">Autentifică-te temporar ca un alt utilizator pentru a-i vedea permisiunile.</p>
            </div>
            <form class="d-flex" method="get" action="{{ route('tech.impersonation.index') }}">
                <div class="input-group">
                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                        placeholder="Caută după nume sau email">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Caută
                    </button>
                </div>
            </form>
        </div>

        @if (session('impersonation_status'))
            <div class="alert alert-info" role="alert">
                {{ session('impersonation_status') }}
            </div>
        @endif

        @if ($isImpersonating)
            <div class="alert alert-warning" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                Ești autentificat temporar ca <strong>{{ auth()->user()->name }}</strong>.
                Folosește butonul „Stop impersonating” din meniu pentru a reveni la contul tău.
            </div>
        @endif

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Nume</th>
                            <th scope="col">Email</th>
                            <th scope="col" class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="align-middle">{{ $user->name }}</td>
                                <td class="align-middle">{{ $user->email }}</td>
                                <td class="align-middle text-end">
                                    <form method="post" action="{{ route('tech.impersonation.start') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <button type="submit" class="btn btn-sm btn-primary"
                                            @if ($user->id === $activeUserId) disabled @endif>
                                            <i class="fa-solid fa-user-secret me-1"></i>Impersonare
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nu am găsit niciun utilizator pentru criteriile de căutare introduse.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="card-footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
