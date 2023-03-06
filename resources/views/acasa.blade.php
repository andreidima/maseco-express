@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-5">
            <div class="card culoare2">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @include ('errors')

                    Bine ai venit <b>{{ auth()->user()->name ?? '' }}</b>!
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div id="statusuri">
                <div v-if="statusuri" class="table-responsive rounded">
                    <table class="table table-striped table-hover rounded">
                        <thead class="text-white rounded culoare2">
                            <tr>
                                <th colspan="8" class="text-center">Ultimele 50 de statusuri de comenzi - se actualizează în timp real fără să necesite refreshul paginii</th>
                            </tr>
                            <tr class="" style="padding:2rem">
                                <th class="">#</th>
                                <th class="">Comanda</th>
                                <th class="">Transportator</th>
                                <th class="">Persoană de contact</th>
                                <th class="">Telefon</th>
                                <th class="">Status</th>
                                <th class="text-center">Ora</th>
                                <th class="text-center">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(status, index) in statusuri">
                                <td>
                                    @{{ index+1 }}
                                </td>
                                <td>
                                    {{-- @{{ status.comanda.transportator_contract }} --}}
                                    <a :href="'/comenzi/' + (status.comanda ? status.comanda.id : '') + '/modifica'" class="flex me-1">
                                        <span class="badge bg-primary">@{{ status.comanda ? status.comanda.transportator_contract : '' }}</span>
                                    </a>
                                </td>
                                <td>
                                    @{{ (status.comanda ? status.comanda.transportator.nume : '') }}
                                </td>
                                <td>
                                    @{{ (status.comanda ? status.comanda.transportator.persoana_contact : '') }}
                                </td>
                                <td>
                                    @{{ (status.comanda ? status.comanda.transportator.telefon : '') }}
                                </td>
                                <td>
                                    @{{ status.status }}
                                </td>
                                <td class="text-center">
                                    @{{ status.ora }}
                                </td>
                                <td class="text-center">
                                    @{{ status.data }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

