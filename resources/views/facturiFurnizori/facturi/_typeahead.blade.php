<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        const endpoint = '{{ route('facturi-furnizori.facturi.sugestii') }}';
        const inputs = document.querySelectorAll('input[data-typeahead-tip]');

        const cachedInitialSuggestions = new Map();
        const prefetchedTips = new Set();
        const pendingPrefetchControllers = new Map();
        const inputsByTip = new Map();

        const populateDatalist = (datalist, items) => {
            datalist.innerHTML = '';

            if (!Array.isArray(items) || !items.length) {
                return;
            }

            items.forEach(item => {
                const option = document.createElement('option');
                option.value = item;
                datalist.appendChild(option);
            });
        };

        const applyCachedSuggestions = info => {
            const cached = cachedInitialSuggestions.get(info.tip);

            if (!cached || !cached.length) {
                return false;
            }

            populateDatalist(info.datalist, cached);
            return true;
        };

        const ensurePrefetch = tip => {
            if (prefetchedTips.has(tip) || pendingPrefetchControllers.has(tip)) {
                return;
            }

            const controller = new AbortController();
            pendingPrefetchControllers.set(tip, controller);

            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('tip', tip);
            url.searchParams.set('initial', '1');

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                signal: controller.signal,
            })
                .then(response => (response.ok ? response.json() : []))
                .then(items => {
                    if (controller.signal.aborted) {
                        return;
                    }

                    cachedInitialSuggestions.set(tip, Array.isArray(items) ? items : []);
                    prefetchedTips.add(tip);

                    const infos = inputsByTip.get(tip) ?? [];

                    infos.forEach(info => {
                        if (info.input.value.trim().length < info.minLength) {
                            populateDatalist(info.datalist, cachedInitialSuggestions.get(tip));
                        }
                    });
                })
                .catch(() => {
                    if (!controller.signal.aborted) {
                        cachedInitialSuggestions.set(tip, []);
                    }
                })
                .finally(() => {
                    if (pendingPrefetchControllers.get(tip) === controller) {
                        pendingPrefetchControllers.delete(tip);
                    }
                });
        };

        inputs.forEach(input => {
            const datalistId = input.getAttribute('list');
            const datalist = datalistId ? document.getElementById(datalistId) : null;
            const tip = input.dataset.typeaheadTip;
            const minLength = parseInt(input.dataset.typeaheadMinlength ?? '2', 10) || 1;
            const shouldPrefetch = input.dataset.typeaheadPrefetch !== 'false';

            if (!datalist || !tip) {
                return;
            }

            const info = { input, datalist, tip, minLength };
            const group = inputsByTip.get(tip) ?? [];
            group.push(info);
            inputsByTip.set(tip, group);

            if (shouldPrefetch) {
                ensurePrefetch(tip);
            }

            let debounceTimeout = null;
            let activeRequest = null;

            input.addEventListener('focus', () => {
                if (shouldPrefetch) {
                    ensurePrefetch(tip);
                }

                if (input.value.trim().length < minLength) {
                    applyCachedSuggestions(info);
                }
            });

            input.addEventListener('input', () => {
                const query = input.value.trim();

                if (activeRequest) {
                    activeRequest.abort();
                    activeRequest = null;
                }

                if (query.length < minLength) {
                    clearTimeout(debounceTimeout);

                    if (!applyCachedSuggestions(info)) {
                        datalist.innerHTML = '';
                    }

                    return;
                }

                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const controller = new AbortController();
                    activeRequest = controller;

                    const url = new URL(endpoint, window.location.origin);
                    url.searchParams.set('tip', tip);
                    url.searchParams.set('q', query);

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        signal: controller.signal,
                    })
                        .then(response => (response.ok ? response.json() : []))
                        .then(items => {
                            if (controller.signal.aborted) {
                                return;
                            }

                            populateDatalist(datalist, items);
                        })
                        .catch(() => {
                            if (!controller.signal.aborted) {
                                datalist.innerHTML = '';
                            }
                        })
                        .finally(() => {
                            if (activeRequest === controller) {
                                activeRequest = null;
                            }
                        });
                }, 250);
            });
        });
    });
</script>
