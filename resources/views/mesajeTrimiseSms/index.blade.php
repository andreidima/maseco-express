@extends ('layouts.app')

@section('content')
<div class="container card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fas fa-sms me-1"></i>SMS trimise
                </span>
            </div>

            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="/mesaje-trimise-sms">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-6">
                            <input type="text" class="form-control rounded-3" id="search_transportator_contract" name="search_transportator_contract" placeholder="Contract" value="{{ $search_transportator_contract }}">
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control rounded-3" id="search_telefon" name="search_telefon" placeholder="Telefon" value="{{ $search_telefon }}">
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                        <a class="btn btn-sm bg-secondary text-white col-md-4 border border-dark rounded-3" href="/mesaje-trimise-sms" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                        </a>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-end">
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th style="">#</th>
                            <th class="" style="">Contract</th>
                            <th style="">Telefon SMS</th>
                            <th class="text-center">Mesaj</th>
                            <th class="text-center">Trimis</th>
                            <th class="text-center">Mesaj success/ eroare</th>
                            <th class="text-right">Data trimitere</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mesaje_sms as $mesaj_sms)
                            <tr>
                                <td align="">
                                    {{ ($mesaje_sms ->currentpage()-1) * $mesaje_sms ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $mesaj_sms->comanda->transportator_contract ?? '' }}
                                </td>
                                <td class="">
                                    {{ $mesaj_sms->telefon ?? '' }}
                                </td>
                                <td class="">
                                    {{ $mesaj_sms->mesaj }}
                                </td>
                                <td class="text-center">
                                    @if ($mesaj_sms->trimis === 1)
                                        <span class="text-success">DA</span>
                                    @else
                                        <span class="text-danger">NU</span>
                                    @endif
                                </td>
                                <td class="">
                                    {{ $mesaj_sms->raspuns }}
                                </td>
                                <td class="text-right">
                                    {{ \Carbon\Carbon::parse($mesaj_sms->created_at)->isoFormat('HH:mm - DD.MM.YYYY') ?? '' }}
                                </td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit rezervări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>

            <nav>
                <ul class="pagination justify-content-center">
                    {{$mesaje_sms->appends(Request::except('page'))->links()}}
                </ul>
            </nav>
        </div>
    </div>

@endsection
