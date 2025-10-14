@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-truck me-1"></i>Flotă statusuri C
            </span>
        </div>
        <div class="col-lg-6">
        </div>
        <div class="col-lg-3 text-end">
            {{-- “Create” button --}}
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8"
               href="{{ route('flota-statusuri-c.create') }}"
               role="button">
                <i class="fas fa-plus-square text-white me-1"></i>Adaugă status C
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
                        <th class="text-center">Dimenssions</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Out of EU</th>
                        <th class="text-center">Info I</th>
                        <th class="text-center">Info II</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($flotaStatusuriC as $statusC)
                        <tr>
                            <td>
                                {{ ($flotaStatusuriC->currentPage() - 1) * $flotaStatusuriC->perPage() + $loop->index + 1 }}
                            </td>
                            <td class="text-center"
                                style="background-color: {{ $statusC->color ?? '' }};">
                                {{ $statusC->nr_auto }}
                            </td>
                            <td class="text-center">
                                {{ $statusC->dimenssions }}
                            </td>
                            <td class="text-center">
                                {{ $statusC->type }}
                            </td>
                            <td class="text-center">
                                {{ $statusC->out_of_eu }}
                            </td>
                            <td class="text-center">
                                {{ $statusC->info_i }}
                            </td>
                            <td class="text-center">
                                {{ $statusC->info_ii }}
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    {{-- Edit button --}}
                                    <a href="{{ route('flota-statusuri-c.edit', $statusC) }}"
                                       class="flex me-1">
                                        <span class="badge bg-primary">Modifică</span>
                                    </a>

                                    {{-- Delete button triggers a modal --}}
                                    <div>
                                        <a href="#"
                                           data-bs-toggle="modal"
                                           data-bs-target="#stergeStatusC{{ $statusC->id }}"
                                           title="Șterge Status C">
                                            <span class="badge bg-danger">Șterge</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Nu există statusuri C înregistrate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $flotaStatusuriC->appends(request()->except('page'))->links() }}
        </div>
    </div>
</div>

{{-- Modal pentru ștergerea fiecărui status C --}}
@foreach ($flotaStatusuriC as $statusC)
    <div class="modal fade text-dark"
         id="stergeStatusC{{ $statusC->id }}"
         tabindex="-1"
         role="dialog"
         aria-labelledby="stergeModalLabel{{ $statusC->id }}"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeModalLabel{{ $statusC->id }}">
                        Status C: <b>{{ $statusC->nr_auto }}</b>
                    </h5>
                    <button type="button"
                            class="btn-close bg-white"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body text-start">
                    Ești sigur că vrei să ștergi acest Status C?
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Renunță</button>

                    <form method="POST"
                          action="{{ route('flota-statusuri-c.destroy', $statusC) }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit"
                                class="btn btn-danger text-white">
                            Șterge Status C
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
