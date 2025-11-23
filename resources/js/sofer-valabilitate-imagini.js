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
    let mode = 'create';
    let endpoint = root.dataset.uploadUrl;
    let mimeType = 'image/jpeg';
    let originalName = 'imagine.jpg';

    const defaultSaveLabel = saveButton.innerHTML;

    const updateContainerSize = () => {
        if (!cropperContainer || !cropperImage) {
            return;
        }

        const naturalWidth = cropperImage.naturalWidth || 1;
        const naturalHeight = cropperImage.naturalHeight || 1;
        const viewportWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        const viewportHeight = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

        const targetMaxWidth = Math.min(viewportWidth * 0.94, 900);
        const aspectRatio = naturalHeight / naturalWidth;
        const targetWidth = targetMaxWidth;
        const targetHeight = Math.min(targetWidth * aspectRatio, viewportHeight * 0.72);

        cropperContainer.style.maxWidth = `${targetMaxWidth}px`;
        cropperContainer.style.height = `${targetHeight}px`;
        cropperContainer.style.maxHeight = `${Math.min(viewportHeight * 0.75, targetHeight)}px`;
    };

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

                // Fit the image inside the cropper container without overflowing it (account for padding).
                const availableWidth = Math.max((containerRect?.width || containerData.width) - paddingX - 2, 0); // small margin to avoid bleed
                const availableHeight = Math.max((containerRect?.height || containerData.height) - paddingY - 2, 0);
                const containRatio = Math.min(
                    availableWidth / imageData.naturalWidth,
                    availableHeight / imageData.naturalHeight
                );
                const fallbackRatio = imageData.width / imageData.naturalWidth;
                const targetRatio = containRatio > 0 ? containRatio : fallbackRatio;
                const targetWidth = imageData.naturalWidth * targetRatio;
                const targetHeight = imageData.naturalHeight * targetRatio;
                const containerWidth = Math.max((containerRect?.width || containerData.width) - paddingX, 0);
                const containerHeight = Math.max((containerRect?.height || containerData.height) - paddingY, 0);
                const left = Math.max((containerWidth - targetWidth) / 2, 0) + paddingLeft;
                const top = Math.max((containerHeight - targetHeight) / 2, 0) + paddingTop;

                instance.zoomTo(targetRatio);
                instance.setCanvasData({
                    width: targetWidth,
                    height: targetHeight,
                    left,
                    top,
                });
            },
        });
    };

    const openCropper = (src, title) => {
        titleElement.textContent = title;
        statusElement.textContent = '';

        destroyCropper();
        imageLoaded = false;
        cropperImage.src = src;

        cropperImage.onload = () => {
            imageLoaded = true;
            updateContainerSize();
            tryInitCropper();
        };

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

        mode = 'create';
        endpoint = root.dataset.uploadUrl;
        mimeType = file.type || 'image/jpeg';
        originalName = file.name || '';

        const objectUrl = URL.createObjectURL(file);
        openCropper(objectUrl, 'Incarca imagine');
    });

    document.querySelectorAll('[data-recrop]').forEach((button) => {
        button.addEventListener('click', () => {
            mode = 'update';
            endpoint = button.dataset.updateUrl;
            mimeType = button.dataset.mime || 'image/jpeg';
            originalName = button.dataset.originalName || '';

            const src = button.dataset.streamUrl;
            if (!src) {
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
    });

    modalElement.addEventListener('shown.bs.modal', () => {
        updateContainerSize();
        tryInitCropper();
    });

    window.addEventListener('resize', updateContainerSize);

    saveButton?.addEventListener('click', () => {
        if (!cropper) {
            statusElement.textContent = 'Selecteaza o imagine pentru a continua.';
            return;
        }

        setSaving(true);

        cropper.getCroppedCanvas().toBlob(
            async (blob) => {
                if (!blob) {
                    setSaving(false, 'Nu am putut genera imaginea. Incearca din nou.');
                    return;
                }

                const ext = mimeToExtension(mimeType);
                const formData = new FormData();

                formData.append('_token', root.dataset.csrf);
                if (mode === 'update') {
                    formData.append('_method', 'PUT');
                }

                formData.append('image', blob, formatFilename(originalName, ext));

                try {
                    const response = await fetch(endpoint, {
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

                    const json = await response.json().catch(() => null);
                    const message = json?.message || 'Incarcarea a esuat. Verifica dimensiunea si formatul imaginii.';
                    setSaving(false, message);
                } catch (error) {
                    setSaving(false, 'A aparut o eroare de retea. Incearca din nou.');
                }
            },
            mimeType || 'image/jpeg'
        );
    });
}
