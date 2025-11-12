@extends('layouts.driver')

@section('title', __('Zona șoferului'))

@section('content')
    <div
        id="driver-app"
        data-valabilitati-endpoint="{{ route('driver.api.valabilitati.index') }}"
        data-localitati-endpoint="{{ route('driver.api.localitati.index') }}"
        data-tari='@json($tari)'
        data-romania-id="{{ $romaniaId ?? '' }}"
        data-initial-valabilitati='@json($valabilitati)'
        class="driver-shell"
    ></div>
@endsection
