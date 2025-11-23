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

    const openCropper = (src, title) => {
        titleElement.textContent = title;
        statusElement.textContent = '';

        destroyCropper();
        cropperImage.src = src;

        cropperImage.onload = () => {
            destroyCropper();
            cropper = new Cropper(cropperImage, {
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
                background: false,

                // Run when Cropper finished initializing
                ready() {
                    const instance = this.cropper;
                    const imageData = instance.getImageData();
                    const containerData = instance.getContainerData();

                    // Ensure the image covers the cropper container (width or height) without shrinking.
                    const currentRatio = imageData.width / imageData.naturalWidth;
                    const coverRatio = Math.max(
                        containerData.width / imageData.naturalWidth,
                        containerData.height / imageData.naturalHeight
                    );
                    const targetRatio = Math.max(currentRatio, coverRatio);

                    instance.zoomTo(targetRatio);
                },
            });


            modal.show();
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
