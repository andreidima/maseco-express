<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
        const fields = [
            { inputId: 'denumire_furnizor', datalistId: 'furnizor-suggestions', tip: 'furnizor' },
            { inputId: 'departament_vehicul', datalistId: 'departament-suggestions', tip: 'departament' },
        ];

        fields.forEach(({ inputId, datalistId, tip }) => {
            const input = document.getElementById(inputId);
            const datalist = document.getElementById(datalistId);

            if (!input || !datalist) {
                return;
            }

            let debounceTimeout = null;

            input.addEventListener('input', () => {
                const query = input.value.trim();
                if (query.length < 2) {
                    datalist.innerHTML = '';
                    return;
                }

                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const url = new URL('{{ route('facturi-furnizori.facturi.sugestii') }}');
                    url.searchParams.set('tip', tip);
                    url.searchParams.set('q', query);

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    })
                        .then(response => response.ok ? response.json() : [])
                        .then(items => {
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
                            datalist.innerHTML = '';
                        });
                }, 250);
            });
        });
    });
</script>
