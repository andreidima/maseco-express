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
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-numar-auto" name="numar_auto" placeholder="Număr auto" value="{{ $filters['numar_auto'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-sofer" name="sofer" placeholder="Șofer" value="{{ $filters['sofer'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-denumire" name="denumire" placeholder="Denumire" value="{{ $filters['denumire'] }}">
                    </div>
                </div>
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="date" class="form-control rounded-3" id="filter-interval-start" name="interval_start" value="{{ $filters['interval_start'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="date" class="form-control rounded-3" id="filter-interval-end" name="interval_end" value="{{ $filters['interval_end'] }}">
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                        <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ route('valabilitati.index') }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează
                        </a>
                    </div>
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
