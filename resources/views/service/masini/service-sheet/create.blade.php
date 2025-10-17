@extends('layouts.app')

@section('content')
    @include('service.masini.service-sheet._form', [
        'masina' => $masina,
    ])
@endsection
