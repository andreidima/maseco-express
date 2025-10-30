@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>{{ $masina->numar_inmatriculare }}
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a href="{{ route('masini-mementouri.show', $masina) }}" class="btn btn-sm btn-secondary border border-dark rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>{{ __('Înapoi la mașină') }}
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="mx-3 mb-4">
            <form method="POST" action="{{ route('masini-mementouri.fisiere-generale.store', $masina) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-8">
                    <label class="form-label" for="fisier">{{ __('Încarcă fișier') }}</label>
                    <input type="file" id="fisier" name="fisier" class="form-control rounded-3" required>
                    <small class="text-muted">{{ __('Fișiere permise: pdf, imagini, documente Office (maxim 50 MB).') }}</small>
                </div>
                <div class="col-md-4 text-md-end">
                    <button type="submit" class="btn btn-success text-white border border-dark w-100 rounded-3">
                        <i class="fa-solid fa-upload me-1"></i>{{ __('Încarcă') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="mx-3">
            <h5 class="mb-3">{{ __('Fișiere generale') }}</h5>
            @include('masini-mementouri.partials.general-files-list', ['masina' => $masina, 'fisiere' => $fisiere])
        </div>
    </div>
</div>
@endsection
