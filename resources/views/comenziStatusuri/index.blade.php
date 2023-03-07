@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-12">
            Sunt afișate ultimele 50 de statusuri de comenzi. Aceste statusuri se actualizează în timp real, fără să fie necesar refreshul paginii
        </div>
    </div>

    <div class="card-body px-0 py-3" id="statusuri">

        @include ('errors')

        <div v-if="statusuri" class="table-responsive rounded">
            <table class="table table-striped table-hover rounded">
                <thead class="text-white rounded culoare2">
                    {{-- <tr>
                        <th colspan="9" class="text-center">Ultimele 50 de statusuri de comenzi - se actualizează în timp real fără să necesite refreshul paginii</th>
                    </tr> --}}
                    <tr class="" style="padding:2rem">
                        <th class="">#</th>
                        <th class="">Comanda</th>
                        <th class="">Transportator</th>
                        <th class="">Client</th>
                        {{-- <th class="">Persoană de contact</th>
                        <th class="">Telefon</th> --}}
                        <th class="">Status</th>
                        <th class="">Mod transmitere</th>
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
                                <span class="badge bg-success">@{{ status.comanda ? status.comanda.transportator_contract : '' }}</span>
                            </a>
                        </td>
                        <td>
                            {{-- @{{ (status.comanda ? status.comanda.transportator.nume : '') }} --}}
                            <a :href="'/firme/transportatori/' + (status.comanda ? status.comanda.transportator.id : '') + '/modifica'" class="flex me-1">
                                <span class="badge bg-primary">@{{ status.comanda ? status.comanda.transportator.nume : '' }}</span>
                            </a>
                        </td>
                        {{-- <td>
                            @{{ (status.comanda ? status.comanda.transportator.persoana_contact : '') }}
                        </td>
                        <td>
                            @{{ (status.comanda ? status.comanda.transportator.telefon : '') }}
                        </td> --}}
                        <td>
                            <a :href="'/firme/clienti/' + (status.comanda ? status.comanda.client.id : '') + '/modifica'" class="flex me-1">
                                <span class="badge bg-primary">@{{ status.comanda ? status.comanda.client.nume : '' }}</span>
                            </a>
                        </td>
                        <td>
                            @{{ status.status }}
                        </td>
                        <td>
                            @{{ status.mod_transmitere }}
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
@endsection
