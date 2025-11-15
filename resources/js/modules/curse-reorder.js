const instances = [];

const getCsrfToken = () => {
    if (typeof document === 'undefined') {
        return '';
    }

    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute('content') || '' : '';
};

class CurseReorder {
    constructor(container) {
        this.container = container;
        this.itemsSelector = container.dataset.itemsSelector || '[data-cursa-id]';
        this.handleSelector = container.dataset.handleSelector || '[data-cursa-drag-handle]';
        this.draggingClass = container.dataset.draggingClass || 'is-dragging';
        this.reorderUrl = container.dataset.reorderUrl || null;
        this.csrfToken = getCsrfToken();
        this.draggedItem = null;
        this.pendingController = null;
        this.boundItems = new WeakSet();
        this.feedbackEventTarget = container;

        this.refresh();
    }

    refresh() {
        const items = Array.from(this.container.querySelectorAll(this.itemsSelector));

        items.forEach(item => {
            if (!item.dataset.cursaId && item.dataset.cursaId !== '0') {
                const id = item.getAttribute('data-cursa-id');
                if (id !== null) {
                    item.dataset.cursaId = id;
                }
            }

            if (!this.boundItems.has(item)) {
                this.bindItem(item);
            }
        });

        this.updateOrderLabels();
    }

    bindItem(item) {
        item.setAttribute('draggable', 'true');

        const handle = this.handleSelector ? item.querySelector(this.handleSelector) : null;

        if (handle) {
            handle.classList.add('curse-drag-handle');
            handle.setAttribute('role', handle.getAttribute('role') || 'button');
            if (!handle.hasAttribute('tabindex')) {
                handle.setAttribute('tabindex', '0');
            }

            const activate = () => {
                item.dataset.dragReady = 'true';
                handle.classList.add('is-drag-ready');
            };

            const deactivate = () => {
                delete item.dataset.dragReady;
                handle.classList.remove('is-drag-ready');
            };

            ['mousedown', 'touchstart', 'keydown'].forEach(eventName => {
                handle.addEventListener(eventName, event => {
                    if (eventName === 'keydown') {
                        if (event.key !== ' ' && event.key !== 'Enter') {
                            return;
                        }

                        event.preventDefault();
                    }

                    activate();
                }, { passive: eventName.startsWith('touch') });
            });

            ['mouseup', 'mouseleave', 'touchend', 'touchcancel', 'blur'].forEach(eventName => {
                handle.addEventListener(eventName, () => {
                    deactivate();
                });
            });

            item.addEventListener('dragend', () => {
                deactivate();
            });
        }

        item.addEventListener('dragstart', event => this.handleDragStart(event, item));
        item.addEventListener('dragover', event => this.handleDragOver(event, item));
        item.addEventListener('drop', event => this.handleDrop(event));
        item.addEventListener('dragend', () => this.handleDragEnd());

        this.boundItems.add(item);
    }

    handleDragStart(event, item) {
        if (this.handleSelector && item.dataset.dragReady !== 'true') {
            event.preventDefault();
            return;
        }

        this.draggedItem = item;
        item.classList.add(this.draggingClass);
        this.container.classList.add('is-reordering');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.dataset.cursaId || '');
        }
    }

    handleDragOver(event, targetItem) {
        if (!this.draggedItem || targetItem === this.draggedItem) {
            return;
        }

        event.preventDefault();

        const rect = targetItem.getBoundingClientRect();
        const middle = rect.top + rect.height / 2;
        const nextSibling = this.draggedItem.nextElementSibling;

        if (event.clientY > middle) {
            if (nextSibling !== targetItem) {
                this.container.insertBefore(this.draggedItem, targetItem.nextElementSibling);
            }
        } else {
            if (targetItem !== this.draggedItem.nextElementSibling) {
                this.container.insertBefore(this.draggedItem, targetItem);
            }
        }
    }

    handleDrop(event) {
        if (!this.draggedItem) {
            return;
        }

        event.preventDefault();
        this.updateOrderLabels();
        this.persistOrder();
    }

    handleDragEnd() {
        if (this.draggedItem) {
            this.draggedItem.classList.remove(this.draggingClass);
        }

        this.container.classList.remove('is-reordering');
        this.draggedItem = null;
    }

    updateOrderLabels() {
        const items = Array.from(this.container.querySelectorAll(this.itemsSelector));

        items.forEach((item, index) => {
            const label = item.querySelector('[data-order-label]');
            if (label) {
                label.textContent = `#${index + 1}`;
            }
        });
    }

    persistOrder() {
        if (!this.reorderUrl) {
            return;
        }

        const order = this.getCurrentOrder();

        if (order.length === 0) {
            return;
        }

        if (this.pendingController) {
            this.pendingController.abort();
        }

        const controller = new AbortController();
        this.pendingController = controller;

        const headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (this.csrfToken) {
            headers['X-CSRF-TOKEN'] = this.csrfToken;
        }

        fetch(this.reorderUrl, {
            method: 'POST',
            headers,
            body: JSON.stringify({ order }),
            signal: controller.signal,
        })
            .then(response => {
                if (controller.signal.aborted) {
                    return null;
                }

                if (!response.ok) {
                    const error = new Error('request_failed');
                    error.status = response.status;
                    throw error;
                }

                if (response.status === 204) {
                    return {};
                }

                const contentType = response.headers.get('Content-Type') || '';
                if (contentType.includes('application/json')) {
                    return response.json();
                }

                return {};
            })
            .then(data => {
                if (controller.signal.aborted) {
                    return;
                }

                this.pendingController = null;
                document.dispatchEvent(new CustomEvent('curse:reorder-success', {
                    detail: {
                        container: this.container,
                        order,
                        response: data || {},
                    },
                }));
            })
            .catch(error => {
                if (controller.signal.aborted) {
                    return;
                }

                this.pendingController = null;
                document.dispatchEvent(new CustomEvent('curse:reorder-error', {
                    detail: {
                        container: this.container,
                        order,
                        error,
                    },
                }));
            });
    }

    getCurrentOrder() {
        return Array.from(this.container.querySelectorAll(this.itemsSelector))
            .map(item => item.dataset.cursaId)
            .filter(id => id !== undefined && id !== null);
    }
}

export const initCurseReorder = () => {
    if (typeof document === 'undefined') {
        return;
    }

    const containers = Array.from(document.querySelectorAll('[data-cursa-reorder]'));

    containers.forEach(container => {
        const existing = instances.find(instance => instance.container === container);

        if (existing) {
            existing.refresh();
        } else {
            instances.push(new CurseReorder(container));
        }
    });
};

export const refreshCurseReorder = () => {
    instances.forEach(instance => instance.refresh());
};

if (typeof window !== 'undefined') {
    window.__curseReorder = {
        init: initCurseReorder,
        refresh: refreshCurseReorder,
    };
}

export default initCurseReorder;
