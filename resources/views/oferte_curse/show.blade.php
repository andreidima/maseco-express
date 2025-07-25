{{-- resources/views/oferte_curse/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="shadow-lg" style="border-radius: 40px;">
        <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0 0;">
          <span class="badge text-light fs-5">
            <i class="fa-solid fa-truck me-1"></i> Detalii Ofertă
          </span>
        </div>

        <div class="card-body p-4 border border-secondary" style="border-radius: 0 0 40px 40px;">
          {{-- Încărcare & Descărcare --}}
          <div class="row">
            <div class="col-md-4 mb-3">
              <strong>Cod poștal încărcare:</strong>
              {{ $oferta->incarcare_cod_postal ?? '-' }}
            </div>
            <div class="col-md-4 mb-3">
              <strong>Localitate încărcare:</strong>
              {{ $oferta->incarcare_localitate ?? '-' }}
            </div>
            <div class="col-md-4 mb-3">
              <strong>Data & ora încărcare:</strong>
              {{ $oferta->incarcare_data_ora ?? '-' }}
            </div>

            <div class="col-md-4 mb-3">
              <strong>Cod poștal descărcare:</strong>
              {{ $oferta->descarcare_cod_postal ?? '-' }}
            </div>
            <div class="col-md-4 mb-3">
              <strong>Localitate descărcare:</strong>
              {{ $oferta->descarcare_localitate ?? '-' }}
            </div>
            <div class="col-md-4 mb-3">
              <strong>Data & ora descărcare:</strong>
              {{ $oferta->descarcare_data_ora ?? '-' }}
            </div>

            <div class="col-12 mb-4">
              <strong>Detalii cursă:</strong><br>
              {!! nl2br(e($oferta->detalii_cursa ?? '-')) !!}
            </div>
          </div>

          <hr>

          {{-- Informații email --}}
          <div class="row">
            <div class="col-md-6 mb-3">
              <strong>Subiect email:</strong>
              {{ $oferta->email_subiect }}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Expeditor:</strong>
              {{ $oferta->email_expeditor }}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Data primirii:</strong>
              {{ \Carbon\Carbon::parse($oferta->data_primirii)->format('d.m.Y H:i') }}
            </div>
            <div class="col-12 mb-3">
              <strong>Link Gmail:</strong>
              <a href="{{ $oferta->gmail_link }}" target="_blank">
                <i class="fa-brands fa-google me-1"></i>Vezi în Gmail
              </a>
            </div>
          </div>

          <hr>

          {{-- Timestamps --}}
          <div class="row">
            <div class="col-md-6 mb-3">
              <strong>Creat la:</strong>
              {{ $oferta->created_at?->format('d.m.Y H:i') }}
            </div>
            <div class="col-md-6 mb-3">
              <strong>Ultima modificare:</strong>
              {{ $oferta->updated_at?->format('d.m.Y H:i') }}
            </div>
          </div>

          {{-- Acțiuni --}}
          <div class="d-flex justify-content-center mt-4">
            <a href="{{ route('oferte-curse.edit', $oferta) }}" class="btn btn-primary me-3 rounded-3">
              <i class="fa-solid fa-edit me-1"></i> Modifică
            </a>
            <a href="{{ Session::get('returnUrl', route('oferte-curse.index')) }}" class="btn btn-secondary rounded-3">
              <i class="fa-solid fa-arrow-left me-1"></i> Înapoi
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
