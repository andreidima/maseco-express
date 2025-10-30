@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>Adaugă mașină
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a href="{{ route('masini-mementouri.index') }}" class="btn btn-sm btn-secondary border border-dark rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la listă
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        <div class="mx-3 mb-4">
            @include('errors')
        </div>

        <div class="mx-3">
            <form method="POST" action="{{ route('masini-mementouri.store') }}" class="row g-3">
                @csrf

                <div class="col-md-3">
                    <label class="form-label" for="numar_inmatriculare">Număr înmatriculare</label>
                    <input type="text" id="numar_inmatriculare" name="numar_inmatriculare" class="form-control rounded-3"
                           value="{{ old('numar_inmatriculare') }}" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="descriere">Descriere</label>
                    <input type="text" id="descriere" name="descriere" class="form-control rounded-3"
                           value="{{ old('descriere') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="marca_masina">Marca mașină</label>
                    <input type="text" id="marca_masina" name="marca_masina" class="form-control rounded-3"
                           value="{{ old('marca_masina') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="serie_sasiu">Serie șasiu</label>
                    <input type="text" id="serie_sasiu" name="serie_sasiu" class="form-control rounded-3"
                           value="{{ old('serie_sasiu') }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="email_notificari">Email notificări</label>
                    <input type="email" id="email_notificari" name="email_notificari" class="form-control rounded-3"
                           value="{{ old('email_notificari', $defaultEmail) }}">
                </div>

                <div class="col-12">
                    <label class="form-label" for="observatii">Observații</label>
                    <textarea id="observatii" name="observatii" class="form-control rounded-3" rows="2">{{ old('observatii') }}</textarea>
                </div>

                <div class="col-12">
                    <div class="alert alert-info border border-info-subtle rounded-4">
                        După salvare vei fi redirecționat către pagina dedicată mașinii de unde poți gestiona și fișierele
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Salvează mașina
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
