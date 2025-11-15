@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-calendar-check me-1"></i>Valabilitate
            </span>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-end align-items-stretch align-items-lg-center gap-2">
                <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                    <i class="fas fa-arrow-left text-white me-1"></i>Înapoi
                </a>
                <button
                    type="button"
                    class="btn btn-sm btn-primary text-white border border-dark rounded-3"
                    data-bs-toggle="modal"
                    data-bs-target="#valabilitateEditModal{{ $valabilitate->id }}"
                >
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i>Modifică
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-danger text-white border border-dark rounded-3"
                    data-bs-toggle="modal"
                    data-bs-target="#valabilitateDeleteModal{{ $valabilitate->id }}"
                >
                    <i class="fa-solid fa-trash-can text-white me-1"></i>Șterge
                </button>
            </div>
        </div>
    </div>

    <div class="card-body py-4">
        @include('errors')

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Informații generale</h6>
                    <p class="mb-1"><strong>Denumire:</strong> {{ $valabilitate->denumire }}</p>
                    <p class="mb-1"><strong>Număr auto:</strong> {{ $valabilitate->numar_auto }}</p>
                    <p class="mb-0"><strong>Șofer:</strong> {{ $valabilitate->sofer->name ?? '—' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Perioadă</h6>
                    <p class="mb-1">
                        <strong>Început:</strong>
                        {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                    </p>
                    <p class="mb-1">
                        <strong>Sfârșit:</strong>
                        {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? '—' }}
                    </p>
                    @php($azi = now()->startOfDay())
                    @php($isActive = is_null($valabilitate->data_sfarsit) || optional($valabilitate->data_sfarsit)->greaterThanOrEqualTo($azi))
                    <p class="mb-0">
                        <strong>Status:</strong>
                        <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                            {{ $isActive ? 'Activă' : 'Expirată' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="border rounded-3 p-3">
                <h6 class="text-muted text-uppercase mb-2">Taxe de drum</h6>

                @if ($valabilitate->taxeDrum->isEmpty())
                    <p class="mb-0 text-muted">Nu există taxe de drum înregistrate.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nume</th>
                                    <th>Țară</th>
                                    <th class="text-end">Sumă</th>
                                    <th>Monedă</th>
                                    <th>Dată</th>
                                    <th>Observații</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($valabilitate->taxeDrum as $taxa)
                                    <tr>
                                        <td>{{ $taxa->nume ?? '—' }}</td>
                                        <td>{{ $taxa->tara }}</td>
                                        <td class="text-end">{{ number_format((float) $taxa->suma, 2, ',', '.') }}</td>
                                        <td>{{ $taxa->moneda }}</td>
                                        <td>{{ optional($taxa->data)->format('d.m.Y') ?? '—' }}</td>
                                        <td>{{ $taxa->observatii ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div
    id="valabilitati-modals"
    data-active-modal="{{ session('valabilitati.modal') }}"
>
    @include('valabilitati.partials.modals', [
        'valabilitati' => collect([$valabilitate]),
        'soferi' => $soferi,
        'includeCreate' => false,
        'formType' => old('form_type'),
        'formId' => old('form_id'),
    ])
</div>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('valabilitati-modals');
            if (!container) {
                return;
            }

            const activeModal = container.dataset.activeModal;
            if (!activeModal) {
                return;
            }

            let modalId = null;

            if (activeModal === 'create') {
                modalId = 'valabilitateCreateModal';
            } else if (activeModal.startsWith('edit:')) {
                const parts = activeModal.split(':');
                if (parts.length === 2 && parts[1]) {
                    modalId = `valabilitateEditModal${parts[1]}`;
                }
            }

            if (!modalId) {
                return;
            }

            const modalElement = document.getElementById(modalId);
            if (!modalElement) {
                return;
            }

            const bootstrap = window.bootstrap;
            const bootstrapModal = bootstrap && bootstrap.Modal ? bootstrap.Modal : null;

            if (bootstrapModal) {
                const instance =
                    typeof bootstrapModal.getOrCreateInstance === 'function'
                        ? bootstrapModal.getOrCreateInstance(modalElement)
                        : new bootstrapModal(modalElement);
                instance.show();
                return;
            }

            const $ = window.jQuery || window.$;
            if (typeof $ === 'function' && typeof $(modalElement).modal === 'function') {
                $(modalElement).modal('show');
                return;
            }

            modalElement.classList.add('show');
            modalElement.removeAttribute('aria-hidden');
        });
    </script>
@endpush
@endsection
