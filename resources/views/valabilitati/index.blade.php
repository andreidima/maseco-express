@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-1 mb-2">
            <span class="badge culoare1 fs-5">
                <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                    <span><i class="fa-solid fa-calendar-check me-1"></i>Valabilități</span>
                    <span class="ms-4">flotă</span>
                </span>
            </span>
        </div>
        <div class="col-lg-8 mb-0" id="formularValabilitati">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row gy-1 gx-4 mb-2 custom-search-form d-flex justify-content-center align-items-end">
                    <div class="col-lg-2 col-md-6">
                        <select name="status" id="filter-status" class="form-select bg-white rounded-3">
                            <option value="toate" @selected($filters['status'] === 'toate')>Toate</option>
                            <option value="active" @selected($filters['status'] === 'active')>Active ({{ $statusCounts['active'] ?? 0 }})</option>
                            <option value="expirate" @selected($filters['status'] === 'expirate')>Expirate ({{ $statusCounts['expirate'] ?? 0 }})</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-book text-muted" title="Caută după denumire"></i>
                            <input
                                type="text"
                                class="form-control rounded-3 flex-grow-1"
                                id="filter-denumire"
                                name="denumire"
                                placeholder="Denumire valabilitate"
                                value="{{ $filters['denumire'] }}"
                                list="filter-denumire-suggestions"
                                autocomplete="off"
                            >
                        </div>
                        <datalist id="filter-denumire-suggestions">
                            @foreach ($denumiri as $denumire)
                                <option value="{{ $denumire }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user text-muted" title="Caută după șofer"></i>
                            <input
                                type="text"
                                class="form-control rounded-3 flex-grow-1"
                                id="filter-sofer"
                                name="sofer"
                                placeholder="Șofer"
                                value="{{ $filters['sofer'] }}"
                                list="filter-sofer-suggestions"
                                autocomplete="off"
                            >
                        </div>
                        <datalist id="filter-sofer-suggestions">
                            @foreach ($soferi as $sofer)
                                <option value="{{ $sofer }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-truck text-muted" title="Caută după număr auto"></i>
                            <input
                                type="text"
                                class="form-control rounded-3 flex-grow-1"
                                id="filter-numar-auto"
                                name="numar_auto"
                                placeholder="Număr auto"
                                value="{{ $filters['numar_auto'] }}"
                                list="filter-numar-auto-suggestions"
                                autocomplete="off"
                            >
                        </div>
                        <datalist id="filter-numar-auto-suggestions">
                            @foreach ($numereAuto as $numarAuto)
                                <option value="{{ $numarAuto }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="filter-inceput-de-la" class="form-label ps-2 small text-muted mb-0">Început de la</label>
                        <input type="date" class="form-control rounded-3" id="filter-inceput-de-la" name="inceput_de_la" value="{{ $filters['inceput_de_la'] }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="filter-inceput-pana" class="form-label ps-2 small text-muted mb-0">Început până la</label>
                        <input type="date" class="form-control rounded-3" id="filter-inceput-pana" name="inceput_pana_la" value="{{ $filters['inceput_pana_la'] }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="filter-sfarsit-de-la" class="form-label ps-2 small text-muted mb-0">Sfârșit de la</label>
                        <input type="date" class="form-control rounded-3" id="filter-sfarsit-de-la" name="sfarsit_de_la" value="{{ $filters['sfarsit_de_la'] }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label for="filter-sfarsit-pana" class="form-label ps-2 small text-muted mb-0">Sfârșit până la</label>
                        <input type="date" class="form-control rounded-3" id="filter-sfarsit-pana" name="sfarsit_pana_la" value="{{ $filters['sfarsit_pana_la'] }}">
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <div class="form-check ps-0 ms-1">
                            <input class="form-check-input ms-2" type="checkbox" value="1" id="filter-fara-sfarsit" name="fara_sfarsit" @checked($filters['fara_sfarsit'])>
                            <label class="form-check-label ms-4" for="filter-fara-sfarsit">
                                Doar fără sfârșit
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('valabilitati.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                @include('partials.operations-navigation')
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>Denumire</th>
                        <th>Număr auto</th>
                        <th>Șofer</th>
                        <th>Început</th>
                        <th>Sfârșit</th>
                        <th>Status</th>
                        <th class="text-end">Zile rămase</th>
                    </tr>
                </thead>
                <tbody id="valabilitati-table-body">
                    @if ($valabilitati->count())
                        @include('valabilitati.partials.rows', ['valabilitati' => $valabilitati])
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-4">Nu există valabilități care să respecte criteriile selectate.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($valabilitati instanceof \Illuminate\Contracts\Pagination\Paginator || $valabilitati instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div id="valabilitati-infinite-scroll" class="mt-3 px-3">
                <div
                    id="valabilitati-load-more-trigger"
                    class="d-flex justify-content-center py-3"
                    data-next-url="{{ $nextPageUrl }}"
                >
                    @if ($valabilitati->hasMorePages())
                        <button type="button" class="btn btn-outline-primary" id="valabilitati-load-more">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="load-more-label">Încarcă mai multe</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loadMoreButton = document.getElementById('valabilitati-load-more');
            const loadMoreTrigger = document.getElementById('valabilitati-load-more-trigger');
            const tableBody = document.getElementById('valabilitati-table-body');
            const loadMoreSpinner = loadMoreButton ? loadMoreButton.querySelector('.spinner-border') : null;
            const loadMoreLabel = loadMoreButton ? loadMoreButton.querySelector('.load-more-label') : null;

            const loadState = {
                loading: false,
                nextUrl: loadMoreTrigger ? loadMoreTrigger.dataset.nextUrl : null,
                observer: null,
            };

            const setLoadingState = (isLoading) => {
                loadState.loading = isLoading;

                if (!loadMoreButton) {
                    return;
                }

                if (isLoading) {
                    loadMoreButton.disabled = true;
                    if (loadMoreSpinner) {
                        loadMoreSpinner.classList.remove('d-none');
                    }
                    if (loadMoreLabel) {
                        loadMoreLabel.textContent = 'Se încarcă...';
                    }
                } else {
                    loadMoreButton.disabled = false;
                    if (loadMoreSpinner) {
                        loadMoreSpinner.classList.add('d-none');
                    }
                    if (loadMoreLabel) {
                        loadMoreLabel.textContent = 'Încarcă mai multe';
                    }
                }
            };

            const appendHtml = (container, html) => {
                if (!container || !html) {
                    return;
                }

                const template = document.createElement('template');
                template.innerHTML = html.trim();
                container.appendChild(template.content);
            };

            const handleLoadMore = () => {
                if (!loadState.nextUrl || loadState.loading) {
                    return;
                }

                setLoadingState(true);

                fetch(loadState.nextUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Solicitarea a eșuat.');
                        }

                        return response.json();
                    })
                    .then(data => {
                        if (data.rows_html) {
                            appendHtml(tableBody, data.rows_html);
                        }

                        loadState.nextUrl = data.next_url || null;

                        if (loadMoreTrigger) {
                            loadMoreTrigger.dataset.nextUrl = loadState.nextUrl || '';
                        }

                        if (loadMoreButton) {
                            loadMoreButton.classList.remove('btn-danger');
                        }

                        if (!loadState.nextUrl && loadMoreButton) {
                            loadMoreButton.remove();
                        }

                        if (!loadState.nextUrl && loadState.observer) {
                            loadState.observer.disconnect();
                        }

                        setLoadingState(false);
                    })
                    .catch(() => {
                        if (loadMoreButton) {
                            loadMoreButton.classList.add('btn-danger');
                        }

                        if (loadMoreLabel) {
                            loadMoreLabel.textContent = 'A apărut o eroare. Reîncearcă';
                        }

                        setLoadingState(false);
                    });
            };

            if (loadMoreButton) {
                loadMoreButton.addEventListener('click', () => {
                    handleLoadMore();
                });
            }

            if ('IntersectionObserver' in window && loadMoreTrigger) {
                loadState.observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            handleLoadMore();
                        }
                    });
                }, {
                    root: null,
                    rootMargin: '0px 0px 200px 0px',
                });

                loadState.observer.observe(loadMoreTrigger);
            }
        });
    </script>
@endpush
@endsection
