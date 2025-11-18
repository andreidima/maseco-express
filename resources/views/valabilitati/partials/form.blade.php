@php
    $valabilitate = $valabilitate ?? null;
    $method = strtoupper($method ?? 'POST');
    $submitLabel = $submitLabel ?? 'Salvează';
    $formAction = $action ?? '';
    $soferi = $soferi ?? [];
    $divizii = $divizii ?? [];
@endphp

<form action="{{ $formAction }}" method="POST" class="needs-validation" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="valabilitate-divizie" class="form-label">Divizie<span class="text-danger">*</span></label>
            <select
                name="divizie_id"
                id="valabilitate-divizie"
                class="form-select bg-white rounded-3 @error('divizie_id') is-invalid @enderror"
                required
            >
                <option value="">Selectează divizie</option>
                @foreach ($divizii as $divizieId => $divizieNume)
                    <option value="{{ $divizieId }}" @selected((int) old('divizie_id', optional($valabilitate)->divizie_id) === (int) $divizieId)>
                        {{ $divizieNume }}
                    </option>
                @endforeach
                @if ($valabilitate && $valabilitate->divizie && ! array_key_exists($valabilitate->divizie_id, $divizii))
                    <option value="{{ $valabilitate->divizie_id }}" selected>
                        {{ $valabilitate->divizie->nume }}
                    </option>
                @endif
            </select>
            @error('divizie_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="valabilitate-numar-auto" class="form-label">Număr auto<span class="text-danger">*</span></label>
            <input
                type="text"
                name="numar_auto"
                id="valabilitate-numar-auto"
                class="form-control bg-white rounded-3 @error('numar_auto') is-invalid @enderror"
                value="{{ old('numar_auto', optional($valabilitate)->numar_auto) }}"
                autocomplete="off"
                required
            >
            @error('numar_auto')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="valabilitate-sofer" class="form-label">Șofer<span class="text-danger">*</span></label>
            <select
                name="sofer_id"
                id="valabilitate-sofer"
                class="form-select bg-white rounded-3 @error('sofer_id') is-invalid @enderror"
                required
            >
                <option value="">Selectează șofer</option>
                @foreach ($soferi as $soferId => $soferName)
                    <option value="{{ $soferId }}" @selected((int) old('sofer_id', optional($valabilitate)->sofer_id) === (int) $soferId)>
                        {{ $soferName }}
                    </option>
                @endforeach
                @if ($valabilitate && $valabilitate->sofer && ! array_key_exists($valabilitate->sofer_id, $soferi))
                    <option value="{{ $valabilitate->sofer_id }}" selected>
                        {{ $valabilitate->sofer->name }}
                    </option>
                @endif
            </select>
            @error('sofer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="valabilitate-data-inceput" class="form-label">Data început<span class="text-danger">*</span></label>
            <input
                type="date"
                name="data_inceput"
                id="valabilitate-data-inceput"
                class="form-control bg-white rounded-3 @error('data_inceput') is-invalid @enderror"
                value="{{ old('data_inceput', optional(optional($valabilitate)->data_inceput)->format('Y-m-d')) }}"
                required
            >
            @error('data_inceput')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label for="valabilitate-data-sfarsit" class="form-label">Data sfârșit</label>
            <input
                type="date"
                name="data_sfarsit"
                id="valabilitate-data-sfarsit"
                class="form-control bg-white rounded-3 @error('data_sfarsit') is-invalid @enderror"
                value="{{ old('data_sfarsit', optional(optional($valabilitate)->data_sfarsit)->format('Y-m-d')) }}"
            >
            @error('data_sfarsit')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @include('valabilitati.partials.road-taxes-form', [
        'entries' => old('taxe_drum', $valabilitate ? $valabilitate->taxeDrum : []),
        'formPrefix' => '',
        'isActive' => true,
    ])

    @include('valabilitati.partials.road-taxes-script')

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ $backUrl ?? route('valabilitati.index') }}" class="btn btn-secondary">Renunță</a>
        <button type="submit" class="btn btn-primary text-white border border-dark rounded-3">
            <i class="fa-solid fa-floppy-disk me-1"></i>{{ $submitLabel }}
        </button>
    </div>
</form>
