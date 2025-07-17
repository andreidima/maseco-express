{{-- resources/views/flotaStatusuri_c/save.blade.php --}}
@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        {{-- Use a truck icon, changing between “plus” and “edit” depending on context --}}
                        <i class="fa-solid fa-truck-{{ isset($masinaValabilitati->id) ? 'edit' : 'plus' }} me-1"></i>
                        {{-- Toggle heading text between “Edit” and “Add” --}}
                        {{ isset($masinaValabilitati->id) ? 'Modificare Mașină' : 'Adăugare Mașină' }}
                    </span>
                </div>

                {{-- Display validation errors --}}
                @include ('errors')

                <div class="card-body py-3 px-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
                    <form class="needs-validation" novalidate
                          method="POST"
                          action="{{ isset($masinaValabilitati->id)
                                      ? route('masini-valabilitati.update', $masinaValabilitati->id)
                                      : route('masini-valabilitati.store') }}">
                        @csrf
                        {{-- When editing, spoof the PUT method --}}
                        @if(isset($masinaValabilitati->id))
                            @method('PUT')
                        @endif

                        {{-- Include the shared form partial and pass the existing model if available --}}
                        @include ('masiniValabilitati.form', [
                            'masinaValabilitati' => $masinaValabilitati ?? null,
                            'buttonText'   => isset($masinaValabilitati->id) ? 'Salvează modificările' : 'Adaugă Mașină'
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
