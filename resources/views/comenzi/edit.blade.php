@extends ('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-clipboard-list me-1"></i>Comanda {{ $comanda->transportator_contract }}
                    </span>
                </div>

                @include ('errors')

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >
                    <form  class="needs-validation" novalidate method="POST" action="{{ $comanda->path() }}">
                        @method('PATCH')

                                @include ('comenzi.form', [
                                    'buttonText' => 'Salvează Comanda'
                                ])

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade text-dark" id="adaugareFirmaDinFormularComanda" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
        <div class="modal-header bg-danger">
            <h5 class="modal-title text-white" id="exampleModalLabel">Firma</h5>
            <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="text-align:left;">
            <form class="needs-validation" novalidate method="POST" action="/firme/transportatori">
                @include ('firme.form', [
                    'firma' => new App\Models\Firma,
                    'tipPartener' => 'transportatori',
                    'tari' => App\Models\Tara::select('id', 'nume')->orderBy('nume')->get(),
                    'buttonText' => 'Adaugă Firma'
                ])
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary"
            {{-- data-bs-dismiss="modal" --}}
            @click="adaugaTransportator()"
            >Adaugă Transportator</button>

            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>


        </div>
        </div>
    </div>
</div>


{{-- <div class="modal fade text-dark" id="adaugaLocOperareNou" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
        <div class="modal-header bg-danger">
            <h5 class="modal-title text-white" id="exampleModalLabel">Loc Operare</h5>
            <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="text-align:left;">
            <form class="needs-validation" novalidate method="POST" action="/locuri-operare">
                @include ('locuriOperare.form', [
                    'locOperare' => new App\Models\LocOperare,
                    'buttonText' => 'Adaugă Loc Operare'
                ])
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>


        </div>
        </div>
    </div>
</div> --}}
@endsection
