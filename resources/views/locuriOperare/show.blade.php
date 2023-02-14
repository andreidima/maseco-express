@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="culoare2 border border-secondary p-2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-user me-1"></i>Clienți / {{ $client->nume }}
                    </span>
                </div>

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >

            @include ('errors')

                    <div class="table-responsive col-md-12 mx-auto">
                        <table class="table table-striped table-hover"
                        >
                            <tr>
                                <td class="pe-4">
                                    Creat de
                                </td>
                                <td>
                                    {{ $client->user->name }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Nume
                                </td>
                                <td>
                                    {{ $client->nume }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Telefon
                                </td>
                                <td>
                                    {{ $client->telefon }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Adresa
                                </td>
                                <td>
                                    {{ $client->adresa }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Status
                                </td>
                                <td>
                                    {{ $client->status }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Intrare
                                </td>
                                <td>
                                    {{ $client->intrare ? \Carbon\Carbon::parse($client->intrare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Lansare
                                </td>
                                <td>
                                    {{ $client->lansare ? \Carbon\Carbon::parse($client->lansare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Ofertă preț
                                </td>
                                <td>
                                    {{ $client->oferta_pret }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Avans
                                </td>
                                <td>
                                    {{ $client->avans }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Observații
                                </td>
                                <td>
                                    {{ $client->observatii }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Creat la data:
                                </td>
                                <td>
                                    {{ $client->created_at ? \Carbon\Carbon::parse($client->created_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="pe-4">
                                    Modificat la data:
                                </td>
                                <td>
                                    {{ $client->updated_at ? \Carbon\Carbon::parse($client->updated_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="form-row mb-2 px-2">
                        <div class="col-lg-12 d-flex justify-content-center">
                            <a class="btn btn-secondary text-white rounded-3" href="{{ Session::get('client_return_url') }}">Înapoi</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
