@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-word me-1"></i>Documente word
            </span>
        </div>
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-8">
                        <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume" value="{{ $searchNume }}">
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
            @can('documente-word-manage')
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă Document Word
                </a>
            @endcan
        </div>
    </div>

    <div class="card-body px-0 py-3">

        @include ('errors')

        <div class="row">
            @forelse ($documenteWord as $documentWord)
                <div class="col-lg-2 mb-4 text-center">
                    @can('update', $documentWord)
                        <a href="{{ $documentWord->path() }}/modifica" class="flex me-1">
                            <i class="fa-solid fa-file-word fa-3x me-1"></i>
                            <br>
                            {{ $documentWord->nume }}
                            @if ($documentWord->locked_by === auth()->id())
                                <br>
                                <span class="badge bg-warning text-dark">
                                    Blocat de tine<br>
                                    {{ $documentWord->locked_at?->diffForHumans() }}
                                </span>
                            @endif
                        </a>
                    @else
                        <i class="fa-solid fa-file-word fa-3x me-1"></i>
                        <br>
                        {{ $documentWord->nume }}
                        <br>
                        <span class="badge bg-danger">
                            În lucru<br>
                            Blocat de {{ $documentWord->lockedByUser?->name ?? 'cineva' }}<br>
                            {{ $documentWord->locked_at?->diffForHumans() }}
                        </span>
                    @endcan
                    @if($documentWord->locked_by)
                        <br>
                        @can('unlock', $documentWord)
                            <a class="btn btn-sm btn-success rounded-3" href="#"
                                data-bs-toggle="modal"
                                data-bs-target="#unlockDocumentWord{{ $documentWord->id }}"
                                title="Deblochează document word"
                                ><i class="fa-solid fa-lock-open"></i></a>
                        @endcan
                    @endif
                </div>
            @empty
            @endforelse
        </div>

            <div class="d-flex justify-content-center">
                {{$documenteWord->appends(Request::except('page'))->links()}}
            </div>
    </div>
</div>

{{-- Modals to unlock documentWord --}}
@foreach ($documenteWord->whereNotNull('locked_by') as $documentWord)
    <div class="modal fade text-dark" id="unlockDocumentWord{{ $documentWord->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="exampleModalLabel">Document Word: <b>{{ $documentWord->nume }}</b></h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align:left;">
                Ești sigur ca vrei să deblochezi documentul word?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                <a class="btn btn-secondary bg-success rounded-3" href="{{ route('documentWord.unlock', $documentWord->id) }}">
                    Deblochează documentul word
                </a>

            </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
