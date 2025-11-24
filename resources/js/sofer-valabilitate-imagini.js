import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

const root = document.getElementById('cursa-images-app');

if (root) {
    const uploadButton = document.getElementById('uploadButton');
    const fileInput = document.getElementById('imageInput');
    const cropperImage = document.getElementById('cropperImage');
    const cropperContainer = document.getElementById('cropperContainer');
    const modalElement = document.getElementById('imageCropperModal');
    const saveButton = document.getElementById('saveCropButton');
    const statusElement = document.getElementById('cropperStatus');
    const titleElement = document.getElementById('cropperModalTitle');

    const modal = new window.bootstrap.Modal(modalElement);

    let cropper = null;
    const state = {
        mode: 'create',
        endpoint: root.dataset.uploadUrl,
        mimeType: 'image/jpeg',
        originalName: 'imagine.jpg',
    };

    const defaultSaveLabel = saveButton.innerHTML;

    const updateContainerSize = () => {
        if (!cropperContainer || !cropperImage || !imageLoaded) {
            return;
        }

        const naturalWidth = cropperImage.naturalWidth || 1;
        const naturalHeight = cropperImage.naturalHeight || 1;
        const viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        const viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

        const targetMaxWidth = Math.min(viewportWidth * 0.9, 850);
        const aspectRatio = naturalHeight / naturalWidth;
        const targetWidth = targetMaxWidth;
        const targetHeight = Math.min(targetWidth * aspectRatio, viewportHeight * 0.68);

        cropperContainer.style.maxWidth = `${targetMaxWidth}px`;
        cropperContainer.style.height = `${targetHeight}px`;
        cropperContainer.style.maxHeight = `${Math.min(viewportHeight * 0.75, targetHeight)}px`;
    };

    const debounce = (fn, delay = 150) => {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = window.setTimeout(() => fn(...args), delay);
        };
    };

    const debouncedUpdateContainerSize = debounce(updateContainerSize);

    const mimeToExtension = (mime) => {
        if (mime?.includes('png')) return 'png';
        if (mime?.includes('webp')) return 'webp';
        return 'jpg';
    };

    const formatFilename = (name, fallbackExt) => {
        if (!name) {
            return `imagine.${fallbackExt}`;
        }

        if (name.includes('.')) {
            return name;
        }

        return `${name}.${fallbackExt}`;
    };

    const destroyCropper = () => {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    };

    const setSaving = (isSaving, message = '') => {
        saveButton.disabled = isSaving;
        saveButton.innerHTML = isSaving ? '<span class="spinner-border spinner-border-sm me-2"></span>Se salveaza...' : defaultSaveLabel;
        statusElement.textContent = message;
    };

    let imageLoaded = false;

    const tryInitCropper = () => {
        const isModalVisible = modalElement.classList.contains('show');

        if (!imageLoaded || !isModalVisible) {
            return;
        }

        destroyCropper();
        cropper = new Cropper(cropperImage, {
            viewMode: 2,
            autoCropArea: 1,
            responsive: true,
            background: false,
            restore: false,

            // Run when Cropper finished initializing
            ready() {
                const instance = this.cropper;
                const imageData = instance.getImageData();
                const containerData = instance.getContainerData();
                const containerRect = cropperContainer?.getBoundingClientRect();
                const containerStyle = window.getComputedStyle(cropperContainer);
                const paddingLeft = parseFloat(containerStyle.paddingLeft || '0');
                const paddingRight = parseFloat(containerStyle.paddingRight || '0');
                const paddingTop = parseFloat(containerStyle.paddingTop || '0');
                const paddingBottom = parseFloat(containerStyle.paddingBottom || '0');
                const paddingX = paddingLeft + paddingRight;
                const paddingY = paddingTop + paddingBottom;
                const containerWidth = Math.max((containerRect?.width || containerData.width) - paddingX, 0);
                const containerHeight = Math.max((containerRect?.height || containerData.height) - paddingY, 0);

                // Fit the image inside the cropper container without overflowing it (account for padding),
                // then center it with a visible inset so the canvas never bleeds past the edges.
                const visualPadding = 20;
                const availableWidth = Math.max(containerWidth - visualPadding * 2, 0);
                const availableHeight = Math.max(containerHeight - visualPadding * 2, 0);
                const containRatio = Math.min(
                    availableWidth / imageData.naturalWidth,
                    availableHeight / imageData.naturalHeight,
                    1
                );
                const fallbackRatio = imageData.width / imageData.naturalWidth;
                const targetRatio = containRatio > 0 ? containRatio : fallbackRatio;
                const scaledWidth = imageData.naturalWidth * targetRatio;
                const scaledHeight = imageData.naturalHeight * targetRatio;
                const finalWidth = Math.min(scaledWidth, availableWidth);
                const finalHeight = Math.min(scaledHeight, availableHeight);
                const left = Math.max((containerWidth - finalWidth) / 2, 0) + paddingLeft;
                const top = Math.max((containerHeight - finalHeight) / 2, 0) + paddingTop;

                const cropBoxWidth = Math.min(finalWidth, availableWidth);
                const cropBoxHeight = Math.min(finalHeight, availableHeight);

                instance.setCanvasData({
                    width: finalWidth,
                    height: finalHeight,
                    left,
                    top,
                });
                instance.setCropBoxData({
                    width: cropBoxWidth,
                    height: cropBoxHeight,
                    left: Math.max((containerWidth - cropBoxWidth) / 2, 0) + paddingLeft,
                    top: Math.max((containerHeight - cropBoxHeight) / 2, 0) + paddingTop,
                });
            },
        });
    };

    const handleImageLoad = () => {
        imageLoaded = true;
        updateContainerSize();
        tryInitCropper();
    };

    const openCropper = (src, title) => {
        titleElement.textContent = title;
        statusElement.textContent = '';

        destroyCropper();
        imageLoaded = false;
        cropperImage.removeEventListener('load', handleImageLoad);
        cropperImage.addEventListener('load', handleImageLoad, { once: true });
        cropperImage.src = src;

        modal.show();
    };

    uploadButton?.addEventListener('click', () => {
        fileInput?.click();
    });

    fileInput?.addEventListener('change', (event) => {
        const [file] = event.target.files || [];
        if (!file) {
            return;
        }

        state.mode = 'create';
        state.endpoint = root.dataset.uploadUrl;
        state.mimeType = file.type || 'image/jpeg';
        state.originalName = file.name || '';

        const objectUrl = URL.createObjectURL(file);
        openCropper(objectUrl, 'Incarca imagine');
    });

    document.querySelectorAll('[data-recrop]').forEach((button) => {
        button.addEventListener('click', () => {
            state.mode = 'update';
            state.endpoint = button.dataset.updateUrl;
            state.mimeType = button.dataset.mime || 'image/jpeg';
            state.originalName = button.dataset.originalName || '';

            if (!state.endpoint) {
                statusElement.textContent = 'Nu am putut pregăti salvarea acestei imagini.';
                return;
            }

            const src = button.dataset.streamUrl;
            if (!src) {
                statusElement.textContent = 'Nu am putut găsi imaginea pentru editare.';
                return;
            }

            openCropper(src, 'Reediteaza imagine');
        });
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        destroyCropper();
        statusElement.textContent = '';
        saveButton.innerHTML = defaultSaveLabel;
        fileInput.value = '';
        cropperImage.removeEventListener('load', handleImageLoad);
        window.removeEventListener('resize', debouncedUpdateContainerSize);
        imageLoaded = false;
    });

    modalElement.addEventListener('shown.bs.modal', () => {
        updateContainerSize();
        tryInitCropper();
        window.addEventListener('resize', debouncedUpdateContainerSize);
    });

    saveButton?.addEventListener('click', () => {
        if (!cropper) {
            statusElement.textContent = 'Selecteaza o imagine pentru a continua.';
            return;
        }

        setSaving(true);

        if (!state.endpoint) {
            setSaving(false, 'Nu am putut găsi adresa de salvare. Reîncarcă pagina.');
            return;
        }

        cropper.getCroppedCanvas().toBlob(
            async (blob) => {
                if (!blob) {
                    setSaving(false, 'Nu am putut genera imaginea. Incearca din nou.');
                    return;
                }

                const ext = mimeToExtension(state.mimeType);
                const formData = new FormData();

                formData.append('_token', root.dataset.csrf);
                if (state.mode === 'update') {
                    formData.append('_method', 'PUT');
                }

                formData.append('image', blob, formatFilename(state.originalName, ext));

                try {
                    const response = await fetch(state.endpoint, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                        },
                        body: formData,
                    });

                    if (response.ok) {
                        window.location.reload();
                        return;
                    }

                    const contentType = response.headers.get('content-type');
                    const canParseJson = contentType && contentType.includes('application/json');
                    const json = canParseJson ? await response.json().catch(() => null) : null;
                    const message = json?.message || 'Incarcarea a esuat. Verifica dimensiunea si formatul imaginii.';
                    setSaving(false, message);
                } catch (error) {
                    setSaving(false, 'A aparut o eroare de retea. Incearca din nou.');
                }
            },
            state.mimeType || 'image/jpeg'
        );
    });
}
