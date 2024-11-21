@extends('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="container">
    <div class="row my-4 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px 40px 40px 40px;">
                <div class="p-2 text-white culoare2" style="border-radius: 40px 40px 0px 0px;">
                    <div class="row d-flex align-items-center">
                        <div class="col-lg-12 mb-0 py-3 d-flex justify-content-center">
                            <img src="{{url('/images/logo.jpg')}}" alt="Logo PDF" height="200px" class="bg-white rounded-3 px-1">
                        </div>
                        <div class="col-lg-12 d-flex justify-content-center" style="">
                            <h3 class="my-2 text-center">
                                @if (isset($comanda))
                                    {{ $comanda->transportator_contract }}
                                @endif
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 border border-0 border-dark" style="border-radius: 0px 0px 40px 40px">
                    @if (isset($comanda))
                        @include('errors')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
