@once
    @push('page-scripts')
        <script>
            window.MasiniMementouriDocuments = window.MasiniMementouriDocuments || (function () {
                const feedbackClasses = ['text-success', 'text-danger', 'text-muted'];

                function findFeedbackElement(form) {
                    if (!form) {
                        return null;
                    }

                    const direct = form.querySelector('[data-feedback-target]');
                    if (direct) {
                        return direct;
                    }

                    const wrapper = form.closest('[data-document-wrapper]');
                    if (wrapper) {
                        return wrapper.querySelector('[data-feedback-target]') ?? null;
                    }

                    return null;
                }

                function clearFeedback(element) {
                    if (!element) {
                        return;
                    }

                    feedbackClasses.forEach((className) => element.classList.remove(className));
                    element.textContent = '';
                    element.hidden = true;
                }

                function setFeedback(element, type, message) {
                    if (!element) {
                        return;
                    }

                    clearFeedback(element);

                    const className = type === 'success'
                        ? 'text-success'
                        : type === 'error'
                            ? 'text-danger'
                            : 'text-muted';

                    element.classList.add(className);
                    element.textContent = message;
                    element.hidden = !message;
                }

                function extractErrorMessage(data, fallback = 'A apărut o eroare. Te rugăm să încerci din nou.') {
                    if (!data) {
                        return fallback;
                    }

                    if (typeof data.message === 'string' && data.message.trim() !== '') {
                        return data.message;
                    }

                    if (data.errors && typeof data.errors === 'object') {
                        for (const key of Object.keys(data.errors)) {
                            const value = data.errors[key];
                            if (Array.isArray(value) && value.length > 0) {
                                return value[0];
                            }
                        }
                    }

                    return fallback;
                }

                function isValidDateValue(value) {
                    if (!value) {
                        return true;
                    }

                    const match = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(value);

                    if (!match) {
                        return false;
                    }

                    const year = Number.parseInt(match[1], 10);
                    const month = Number.parseInt(match[2], 10);
                    const day = Number.parseInt(match[3], 10);
                    const date = new Date(value);

                    if (Number.isNaN(date.getTime())) {
                        return false;
                    }

                    return date.getUTCFullYear() === year
                        && date.getUTCMonth() + 1 === month
                        && date.getUTCDate() === day;
                }

                function updateDocumentDisplays(documentId, data, fallbackLabel = '—') {
                    if (!documentId) {
                        return;
                    }

                    const wrappers = document.querySelectorAll(`[data-document-id="${documentId}"]`);
                    const hasFormatted = Object.prototype.hasOwnProperty.call(data, 'formatted_date');
                    const hasReadable = Object.prototype.hasOwnProperty.call(data, 'readable_date');

                    wrappers.forEach((wrapper) => {
                        const localFallback = wrapper.dataset.emptyLabel
                            || wrapper.querySelector('[data-document-badge]')?.dataset.emptyLabel
                            || fallbackLabel;

                        const dateInput = wrapper.querySelector('input[name="data_expirare"]');
                        if (dateInput && hasFormatted) {
                            dateInput.value = data.formatted_date ?? '';
                        }

                        const inlineDisplay = wrapper.querySelector('[data-date-text]');
                        if (inlineDisplay) {
                            if (hasReadable) {
                                inlineDisplay.textContent = data.readable_date ?? localFallback;
                            } else if (hasFormatted) {
                                inlineDisplay.textContent = data.formatted_date ?? localFallback;
                            }
                        }

                        const badge = wrapper.querySelector('[data-document-badge]');
                        if (badge && (hasReadable || hasFormatted)) {
                            const badgeFallback = badge.dataset.emptyLabel || localFallback;
                            if (hasReadable) {
                                badge.textContent = data.readable_date ?? badgeFallback;
                            } else {
                                badge.textContent = data.formatted_date ?? badgeFallback;
                            }
                        }

                        if (Object.prototype.hasOwnProperty.call(data, 'color_class')) {
                            const baseClass = wrapper.dataset.baseClass || '';
                            const container = wrapper.closest('[data-color-holder]') || wrapper;

                            if (container) {
                                container.className = baseClass
                                    ? `${baseClass} ${data.color_class ?? ''}`.trim()
                                    : data.color_class ?? '';
                            }
                        }
                    });
                }

                function refreshFileList(wrapper, html) {
                    if (!wrapper) {
                        return;
                    }

                    const container = wrapper.querySelector('[data-document-files]');

                    if (!container) {
                        return;
                    }

                    const existingList = container.querySelector('[data-document-files-list]');
                    if (existingList) {
                        existingList.remove();
                    }

                    if (html) {
                        container.insertAdjacentHTML('beforeend', html);
                    }

                    initializeDocumentForms(container);
                }

                function initializeDocumentUpdateForm(form) {
                    if (!form || form.dataset.initialized === 'true') {
                        return;
                    }

                    form.dataset.initialized = 'true';

                    const wrapper = form.closest('[data-document-wrapper]');
                    const documentId = wrapper?.dataset.documentId;
                    const tokenField = form.querySelector('input[name="_token"]');
                    const dateInput = form.querySelector('[data-date-input], input[name="data_expirare"]');
                    const trigger = form.querySelector('[data-edit-trigger]');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const feedbackElement = findFeedbackElement(form);
                    const emptyLabel = wrapper?.dataset.emptyLabel || '—';

                    const setLoading = (loading) => {
                        form.dataset.loading = loading ? 'true' : 'false';

                        const interactiveElements = Array.from(form.elements).filter((element) => {
                            if (!element || !element.tagName) {
                                return false;
                            }

                            const tag = element.tagName.toLowerCase();
                            if (!['input', 'button', 'select', 'textarea'].includes(tag)) {
                                return false;
                            }

                            if (tag === 'input' && element.type === 'hidden') {
                                return false;
                            }

                            return true;
                        });

                        interactiveElements.forEach((element) => {
                            element.disabled = loading;
                        });

                        if (submitButton) {
                            submitButton.disabled = loading;
                        }

                        if (trigger) {
                            trigger.disabled = loading;
                        }
                    };

                    const handleSubmit = async (event) => {
                        event.preventDefault();

                        if (form.dataset.loading === 'true') {
                            return;
                        }

                        clearFeedback(feedbackElement);

                        if (form.reportValidity && !form.reportValidity()) {
                            return;
                        }

                        if (dateInput && !isValidDateValue(dateInput.value)) {
                            setFeedback(feedbackElement, 'error', 'Data introdusă nu este validă.');
                            return;
                        }

                        const formData = new FormData(form);

                        setLoading(true);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            let data = null;

                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }

                            if (!response.ok || !data || data.status !== 'ok') {
                                setFeedback(feedbackElement, 'error', extractErrorMessage(data));
                                return;
                            }

                            setFeedback(feedbackElement, 'success', data.message || 'Modificarea a fost salvată.');
                            updateDocumentDisplays(documentId, data, emptyLabel);
                        } catch (error) {
                            setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată. Te rugăm să încerci din nou.');
                        } finally {
                            setLoading(false);
                        }
                    };

                    form.addEventListener('submit', handleSubmit);

                    if (trigger && dateInput) {
                        trigger.addEventListener('click', () => {
                            trigger.setAttribute('hidden', 'hidden');
                            dateInput.classList.remove('visually-hidden');
                            dateInput.focus();
                        });
                    }

                    if (dateInput && trigger) {
                        dateInput.addEventListener('blur', () => {
                            if (!dateInput.value) {
                                trigger.removeAttribute('hidden');
                                dateInput.classList.add('visually-hidden');
                            }
                        });
                    }
                }

                function initializeDocumentUploadForm(form) {
                    if (!form || form.dataset.initialized === 'true') {
                        return;
                    }

                    form.dataset.initialized = 'true';

                    const wrapper = form.closest('[data-document-wrapper]');
                    const tokenField = form.querySelector('input[name="_token"]');
                    const fileInput = form.querySelector('input[type="file"]');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const feedbackElement = findFeedbackElement(form);

                    const setLoading = (loading) => {
                        form.dataset.loading = loading ? 'true' : 'false';

                        if (submitButton) {
                            submitButton.disabled = loading;
                        }

                        if (fileInput) {
                            fileInput.disabled = loading;
                        }
                    };

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        if (form.dataset.loading === 'true') {
                            return;
                        }

                        clearFeedback(feedbackElement);

                        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                            setFeedback(feedbackElement, 'error', 'Selectează un fișier PDF.');
                            return;
                        }

                        const file = fileInput.files[0];
                        if (file && file.type !== 'application/pdf') {
                            setFeedback(feedbackElement, 'error', 'Fișierul trebuie să fie în format PDF.');
                            return;
                        }

                        const formData = new FormData(form);

                        setLoading(true);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            let data = null;

                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }

                            if (!response.ok || !data || data.status !== 'ok') {
                                setFeedback(feedbackElement, 'error', extractErrorMessage(data, 'Încărcarea a eșuat.'));
                                return;
                            }

                            setFeedback(feedbackElement, 'success', data.message || 'Fișierul a fost încărcat.');
                            if (data.files_html) {
                                refreshFileList(wrapper, data.files_html);
                            }

                            if (fileInput) {
                                fileInput.value = '';
                            }
                        } catch (error) {
                            setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată la încărcare.');
                        } finally {
                            setLoading(false);
                        }
                    });
                }

                function initializeDocumentDeleteForm(form) {
                    if (!form || form.dataset.initialized === 'true') {
                        return;
                    }

                    form.dataset.initialized = 'true';

                    const wrapper = form.closest('[data-document-wrapper]');
                    const tokenField = form.querySelector('input[name="_token"]');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const feedbackElement = findFeedbackElement(form);

                    const setLoading = (loading) => {
                        form.dataset.loading = loading ? 'true' : 'false';

                        if (submitButton) {
                            submitButton.disabled = loading;
                        }
                    };

                    form.addEventListener('submit', async (event) => {
                        event.preventDefault();

                        if (form.dataset.loading === 'true') {
                            return;
                        }

                        if (!window.confirm('Ștergi fișierul?')) {
                            return;
                        }

                        clearFeedback(feedbackElement);

                        const formData = new FormData(form);

                        setLoading(true);

                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            let data = null;

                            try {
                                data = await response.json();
                            } catch (error) {
                                data = null;
                            }

                            if (!response.ok || !data || data.status !== 'ok') {
                                setFeedback(feedbackElement, 'error', extractErrorMessage(data, 'Fișierul nu a putut fi șters.'));
                                return;
                            }

                            setFeedback(feedbackElement, 'success', data.message || 'Fișierul a fost șters.');
                            if (data.files_html) {
                                refreshFileList(wrapper, data.files_html);
                            }
                        } catch (error) {
                            setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată la ștergere.');
                        } finally {
                            setLoading(false);
                        }
                    });
                }

                function initializeDocumentForms(root = document) {
                    root.querySelectorAll('form[data-document-update]').forEach(initializeDocumentUpdateForm);
                    root.querySelectorAll('form[data-document-upload]').forEach(initializeDocumentUploadForm);
                    root.querySelectorAll('form[data-document-delete]').forEach(initializeDocumentDeleteForm);
                }

                function initOnLoad(root = document) {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', () => initializeDocumentForms(root), { once: true });
                    } else {
                        initializeDocumentForms(root);
                    }
                }

                return {
                    initializeDocumentForms,
                    initOnLoad,
                    updateDocumentDisplays,
                    setFeedback,
                    clearFeedback,
                    extractErrorMessage,
                };
            })();
        </script>
    @endpush
@endonce
