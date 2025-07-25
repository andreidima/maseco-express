@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-truck me-1"></i>
                        {{ isset($oferta) ? 'Editează ofertă' : 'Adaugă ofertă' }}
                    </span>
                </div>

                @include('errors')

                <div class="card-body p-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <form class="needs-validation" novalidate
                          method="POST"
                          action="{{ isset($oferta) ? route('oferte-curse.update', $oferta->id) : route('oferte-curse.store') }}">
                        @csrf
                        @if(isset($oferta))
                            @method('PUT')
                        @endif

                        @include('oferte_curse.form', [
                            'oferta'     => $oferta ?? null,
                            'buttonText' => isset($oferta) ? 'Salvează modificările' : 'Adaugă ofertă',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
