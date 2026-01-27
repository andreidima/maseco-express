@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-truck me-1"></i>Flotă statusuri
                </span>
            </div>
            <div class="col-lg-6">
            </div>
            <div class="col-lg-3 text-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă status
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded mb-5">
                <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="text-center">Nr auto</th>
                            <th class="text-center">Dimenssions</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Abilities</th>
                            <th class="text-center">Info</th>
                            <th class="text-center">Status of the shipment</th>
                            <th class="text-center">Comanda</th>
                            <th class="text-center">Info II</th>
                            <th class="text-center">Info III</th>
                            <th class="text-center">Special info</th>
                            <th class="text-center">E/KM</th>
                            <th class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span>Update</span>
                                    <span
                                        class="text-white-50"
                                        role="button"
                                        tabindex="0"
                                        data-bs-toggle="tooltip"
                                        data-bs-trigger="hover focus"
                                        title="Click stânga: resetare la 60 minute. Click dreapta: setează durata. Cronometrele se sincronizează automat la ~15 secunde pentru performanță. Poți reîmprospăta pagina manual oricând."
                                        aria-label="Informații despre utilizarea cronometrului"
                                    >
                                        <i class="fa-solid fa-circle-info"></i>
                                    </span>
                                </div>
                            </th>
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flotaStatusuri as $status)
                        <tr>
                            <td align="">
                                {{ ($flotaStatusuri ->currentpage()-1) * $flotaStatusuri ->perpage() + $loop->index + 1 }}
                            </td>
                            <td class="text-center" style="color: {{ $status->utilizator->culoare_text ?? '' }}; background-color: {{ $status->utilizator->culoare_background ?? '' }}">
                                {{ $status->nr_auto }}
                            </td>
                            <td class="text-center">
                                {{ $status->dimenssions }}
                            </td>
                            <td class="text-center">
                                {{ $status->type }}
                            </td>
                            <td class="text-center">
                                {{ $status->abilities }}
                            </td>
                            <td class="text-center" style="background-color: {{ $status->info == '1' ? 'yellow' : ($status->info == '2' ? 'orange' : ($status->info == '3' ? 'red' : '')) }}">
                            
                            </td>
                            <td class="text-center">
                                {{ $status->status_of_the_shipment }}
                            </td>
                            <td class="text-center">
                                {{ $status->comanda }}
                            </td>
                            <td class="text-center">
                                {{ $status->info_ii }}
                            </td>
                            <td class="text-center">
                                {{ $status->info_iii }}
                            </td>
                            <td class="text-center">
                                {{ $status->special_info }}
                            </td>
                            <td class="text-center">
                                {{ $status->e_km }}
                            </td>
                            <td
                                class="text-center flota-update-cell is-expired"
                                data-flota-timer
                                data-id="{{ $status->id }}"
                                data-expires-at="{{ $status->update_expires_at?->toIso8601String() ?? '' }}"
                            >
                                <span class="flota-update-label">00:00</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ $status->path() }}/modifica" class="flex me-1">
                                        <span class="badge bg-primary">Modifică</span>
                                    </a>
                                    <div style="flex" class="">
                                        <a
                                            href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#stergeStatus{{ $status->id }}"
                                            title="Șterge Status"
                                            >
                                            <span class="badge bg-danger">Șterge</span>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

                <div class="d-flex justify-content-center">
                    {{$flotaStatusuri->appends(Request::except('page'))->links()}}
                </div>

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge culoare1">Utilizatori</span>
                        <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="/flota-statusuri-utilizatori/adauga" role="button">
                            <i class="fas fa-plus-square text-white me-1"></i>Adauga
                        </a>
                    </div>
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                            <thead class="text-white rounded culoare2">
                                <tr class="" style="padding:2rem">
                                    <th class="text-center">Nume</th>
                                    <th class="text-center">Ordine</th>
                                    <th class="text-end">Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($utilizatori as $utilizator)
                                    <tr>
                                        <td class="text-center" style="color: {{ $utilizator->culoare_text ?? '' }}; background-color: {{ $utilizator->culoare_background ?? '' }}">
                                            {{ $utilizator->nume }}
                                        </td>
                                        <td class="text-center">
                                            {{ $utilizator->ordine_afisare }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ $utilizator->path() }}/modifica" class="flex me-1">
                                                    <span class="badge bg-primary">Modifica</span>
                                                </a>
                                                <div style="flex" class="">
                                                    <a
                                                        href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#stergeUtilizator{{ $utilizator->id }}"
                                                        title="Sterge utilizator"
                                                        >
                                                        <span class="badge bg-danger">Sterge</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <table>
                            <tr><td class="px-1 text-black text-center" style="background-color: yellow">In tranzit, <br>fara cursa dupa descarcare</td></tr>
                            <tr><td class="px-1 text-white text-center" style="background-color: orange">De grupat</td></tr>
                            <tr><td class="px-1 text-white text-center" style="background-color: red">Liber</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="table-responsive rounded text-center">
                        <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                            <thead class="text-white rounded culoare2">
                                <tr class="" style="padding:2rem">
                                    <th class="text-center">MODALITATE DE PLATĂ</th>
                                    <th class="text-center">SPOT</th>
                                    <th class="text-center">TERMEN</th>
                                    <th class="text-center">INFO</th>
                                    <th class="text-center"></th>
                                    <th class="text-end">
                                        <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="/flota-statusuri-informatii/adauga" role="button">
                                            <i class="fas fa-plus-square text-white me-1"></i>Adaugă informație
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flotaStatusuriInformatii as $informatie)
                                    <tr>
                                        <td>
                                            {{ $informatie->modalitate_de_plata }}
                                        </td>
                                        <td>
                                            {{ $informatie->spot }}
                                        </td>
                                        <td>
                                            {{ $informatie->termen }}
                                        </td>
                                        <td>
                                            {{ $informatie->info }}
                                        </td>
                                        <td>
                                            {{ $informatie->info_2 }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ $informatie->path() }}/modifica" class="flex me-1">
                                                    <span class="badge bg-primary">Modifică</span>
                                                </a>
                                                <div style="flex" class="">
                                                    <a
                                                        href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#stergeInformatie{{ $informatie->id }}"
                                                        title="Șterge Informatie"
                                                        >
                                                        <span class="badge bg-danger">Șterge</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- Modalele pentru stergere status --}}
    @foreach ($flotaStatusuri as $status)
        <div class="modal fade text-dark" id="stergeStatus{{ $status->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Status: <b>{{ $status->nr_auto }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur că vrei să ștergi Statusul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $status->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Statusul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere utilizator --}}
    @foreach ($utilizatori as $utilizator)
        <div class="modal fade text-dark" id="stergeUtilizator{{ $utilizator->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Utilizator: <b>{{ $utilizator->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Esti sigur ca vrei sa stergi utilizatorul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>

                    <form method="POST" action="{{ $utilizator->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Sterge utilizatorul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere informatie --}}
    @foreach ($flotaStatusuriInformatii as $informatie)
        <div class="modal fade text-dark" id="stergeInformatie{{ $informatie->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Informație: <b>{{ $informatie->nr_auto }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur că vrei să ștergi Informația?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $informatie->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Informația
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    @push('page-styles')
        <style>
            .flota-update-cell {
                font-weight: 600;
                letter-spacing: 0.02em;
                font-variant-numeric: tabular-nums;
                color: #ffffff !important;
                cursor: pointer;
                user-select: none;
            }

            .flota-update-cell .flota-update-label {
                color: #ffffff !important;
            }

            .flota-update-cell.is-active {
                background-color: #198754;
            }

            .flota-update-cell.is-expired {
                background-color: #dc3545;
            }

            .flota-timer-menu {
                position: fixed;
                z-index: 1080;
                display: none;
                min-width: 220px;
                padding: 0.5rem;
                background: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.15);
            }

            .flota-timer-menu.is-open {
                display: block;
            }

            .flota-timer-menu__title {
                font-weight: 600;
                margin-bottom: 0.5rem;
            }

            .flota-timer-menu__presets {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 0.35rem;
                margin-bottom: 0.5rem;
            }

            .flota-timer-menu__custom {
                display: flex;
                gap: 0.35rem;
                align-items: center;
            }
        </style>
    @endpush

    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const timerCells = Array.from(document.querySelectorAll('[data-flota-timer]'));
                if (!timerCells.length) {
                    return;
                }

                const DEFAULT_MINUTES = 60;
                const MAX_MINUTES = 1440;
                const POLL_MS = 15000;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                const timers = new Map();

                const formatTime = (ms) => {
                    const totalSeconds = Math.max(0, Math.floor(ms / 1000));
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;
                    return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
                };

                const parseExpiry = (value) => {
                    if (!value) {
                        return 0;
                    }
                    const parsed = Date.parse(value);
                    return Number.isNaN(parsed) ? 0 : parsed;
                };

                const updateEntry = (entry, nowMs) => {
                    const remainingMs = entry.expiresAtMs ? entry.expiresAtMs - nowMs : 0;
                    const isExpired = remainingMs <= 0;
                    entry.labelEl.textContent = formatTime(remainingMs);
                    entry.el.classList.toggle('is-expired', isExpired);
                    entry.el.classList.toggle('is-active', !isExpired);
                };

                const setEntry = (entry, expiresAtMs) => {
                    entry.expiresAtMs = expiresAtMs;
                    updateEntry(entry, Date.now());
                };

                const menu = document.createElement('div');
                menu.className = 'flota-timer-menu';
                menu.innerHTML = `
                    <div class="flota-timer-menu__title">Setează update</div>
                    <div class="flota-timer-menu__presets">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-minutes="15">15m</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-minutes="30">30m</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-minutes="60">60m</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-minutes="120">120m</button>
                    </div>
                    <div class="flota-timer-menu__custom">
                        <input type="number" min="1" max="${MAX_MINUTES}" class="form-control form-control-sm" placeholder="Minute">
                        <button type="button" class="btn btn-sm btn-primary" data-action="apply">Setează</button>
                    </div>
                    <div class="invalid-feedback d-block mt-2 d-none" data-error></div>
                `;
                document.body.appendChild(menu);

                const menuInput = menu.querySelector('input');
                const menuError = menu.querySelector('[data-error]');
                let activeEntry = null;

                const setMenuError = (message) => {
                    if (!message) {
                        menuError.textContent = '';
                        menuError.classList.add('d-none');
                        menuInput.classList.remove('is-invalid');
                        return;
                    }
                    menuError.textContent = message;
                    menuError.classList.remove('d-none');
                    menuInput.classList.add('is-invalid');
                };

                const closeMenu = () => {
                    menu.classList.remove('is-open');
                    menu.style.left = '';
                    menu.style.top = '';
                    activeEntry = null;
                    setMenuError('');
                };

                const openMenu = (entry, x, y) => {
                    activeEntry = entry;
                    menu.classList.add('is-open');
                    menu.style.visibility = 'hidden';

                    const rect = menu.getBoundingClientRect();
                    const padding = 8;
                    const left = Math.min(Math.max(padding, x), window.innerWidth - rect.width - padding);
                    const top = Math.min(Math.max(padding, y), window.innerHeight - rect.height - padding);

                    menu.style.left = `${left}px`;
                    menu.style.top = `${top}px`;
                    menu.style.visibility = '';
                    menuInput.value = '';
                    setMenuError('');
                    menuInput.focus();
                };

                const parseMinutes = (value) => {
                    if (value === '' || value === null || typeof value === 'undefined') {
                        return { minutes: null, error: 'Introdu un număr între 1 și 1440 minute.' };
                    }
                    const minutes = Number(value);
                    if (!Number.isFinite(minutes)) {
                        return { minutes: null, error: 'Doar numere întregi sunt permise.' };
                    }
                    if (!Number.isInteger(minutes)) {
                        return { minutes: null, error: 'Doar numere întregi sunt permise.' };
                    }
                    if (minutes < 1 || minutes > MAX_MINUTES) {
                        return { minutes: null, error: 'Valoarea trebuie între 1 și 1440 minute.' };
                    }
                    return { minutes, error: '' };
                };

                menu.addEventListener('click', async (event) => {
                    const preset = event.target.closest('[data-minutes]');
                    if (preset && activeEntry) {
                        const { minutes, error } = parseMinutes(preset.dataset.minutes);
                        if (minutes) {
                            setMenuError('');
                            const ok = await setTimer(activeEntry, minutes, true);
                            if (ok) {
                                closeMenu();
                            }
                        } else if (error) {
                            setMenuError(error);
                        }
                        return;
                    }

                    const apply = event.target.closest('[data-action=\"apply\"]');
                    if (apply && activeEntry) {
                        const { minutes, error } = parseMinutes(menuInput.value);
                        if (!minutes) {
                            setMenuError(error || 'Valoare invalidă.');
                            return;
                        }
                        setMenuError('');
                        const ok = await setTimer(activeEntry, minutes, true);
                        if (ok) {
                            closeMenu();
                        }
                    }
                });

                menuInput.addEventListener('input', () => {
                    setMenuError('');
                });

                document.addEventListener('click', (event) => {
                    if (!menu.classList.contains('is-open')) {
                        return;
                    }
                    if (!menu.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMenu();
                    }
                });

                window.addEventListener('resize', closeMenu);
                window.addEventListener('scroll', closeMenu, true);

                timerCells.forEach((el) => {
                    const id = Number(el.dataset.id);
                    if (!id) {
                        return;
                    }

                    const labelEl = el.querySelector('.flota-update-label') ?? el;
                    const entry = {
                        id,
                        el,
                        labelEl,
                        expiresAtMs: parseExpiry(el.dataset.expiresAt),
                        isUpdating: false,
                    };

                    timers.set(id, entry);
                    updateEntry(entry, Date.now());

                    el.addEventListener('click', () => {
                        setTimer(entry, DEFAULT_MINUTES, false);
                    });

                    el.addEventListener('contextmenu', (event) => {
                        event.preventDefault();
                        openMenu(entry, event.clientX, event.clientY);
                    });
                });

                let lastSeen = null;

                const setTimer = async (entry, minutes, showErrorsInMenu) => {
                    if (entry.isUpdating) {
                        return;
                    }

                    entry.isUpdating = true;
                    const previous = entry.expiresAtMs;
                    const durationMs = (minutes ?? DEFAULT_MINUTES) * 60 * 1000;
                    setEntry(entry, Date.now() + durationMs);

                    try {
                        const response = await fetch(`/flota-statusuri/${entry.id}/timer-reset`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                minutes: minutes ?? DEFAULT_MINUTES,
                            }),
                        });

                        if (!response.ok) {
                            if (response.status === 422) {
                                const data = await response.json().catch(() => ({}));
                                const message = data?.message || 'Valoare invalidă.';
                                if (showErrorsInMenu) {
                                    setMenuError(message);
                                } else {
                                    alert(message);
                                }
                                throw new Error(message);
                            }
                            throw new Error('Request failed');
                        }

                        const data = await response.json();
                        if (data?.update_expires_at) {
                            const parsed = Date.parse(data.update_expires_at);
                            setEntry(entry, Number.isNaN(parsed) ? 0 : parsed);
                        }
                        if (data?.server_now) {
                            lastSeen = data.server_now;
                        }
                    } catch (error) {
                        setEntry(entry, previous || 0);
                        return false;
                    } finally {
                        entry.isUpdating = false;
                    }

                    return true;
                };

                const pollChanges = async () => {
                    try {
                        const params = new URLSearchParams();
                        if (lastSeen) {
                            params.set('since', lastSeen);
                        }

                        const url = `/axios/flota-statusuri-timers${params.toString() ? `?${params.toString()}` : ''}`;
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Request failed');
                        }

                        const data = await response.json();
                        if (data?.now) {
                            lastSeen = data.now;
                        }

                        const payload = Array.isArray(data?.timers) ? data.timers : [];
                        payload.forEach((timer) => {
                            const entry = timers.get(Number(timer.id));
                            if (!entry) {
                                return;
                            }
                            const parsed = timer.update_expires_at ? Date.parse(timer.update_expires_at) : 0;
                            setEntry(entry, Number.isNaN(parsed) ? 0 : parsed);
                        });
                    } catch (error) {
                        // Keep silent to avoid user noise.
                    }
                };

                const tickId = setInterval(() => {
                    const nowMs = Date.now();
                    timers.forEach((entry) => updateEntry(entry, nowMs));
                }, 1000);

                const pollId = setInterval(pollChanges, POLL_MS);
                pollChanges();

                if (typeof bootstrap !== 'undefined') {
                    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
                        new bootstrap.Tooltip(element);
                    });
                }

                window.addEventListener('beforeunload', () => {
                    clearInterval(tickId);
                    clearInterval(pollId);
                });
            });
        </script>
    @endpush

@endsection
