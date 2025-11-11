@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="badge bg-primary text-uppercase">
                <i class="fa-solid fa-calendar-check me-1"></i>
                Valabilități
            </span>
            <a class="btn btn-sm btn-success" href="{{ route('valabilitati.create') }}">
                <i class="fa-solid fa-plus me-1"></i>
                Adaugă valabilitate
            </a>
        </div>

        <div class="card-body">
            @include('errors')

            @if ($valabilitati->isEmpty())
                <p class="text-muted mb-0">Nu există valabilități înregistrate în acest moment.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Referință</th>
                                <th>Mașină</th>
                                <th>Prima cursă</th>
                                <th>Ultima cursă</th>
                                <th class="text-center">Total curse</th>
                                <th class="text-end">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($valabilitati as $valabilitate)
                                <tr>
                                    <td>{{ $loop->iteration + ($valabilitati->currentPage() - 1) * $valabilitati->perPage() }}</td>
                                    <td>{{ $valabilitate->referinta ?: '—' }}</td>
                                    <td>{{ $valabilitate->masina?->numar_inmatriculare ?: '—' }}</td>
                                    <td>{{ $valabilitate->prima_cursa?->format('d.m.Y H:i') ?: '—' }}</td>
                                    <td>{{ $valabilitate->ultima_cursa?->format('d.m.Y H:i') ?: '—' }}</td>
                                    <td class="text-center">{{ $valabilitate->total_curse }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary me-1" href="{{ route('valabilitati.show', $valabilitate) }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a class="btn btn-sm btn-outline-secondary me-1" href="{{ route('valabilitati.edit', $valabilitate) }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('valabilitati.destroy', $valabilitate) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Sigur dorești să ștergi această valabilitate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $valabilitati->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
