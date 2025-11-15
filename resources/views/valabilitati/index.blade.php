@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-1 mb-2">
            <span class="badge culoare1 fs-5">
                <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                    <span><i class="fa-solid fa-calendar-check me-1"></i>Valabilități</span>
                </span>
            </span>
        </div>
        <div class="col-lg-10 mb-0" id="formularValabilitati">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row mb-1 custom-search-form justify-content-center align-items-end" id="datePicker">
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-numar-auto" name="numar_auto" placeholder="Număr auto" value="{{ $filters['numar_auto'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-sofer" name="sofer" placeholder="Șofer" value="{{ $filters['sofer'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-denumire" name="denumire" placeholder="Denumire" value="{{ $filters['denumire'] }}">
                    </div>
                @php
                    $inceputRangeFilter = implode(',', array_filter([
                        $filters['inceput_start'] ?? null,
                        $filters['inceput_end'] ?? null,
                    ]));
                    $sfarsitRangeFilter = implode(',', array_filter([
                        $filters['sfarsit_start'] ?? null,
                        $filters['sfarsit_end'] ?? null,
                    ]));
                @endphp
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label for="filter-interval-inceput" class="form-label mb-0 ps-3">Început</label>
                        <vue-datepicker-next
                            id="filter-interval-inceput"
                            data-veche="{{ $inceputRangeFilter }}"
                            tip="date"
                            value-type="YYYY-MM-DD"
                            format="DD.MM.YYYY"
                            :latime="{ width: '100%' }"
                            :range="true"
                            range-start-name="inceput_start"
                            range-end-name="inceput_end"
                        ></vue-datepicker-next>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label for="filter-interval-sfarsit" class="form-label mb-0 ps-3">Sfârșit</label>
                        <vue-datepicker-next
                            id="filter-interval-sfarsit"
                            data-veche="{{ $sfarsitRangeFilter }}"
                            tip="date"
                            value-type="YYYY-MM-DD"
                            format="DD.MM.YYYY"
                            :latime="{ width: '100%' }"
                            :range="true"
                            range-start-name="sfarsit_start"
                            range-end-name="sfarsit_end"
                        ></vue-datepicker-next>
                    </div>
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
        <div class="col-lg-1 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <a
                    href="{{ route('valabilitati.create') }}"
                    class="btn btn-sm btn-success text-white border border-dark rounded-3"
                >
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă valabilitate
                </a>
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')
        @if (session('status'))
            <div class="alert alert-success mx-3" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mx-3" role="alert">
                {{ session('error') }}
            </div>
        @endif
        <div id="valabilitati-feedback" class="px-3"></div>

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
                        <th class="text-end">Acțiuni</th>
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
            const tableBody = document.getElementById('valabilitati-table-body');
            const feedbackContainer = document.getElementById('valabilitati-feedback');
            const loadMoreButton = document.getElementById('valabilitati-load-more');
            const loadMoreTrigger = document.getElementById('valabilitati-load-more-trigger');
            const spinner = loadMoreButton ? loadMoreButton.querySelector('.spinner-border') : null;
            const label = loadMoreButton ? loadMoreButton.querySelector('.load-more-label') : null;

            const supportsAjax = () => typeof window.fetch === 'function';
            let isLoading = false;
            let observer = null;

            const getNextUrl = () => (loadMoreTrigger ? loadMoreTrigger.dataset.nextUrl || '' : '');

            const setNextUrl = (url) => {
                if (loadMoreTrigger) {
                    loadMoreTrigger.dataset.nextUrl = url || '';
                }
            };

            const setLoadingState = (loading) => {
                if (!loadMoreButton) {
                    return;
                }

                loadMoreButton.disabled = loading;

                if (spinner) {
                    spinner.classList.toggle('d-none', !loading);
                }

                if (label) {
                    label.textContent = loading ? 'Se încarcă...' : 'Încarcă mai multe';
                }
            };

            const clearFeedback = () => {
                if (feedbackContainer) {
                    feedbackContainer.innerHTML = '';
                }
            };

            const showError = (message) => {
                if (!feedbackContainer || !message) {
                    return;
                }

                const alert = document.createElement('div');
                alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alert.setAttribute('role', 'alert');
                alert.textContent = message;

                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn-close';
                closeButton.setAttribute('data-bs-dismiss', 'alert');
                closeButton.setAttribute('aria-label', 'Închide');
                alert.appendChild(closeButton);

                feedbackContainer.innerHTML = '';
                feedbackContainer.appendChild(alert);
            };

            const appendRows = (html) => {
                if (!tableBody || !html) {
                    return;
                }

                const template = document.createElement('template');
                template.innerHTML = html.trim();
                tableBody.appendChild(template.content);
            };

            const disconnectObserver = () => {
                if (observer) {
                    observer.disconnect();
                    observer = null;
                }
            };

            const handleLoadMore = () => {
                if (!supportsAjax() || isLoading) {
                    return;
                }

                const nextUrl = getNextUrl();
                if (!nextUrl) {
                    return;
                }

                isLoading = true;
                setLoadingState(true);
                if (loadMoreButton) {
                    loadMoreButton.classList.remove('btn-danger');
                }

                fetch(nextUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Request failed with status ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        appendRows(data.rows_html || '');
                        setNextUrl(data.next_url || '');
                        clearFeedback();

                        if (!data.next_url && loadMoreButton) {
                            loadMoreButton.remove();
                            disconnectObserver();
                        }
                    })
                    .catch(error => {
                        console.error('Valabilități load more error', error);
                        showError('Nu s-au putut încărca mai multe valabilități. Reîncercați.');
                        if (loadMoreButton) {
                            loadMoreButton.classList.add('btn-danger');
                        }
                    })
                    .finally(() => {
                        isLoading = false;
                        setLoadingState(false);
                    });
            };

            if (loadMoreButton) {
                loadMoreButton.addEventListener('click', handleLoadMore);
            }

            if ('IntersectionObserver' in window && loadMoreTrigger) {
                observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            handleLoadMore();
                        }
                    });
                });

                observer.observe(loadMoreTrigger);
            }
        });
    </script>
@endpush

@include('valabilitati.partials.delete-modal')
@endsection
