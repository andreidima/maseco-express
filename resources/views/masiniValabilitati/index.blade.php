@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-truck me-1"></i>Mașini valabilități
            </span>
        </div>
        <div class="col-lg-6">
        </div>
        <div class="col-lg-3 text-end">
            {{-- “Create” button --}}
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8"
               href="{{ route('masini-valabilitati.create') }}"
               role="button">
                <i class="fas fa-plus-square text-white me-1"></i>Adaugă mașină
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded mb-5">
            <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                <thead class="text-white rounded culoare2">
                    <tr style="padding: 2rem">
                        <th>#</th>
                        <th class="text-center">Nr auto</th>
                        <th class="text-center">Nume șofer</th>
                        <th class="text-center">Divizie</th>
                        <th class="text-center">Valabilitate 1</th>
                        <th class="text-center">Observații 1</th>
                        <th class="text-center">Valabilitate 2</th>
                        <th class="text-center">Observații 2</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($masiniValabilitatiGroupedByDivizie as $masiniValabilitati)
                        @forelse ($masiniValabilitati as $masinaValabilitati)
                            <tr>
                                <td>
                                    {{-- {{ ($masiniValabilitati->currentPage() - 1) * $masiniValabilitati->perPage() + $loop->index + 1 }} --}}
                                    {{ $loop->index }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->nr_auto }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->nume_sofer }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->divizie }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->valabilitate_1 }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->observatii_1 }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->valabilitate_2 }}
                                </td>
                                <td class="text-center">
                                    {{ $masinaValabilitati->observatii_2 }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        {{-- Edit button --}}
                                        <a href="{{ route('masini-valabilitati.edit', $masinaValabilitati) }}"
                                        class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>

                                        {{-- Delete button triggers a modal --}}
                                        <div>
                                            <a href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#stergeMasinaValabilitati{{ $masinaValabilitati->id }}"
                                            title="Șterge Mașină">
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                            <tr>
                                <td colspan="9" class="culoare2">

                                </td>
                            </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Nu există mașini înregistrate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        {{-- <nav>
            <ul class="pagination justify-content-center">
                {{ $masiniValabilitati->appends(request()->except('page'))->links() }}
            </ul>
        </nav> --}}
    </div>
</div>

{{-- Modal pentru ștergerea fiecărei mașini --}}
@foreach ($masiniValabilitati as $masinaValabilitati)
    <div class="modal fade text-dark"
         id="stergeMasinaValabilitati{{ $masinaValabilitati->id }}"
         tabindex="-1"
         role="dialog"
         aria-labelledby="stergeModalLabel{{ $masinaValabilitati->id }}"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeModalLabel{{ $masinaValabilitati->id }}">
                        Mașină: <b>{{ $masinaValabilitati->nr_auto }}</b>
                    </h5>
                    <button type="button"
                            class="btn-close bg-white"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    Ești sigur că vrei să ștergi această Mașină?
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Renunță</button>

                    <form method="POST"
                          action="{{ route('masini-valabilitati.destroy', $masinaValabilitati) }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit"
                                class="btn btn-danger text-white">
                            Șterge Mașina
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
