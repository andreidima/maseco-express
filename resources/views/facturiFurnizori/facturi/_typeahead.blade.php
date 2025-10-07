<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        const inputs = document.querySelectorAll('input[data-typeahead-tip]');

        inputs.forEach(input => {
            const datalistId = input.getAttribute('list');
            const datalist = datalistId ? document.getElementById(datalistId) : null;
            const tip = input.dataset.typeaheadTip;
            const minLength = parseInt(input.dataset.typeaheadMinlength ?? '2', 10) || 1;

            if (!datalist || !tip) {
                return;
            }

            let debounceTimeout = null;
            let activeRequest = null;

            input.addEventListener('input', () => {
                const query = input.value.trim();

                if (activeRequest) {
                    activeRequest.abort();
                    activeRequest = null;
                }

                if (query.length < minLength) {
                    datalist.innerHTML = '';
                    return;
                }

                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const controller = new AbortController();
                    activeRequest = controller;

                    const url = new URL('{{ route('facturi-furnizori.facturi.sugestii') }}');
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

                            datalist.innerHTML = '';

                            if (Array.isArray(items)) {
                                items.forEach(item => {
                                    const option = document.createElement('option');
                                    option.value = item;
                                    datalist.appendChild(option);
                                });
                            }
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
