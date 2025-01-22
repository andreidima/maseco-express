@extends ('layouts.app')

@php
    use \Carbon\Carbon;
    // Set the locale to Romanian
    // Carbon::setLocale('ro');
@endphp

@section('content')
<div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-chart-simple me-1"></i>KPI
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-4">
                            <select id="searchMonth" name="searchMonth" class="form-control">
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $m == $searchMonth ? 'selected' : '' }}>
                                        {{ Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <input type="number" id="searchYear" name="searchYear" class="form-control rounded-3" value="{{ request('searchYear', $searchYear) }}" min="2000" max="{{ now()->year }}">
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                        <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                        </a>
                    </div>
                </form>
            </div>
            <div class="col-lg-3 text-end">
                {{-- <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă utilizator
                </a> --}}
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded-3">
                <table class="table table-striped table-hover" id="keyPerformanceIndicatorsMainPage">
                    <thead class="">
                        <tr class="" style="padding:2rem">
                            <th class="culoare2 text-white">#</th>
                            <th class="culoare2 text-white">Utilizator</th>
                            <th class="culoare2 text-center text-white">Pe plus</th>
                            <th class="culoare2 text-center text-white">Pe minus</th>
                            <th class="culoare2 text-center text-white">Pe 0</th>
                            <th class="w-50 culoare2 text-center text-white">Observații</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usersWithKPIAndComanda as $userWithKPIAndComanda)
                            <tr>
                                <td align="">
                                    {{ ($usersWithKPIAndComanda ->currentPage()-1) * $usersWithKPIAndComanda ->perPage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $userWithKPIAndComanda->user_name }}
                                </td>
                                <td class="text-center">
                                    {{ $userWithKPIAndComanda->greater_than_zero }}
                                </td>
                                <td class="text-center">
                                    {{ $userWithKPIAndComanda->less_than_zero }}
                                </td>
                                <td class="text-center">
                                    {{ $userWithKPIAndComanda->equal_to_zero }}
                                </td>
                                <td class="">
                                    <inline-observatii-editor
                                        :kpi-id="{{ $userWithKPIAndComanda->kpi_id }}"
                                        :user-id="{{ $userWithKPIAndComanda->user_id }}"
                                        :observatii="{{ json_encode($userWithKPIAndComanda->kpi_observatii) }}"
                                        :last-updated-kpi="lastUpdatedKpi"
                                        @update-success="handleUpdateSuccess"
                                        :search-month="{{ $searchMonth }}"
                                        :search-year="{{ $searchYear }}"
                                    ></inline-observatii-editor>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$usersWithKPIAndComanda->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

@endsection
