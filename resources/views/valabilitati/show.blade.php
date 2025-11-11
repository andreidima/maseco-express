@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('valabilitati.index') }}" class="btn btn-link px-0">
            <i class="fa-solid fa-arrow-left me-1"></i> Înapoi la listă
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h5 mb-0">{{ $valabilitate->referinta ?: 'Valabilitate fără referință' }}</h2>
                <small class="text-muted">Creată la {{ $valabilitate->created_at?->format('d.m.Y H:i') }}</small>
            </div>
            <div>
                <a href="{{ route('valabilitati.edit', $valabilitate) }}" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="fa-solid fa-pen"></i> Modifică
                </a>
                <form action="{{ route('valabilitati.destroy', $valabilitate) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Sigur dorești să ștergi această valabilitate?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-trash"></i> Șterge
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @include('errors')

            <dl class="row mb-0">
                <dt class="col-sm-3">Mașină asociată</dt>
                <dd class="col-sm-9">{{ $valabilitate->masina?->numar_inmatriculare ?: '—' }}</dd>

                <dt class="col-sm-3">Prima cursă</dt>
                <dd class="col-sm-9">{{ $valabilitate->prima_cursa?->format('d.m.Y H:i') ?: '—' }}</dd>

                <dt class="col-sm-3">Ultima cursă</dt>
                <dd class="col-sm-9">{{ $valabilitate->ultima_cursa?->format('d.m.Y H:i') ?: '—' }}</dd>

                <dt class="col-sm-3">Total curse</dt>
                <dd class="col-sm-9">{{ $valabilitate->total_curse }}</dd>
            </dl>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h6 mb-0">Curse asociate</h3>
        </div>
        <div class="card-body">
            @if ($valabilitate->curse->isEmpty())
                <p class="text-muted">Nu există curse asociate acestei valabilități.</p>
            @else
                <div class="table-responsive mb-4">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Localitate plecare</th>
                                <th>Localitate sosire</th>
                                <th>Plecare</th>
                                <th>Sosire</th>
                                <th class="text-center">Km bord</th>
                                <th>Observații</th>
                                <th class="text-end">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($valabilitate->curse as $cursa)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cursa->localitate_plecare }}</td>
                                    <td>{{ $cursa->localitate_sosire ?: '—' }}</td>
                                    <td>{{ $cursa->plecare_la?->format('d.m.Y H:i') ?: '—' }}</td>
                                    <td>{{ $cursa->sosire_la?->format('d.m.Y H:i') ?: '—' }}</td>
                                    <td class="text-center">{{ $cursa->km_bord ?? '—' }}</td>
                                    <td>{{ $cursa->observatii ?: '—' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('valabilitati.curse.edit', [$valabilitate, $cursa]) }}"
                                           class="btn btn-sm btn-outline-secondary me-2">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Sigur dorești să ștergi această cursă?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="border rounded p-3">
                <h4 class="h6 mb-3">Adaugă cursă nouă</h4>
                <form method="POST" action="{{ route('valabilitati.curse.store', $valabilitate) }}">
                    @csrf
                    @include('valabilitati.curse._form', ['cursa' => null])
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Adaugă cursă
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
