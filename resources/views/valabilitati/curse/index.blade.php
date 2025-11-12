@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-route me-1"></i>Curse asociate
            </span>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-end align-items-stretch align-items-lg-center gap-2">
                <a href="{{ route('valabilitati.show', $valabilitate) }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                    <i class="fas fa-arrow-left text-white me-1"></i>Înapoi la valabilitate
                </a>
                <a href="{{ $backUrl }}" class="btn btn-sm btn-outline-secondary border border-dark rounded-3">
                    <i class="fa-solid fa-list me-1"></i>Înapoi la listă
                </a>
            </div>
        </div>
    </div>

    <div class="card-body py-4">
        @include('errors')

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Informații generale</h6>
                    <p class="mb-1"><strong>Denumire:</strong> {{ $valabilitate->denumire }}</p>
                    <p class="mb-1"><strong>Număr auto:</strong> {{ $valabilitate->numar_auto }}</p>
                    <p class="mb-0"><strong>Șofer:</strong> {{ $valabilitate->sofer->name ?? '—' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Perioadă</h6>
                    <p class="mb-1">
                        <strong>Început:</strong>
                        {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                    </p>
                    <p class="mb-0">
                        <strong>Sfârșit:</strong>
                        {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="border rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">Curse asociate</h6>
                <span class="badge bg-primary text-white">{{ $valabilitate->curse->count() }} înregistrări</span>
            </div>

            @if ($valabilitate->curse->isEmpty())
                <p class="text-muted mb-4">Nu există curse asociate acestei valabilități.</p>
            @else
                <div class="table-responsive mb-4">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Încărcare - localitate</th>
                                <th>Încărcare - cod poștal</th>
                                <th>Descărcare - localitate</th>
                                <th>Descărcare - cod poștal</th>
                                <th>Data cursă</th>
                                <th>Observații</th>
                                <th class="text-end">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($valabilitate->curse as $cursa)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cursa->incarcare_localitate ?: '—' }}</td>
                                    <td>{{ $cursa->incarcare_cod_postal ?: '—' }}</td>
                                    <td>{{ $cursa->descarcare_localitate ?: '—' }}</td>
                                    <td>{{ $cursa->descarcare_cod_postal ?: '—' }}</td>
                                    <td>{{ $cursa->data_cursa?->format('d.m.Y H:i') ?: '—' }}</td>
                                    <td>{{ $cursa->observatii ?: '—' }}</td>
                                    <td class="text-end">
                                        <a
                                            href="{{ route('valabilitati.curse.edit', [$valabilitate, $cursa]) }}"
                                            class="btn btn-sm btn-outline-secondary me-2"
                                            title="Modifică cursa"
                                        >
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form
                                            action="{{ route('valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Sigur dorești să ștergi această cursă?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Șterge cursa">
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

            <h6 class="text-muted text-uppercase mb-3">Adaugă cursă</h6>
            <form method="POST" action="{{ route('valabilitati.curse.store', $valabilitate) }}" class="needs-validation" novalidate>
                @csrf

                @include('valabilitati.curse._form', ['cursa' => null])

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                        <i class="fa-solid fa-plus me-1"></i>Adaugă cursă
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
