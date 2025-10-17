@php
    $sheet = $sheet ?? null;
    $isEdit = (bool) $sheet;
    $formAction = $formAction ?? ($isEdit ? route('service-masini.sheet.update', [$masina, $sheet]) : route('service-masini.sheet.store', $masina));
    $httpMethod = $httpMethod ?? ($isEdit ? 'PUT' : 'POST');
    $formTitle = $formTitle ?? ($isEdit ? 'Editează foaia de service' : 'Foaie service');
    $submitLabel = $submitLabel ?? ($isEdit ? 'Salvează modificările' : 'Salvează foaia');
    $submitIcon = $submitIcon ?? 'fa-floppy-disk';
    $downloadUrl = $downloadUrl ?? ($isEdit ? route('service-masini.sheet.download', [$masina, $sheet]) : null);

    $rawItems = old('items');
    if (is_array($rawItems) && count($rawItems) > 0) {
        $items = array_values($rawItems);
    } elseif ($isEdit) {
        $items = $sheet->items
            ->sortBy('position')
            ->map(fn ($item) => [
                'descriere' => $item->descriere,
            ])
            ->values()
            ->toArray();
    } else {
        $defaultDescriptions = [
            'schimb ulei + filtre',
            'verificare lumini',
            'verificare placute frana si gresat culisoare',
            'verificare jocuri roti',
        ];

        $items = collect($defaultDescriptions)
            ->map(fn ($description) => [
                'descriere' => $description,
            ])
            ->all();
    }

    $kmValue = old('km_bord', $isEdit ? $sheet->km_bord : '');
    $dateValue = old('data_service', $isEdit ? optional($sheet->data_service)->toDateString() : now()->toDateString());
@endphp

<div class="mx-3 px-3 card mx-auto" style="border-radius: 40px; max-width: 1000px;">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3"
        style="border-radius: 40px 40px 0 0;">
        <div class="d-flex flex-column gap-2">
            <span class="badge culoare1 fs-5 align-self-start">
                <i class="fa-solid fa-file-lines me-1"></i>{{ $formTitle }}
            </span>
            <div>
                <div class="fw-semibold">{{ $masina->numar_inmatriculare }}</div>
                <small class="text-muted">{{ $masina->denumire }}</small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($downloadUrl)
                <a href="{{ $downloadUrl }}" class="btn btn-outline-primary btn-sm rounded-3">
                    <i class="fa-solid fa-file-arrow-down me-1"></i>Descarcă PDF
                </a>
            @endif
            <a href="{{ route('service-masini.index', ['masina_id' => $masina->id, 'view' => 'service-sheets']) }}"
                class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la service mașini
            </a>
        </div>
    </div>

    <div class="card-body">
        @include('errors')

        <form method="POST" action="{{ $formAction }}" id="service-sheet-form" class="d-flex flex-column gap-4">
            @csrf
            @if ($httpMethod !== 'POST')
                @method($httpMethod)
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="km_bord" class="form-label small text-muted mb-1">Km bord</label>
                    <input type="number" min="0" class="form-control rounded-3" id="km_bord" name="km_bord"
                        value="{{ $kmValue }}" placeholder="Ex: 125000" required>
                    @error('km_bord')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="data_service" class="form-label small text-muted mb-1">Data service</label>
                    <input type="date" class="form-control rounded-3" id="data_service" name="data_service"
                        value="{{ $dateValue }}" required>
                    @error('data_service')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div>
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <h6 class="mb-0"><i class="fa-solid fa-list-check me-1"></i>Necesar schimb piese și manoperă</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-3" id="add-service-item">
                        <i class="fa-solid fa-plus me-1"></i>Adaugă rând
                    </button>
                </div>

                <div id="service-items" class="d-flex flex-column gap-2">
                    @foreach ($items as $index => $item)
                        <div class="service-item-row border rounded-3 p-3 bg-light">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <label for="items_{{ $index }}_descriere" class="form-label small text-muted mb-1" data-for="descriere">Descriere intervenție</label>
                                    <input type="text" class="form-control form-control-sm rounded-3" data-name="descriere"
                                        id="items_{{ $index }}_descriere"
                                        name="items[{{ $index }}][descriere]"
                                        value="{{ $item['descriere'] ?? '' }}" placeholder="Ex: Schimb ulei" required>
                                    @error('items.' . $index . '.descriere')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="button" class="btn btn-link text-danger p-0 align-self-center" data-remove-item aria-label="Șterge rândul">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('service-masini.index', ['masina_id' => $masina->id, 'view' => 'service-sheets']) }}"
                    class="btn btn-outline-secondary rounded-3">Renunță</a>
                <button type="submit" class="btn btn-primary rounded-3">
                    <i class="fa-solid {{ $submitIcon }} me-1"></i>{{ $submitLabel }}
                </button>
            </div>
        </form>
    </div>
</div>

<template id="service-item-template">
    <div class="service-item-row border rounded-3 p-3 bg-light">
        <div class="d-flex align-items-start gap-2">
            <div class="flex-grow-1">
                <label class="form-label small text-muted mb-1" data-for="descriere">Descriere intervenție</label>
                <input type="text" class="form-control form-control-sm rounded-3" data-name="descriere"
                    placeholder="Ex: Schimb ulei" required>
            </div>
            <button type="button" class="btn btn-link text-danger p-0 align-self-center" data-remove-item aria-label="Șterge rândul">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>
    </div>
</template>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('service-items');
            const template = document.getElementById('service-item-template');
            const addButton = document.getElementById('add-service-item');

            function updateIndices() {
                const rows = container.querySelectorAll('.service-item-row');

                rows.forEach((row, index) => {
                    row.querySelectorAll('[data-name]').forEach((input) => {
                        const field = input.dataset.name;
                        const id = `items_${index}_${field}`;

                        input.name = `items[${index}][${field}]`;
                        input.id = id;
                    });

                    row.querySelectorAll('[data-for]').forEach((label) => {
                        const field = label.dataset.for;
                        label.setAttribute('for', `items_${index}_${field}`);
                    });
                });
            }

            if (addButton) {
                addButton.addEventListener('click', () => {
                    const clone = document.importNode(template.content, true);
                    container.appendChild(clone);
                    updateIndices();
                });
            }

            container.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-item]');

                if (!removeButton) {
                    return;
                }

                const rows = container.querySelectorAll('.service-item-row');

                if (rows.length <= 1) {
                    return;
                }

                const row = removeButton.closest('.service-item-row');

                if (row) {
                    row.remove();
                    updateIndices();
                }
            });

            updateIndices();
        });
    </script>
@endpush
