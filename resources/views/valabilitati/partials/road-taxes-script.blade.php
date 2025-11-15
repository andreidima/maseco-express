@once
    @push('page-scripts')
        <script>
            (function () {
                function escapeRegExp(value) {
                    return value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                }

                function updateEmptyState(collection) {
                    const itemsContainer = collection.querySelector('.road-tax-items');
                    const emptyState = collection.querySelector('.road-tax-empty');

                    if (!itemsContainer || !emptyState) {
                        return;
                    }

                    const hasItems = itemsContainer.querySelector('.road-tax-entry') !== null;
                    emptyState.classList.toggle('d-none', hasItems);
                }

                function renumberEntries(collection) {
                    const items = collection.querySelectorAll('.road-tax-entry');
                    const fieldName = collection.dataset.fieldName || 'taxe_drum';
                    const idPrefix = collection.dataset.idPrefix || 'taxe-drum-';
                    const namePattern = new RegExp(`${escapeRegExp(fieldName)}\[\\d+\]`);
                    const errorPattern = new RegExp(`${escapeRegExp(fieldName)}\\.\\d+`);
                    const idPattern = new RegExp(`${escapeRegExp(idPrefix)}\\d+`);

                    items.forEach((entry, index) => {
                        entry.dataset.index = String(index);

                        entry.querySelectorAll('[name]').forEach(element => {
                            const name = element.getAttribute('name');
                            if (!name) {
                                return;
                            }

                            element.setAttribute('name', name.replace(namePattern, `${fieldName}[${index}]`));
                        });

                        entry.querySelectorAll('[id]').forEach(element => {
                            const id = element.getAttribute('id');
                            if (!id || !idPattern.test(id)) {
                                return;
                            }

                            element.setAttribute('id', id.replace(idPattern, `${idPrefix}${index}`));
                        });

                        entry.querySelectorAll('label[for]').forEach(label => {
                            const target = label.getAttribute('for');
                            if (!target || !idPattern.test(target)) {
                                return;
                            }

                            label.setAttribute('for', target.replace(idPattern, `${idPrefix}${index}`));
                        });

                        entry.querySelectorAll('[data-error-for]').forEach(element => {
                            const field = element.getAttribute('data-error-for');
                            if (!field) {
                                return;
                            }

                            element.setAttribute('data-error-for', field.replace(errorPattern, `${fieldName}.${index}`));
                        });
                    });

                    collection.dataset.nextIndex = String(items.length);
                }

                function updateCollection(collection) {
                    renumberEntries(collection);
                    updateEmptyState(collection);
                }

                function addEntry(collection) {
                    const template = collection.querySelector('template.road-tax-template');
                    const itemsContainer = collection.querySelector('.road-tax-items');
                    if (!template || !itemsContainer) {
                        return;
                    }

                    const nextIndex = Number(collection.dataset.nextIndex || itemsContainer.children.length);
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = template.innerHTML.replace(/__INDEX__/g, String(nextIndex));
                    const entry = wrapper.firstElementChild;

                    if (!entry) {
                        return;
                    }

                    itemsContainer.appendChild(entry);
                    collection.dataset.nextIndex = String(nextIndex + 1);
                    updateEmptyState(collection);
                }

                function handleAddButton(event) {
                    const button = event.currentTarget;
                    const wrapper = button.closest('[data-road-tax-wrapper]');
                    if (!wrapper) {
                        return;
                    }

                    const collection = wrapper.querySelector('.road-tax-collection');
                    if (!collection) {
                        return;
                    }

                    event.preventDefault();
                    addEntry(collection);
                }

                function handleCollectionClick(event) {
                    const removeTrigger = event.target.closest('[data-road-tax-remove]');
                    if (!removeTrigger) {
                        return;
                    }

                    const collection = event.currentTarget;
                    const entry = removeTrigger.closest('.road-tax-entry');
                    if (!entry) {
                        return;
                    }

                    event.preventDefault();
                    entry.remove();
                    updateCollection(collection);
                }

                function initializeCollection(collection) {
                    if (collection.dataset.initialized === 'true') {
                        return;
                    }

                    collection.addEventListener('click', handleCollectionClick);
                    updateCollection(collection);

                    const wrapper = collection.closest('[data-road-tax-wrapper]');
                    if (wrapper) {
                        const addButton = wrapper.querySelector('[data-road-tax-add]');
                        if (addButton && addButton.dataset.roadTaxBound !== 'true') {
                            addButton.addEventListener('click', handleAddButton);
                            addButton.dataset.roadTaxBound = 'true';
                        }
                    }

                    collection.dataset.initialized = 'true';
                }

                window.initializeRoadTaxCollections = function (root) {
                    const context = root instanceof Element ? root : document;
                    const collections = context.querySelectorAll('.road-tax-collection');
                    collections.forEach(collection => initializeCollection(collection));
                };

                document.addEventListener('DOMContentLoaded', () => {
                    window.initializeRoadTaxCollections();
                });
            })();
        </script>
    @endpush
@endonce
