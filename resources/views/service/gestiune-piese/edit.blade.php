@extends('layouts.app')

@php
    $piesa = $piesa ?? null;
@endphp

@section('content')
    <div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px; max-width: 900px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-pen-to-square me-1"></i>Editează piesa
            </span>
            <a href="{{ route('gestiune-piese.index') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la listă
            </a>
        </div>

        <div class="card-body">
            @include('errors')

            <form method="POST" action="{{ route('gestiune-piese.update', $piesa) }}" class="mt-3">
                @csrf
                @method('PUT')

                @include('service.gestiune-piese.partials.form')

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
                    <div class="text-muted small">
                        Ultima actualizare: {{ optional($piesa->updated_at)->format('d.m.Y H:i') ?? 'n/a' }}
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('gestiune-piese.index') }}" class="btn btn-outline-secondary rounded-3">
                            Renunță
                        </a>
                        <button type="submit" class="btn btn-primary rounded-3">
                            <i class="fa-solid fa-save me-1"></i>Actualizează piesa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
