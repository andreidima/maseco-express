@foreach ($curse as $cursa)
    @php
        $dataCursa = $cursa->data_cursa?->format('d.m.Y H:i');
    @endphp
    @php
        $canMoveUp = ! $loop->first;
        $canMoveDown = ! $loop->last;
        $hasMultipleCurse = $loop->count > 1;
    @endphp
    <tr>
        <td class="text-center fw-semibold">
            <div class="d-inline-flex align-items-center gap-2">
                <span>#{{ $cursa->nr_ordine }}</span>
                @if ($hasMultipleCurse)
                    <div class="d-flex flex-column gap-1">
                        <form
                            method="POST"
                            action="{{ route('valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                            class="mb-0"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-secondary p-1"
                                title="Mută cursa mai sus"
                                @disabled(! $canMoveUp)
                            >
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                        </form>
                        <form
                            method="POST"
                            action="{{ route('valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                            class="mb-0"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-secondary p-1"
                                title="Mută cursa mai jos"
                                @disabled(! $canMoveDown)
                            >
                                <i class="fa-solid fa-arrow-down"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </td>
        <td class="text-nowrap">{{ $cursa->nr_cursa ?: '—' }}</td>
        <td>{{ $cursa->incarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->incarcare_cod_postal ?: '—' }}</td>
        <td>{{ $cursa->incarcareTara?->nume ?: '—' }}</td>
        <td>{{ $cursa->descarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->descarcare_cod_postal ?: '—' }}</td>
        <td>{{ $cursa->descarcareTara?->nume ?: '—' }}</td>
        <td class="text-nowrap">{{ $dataCursa ?: '—' }}</td>
        <td class="text-nowrap">{{ $cursa->km_bord_incarcare !== null ? $cursa->km_bord_incarcare : '—' }}</td>
        <td class="text-nowrap">{{ $cursa->km_bord_descarcare !== null ? $cursa->km_bord_descarcare : '—' }}</td>
        <td class="text-end">
            <div class="d-flex flex-wrap justify-content-end">
                <div class="ms-1">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaEditModal{{ $cursa->id }}"
                        class="flex"
                        title="Modifică cursa"
                    >
                        <span class="badge bg-primary">Modifică</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaDeleteModal{{ $cursa->id }}"
                        class="flex"
                        title="Șterge cursa"
                    >
                        <span class="badge bg-danger">Șterge</span>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
