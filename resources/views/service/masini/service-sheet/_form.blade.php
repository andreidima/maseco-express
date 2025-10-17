@php
    $sheet = $sheet ?? null;
    $isEdit = (bool) $sheet;
    $formAction = $formAction ?? ($isEdit ? route('service-masini.sheet.update', [$masina, $sheet]) : route('service-masini.sheet.store', $masina));
    $httpMethod = $httpMethod ?? ($isEdit ? 'PUT' : 'POST');
    $formTitle = $formTitle ?? ($isEdit ? 'Editează foaia de service' : 'Foaie service');
    $submitLabel = $submitLabel ?? ($isEdit ? 'Salvează modificările' : 'Generează PDF');
    $submitIcon = $submitIcon ?? ($isEdit ? 'fa-floppy-disk' : 'fa-file-arrow-down');
    $downloadUrl = $downloadUrl ?? ($isEdit ? route('service-masini.sheet.download', [$masina, $sheet]) : null);

    $rawItems = old('items');
    if (is_array($rawItems) && count($rawItems) > 0) {
        $items = array_values($rawItems);
    } elseif ($isEdit) {
        $items = $sheet->items
            ->sortBy('position')
            ->map(fn ($item) => [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
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
                'description' => $description,
                'quantity' => '',
                'notes' => '',
            ])
            ->all();
    }

    $kmValue = old('km_bord', $isEdit ? $sheet->km_bord : '');
    $dateValue = old('data_service', $isEdit ? optional($sheet->data_service)->toDateString() : now()->toDateString());
@endphp

<div class="mx-3 px-3 card mx-auto" style="border-radius: 40px; max-width: 1000px;">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"
        style="border-radius: 40px 40px 0 0;">
        <div>
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-lines me-1"></i>{{ $formTitle }}
            </span>
            <div class="mt-2">
                <h5 class="mb-0">{{ $masina->denumire }}</h5>
                <small class="text-muted">Nr. înmatriculare: {{ $masina->numar_inmatriculare }}</small>
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

                <div id="service-items" class="d-flex flex-column gap-3">
                    @foreach ($items as $index => $item)
                        <div class="card border-0 shadow-sm service-item-row">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-6">
                                        <label for="items_{{ $index }}_description" class="form-label small text-muted mb-1">Descriere intervenție</label>
                                        <input type="text" class="form-control rounded-3"
                                            id="items_{{ $index }}_description"
                                            name="items[{{ $index }}][description]"
                                            value="{{ $item['description'] ?? '' }}" placeholder="Ex: Schimb ulei" required>
                                        @error('items.' . $index . '.description')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="items_{{ $index }}_quantity" class="form-label small text-muted mb-1">Cantitate</label>
                                        <input type="text" class="form-control rounded-3"
                                            id="items_{{ $index }}_quantity"
                                            name="items[{{ $index }}][quantity]"
                                            value="{{ $item['quantity'] ?? '' }}" placeholder="Ex: 2 buc">
                                        @error('items.' . $index . '.quantity')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-lg-3">
                                        <label for="items_{{ $index }}_notes" class="form-label small text-muted mb-1">Observații / manoperă</label>
                                        <input type="text" class="form-control rounded-3"
                                            id="items_{{ $index }}_notes"
                                            name="items[{{ $index }}][notes]"
                                            value="{{ $item['notes'] ?? '' }}" placeholder="Ex: Manoperă 1h">
                                        @error('items.' . $index . '.notes')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-link text-danger text-decoration-none px-0" data-remove-item>
                                            <i class="fa-solid fa-trash-can me-1"></i>Elimină rândul
                                        </button>
                                    </div>
                                </div>
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
    <div class="card border-0 shadow-sm service-item-row">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-6">
                    <label class="form-label small text-muted mb-1" data-for="description">Descriere intervenție</label>
                    <input type="text" class="form-control rounded-3" data-name="description"
                        placeholder="Ex: Schimb ulei" required>
                </div>
                <div class="col-lg-3">
                    <label class="form-label small text-muted mb-1" data-for="quantity">Cantitate</label>
                    <input type="text" class="form-control rounded-3" data-name="quantity" placeholder="Ex: 2 buc">
                </div>
                <div class="col-lg-3">
                    <label class="form-label small text-muted mb-1" data-for="notes">Observații / manoperă</label>
                    <input type="text" class="form-control rounded-3" data-name="notes" placeholder="Ex: Manoperă 1h">
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-link text-danger text-decoration-none px-0" data-remove-item>
                        <i class="fa-solid fa-trash-can me-1"></i>Elimină rândul
                    </button>
                </div>
            </div>
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
