<template>
    <div class="driver-form-section p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h2 class="fs-4 mb-1">{{ valabilitate.denumire }}</h2>
                <p class="text-muted mb-0">{{ valabilitate.numar_auto }} · {{ intervalLabel }}</p>
            </div>
            <button
                v-if="!isFormVisible"
                type="button"
                class="btn btn-primary btn-lg"
                @click="startCreate"
            >
                <i class="fa-solid fa-circle-plus me-2"></i> Adaugă cursă
            </button>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Încărcare...</span>
            </div>
        </div>

        <template v-else>
            <section v-if="isFormVisible" class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fs-5 mb-0">{{ formTitle }}</h3>
                    <button type="button" class="btn btn-link text-decoration-none" @click="cancelForm">
                        Renunță
                    </button>
                </div>

                <div v-if="generalError" class="alert alert-danger" role="alert">
                    {{ generalError }}
                </div>

                <form class="row g-3" @submit.prevent="submitForm">
                    <div class="col-12 col-md-6">
                        <LocalitateAutocomplete
                            id="incarcare-localitate"
                            label="Localitate încărcare"
                            :endpoint="localitatiEndpoint"
                            v-model="form.incarcare_localitate"
                            :disabled="saving"
                        />
                        <div v-if="formErrors.incarcare_localitate" class="text-danger small mt-1">
                            {{ formErrors.incarcare_localitate[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="incarcare_cod_postal" class="form-label">Cod poștal încărcare</label>
                        <input
                            id="incarcare_cod_postal"
                            type="text"
                            class="form-control"
                            v-model="form.incarcare_cod_postal"
                            :disabled="saving"
                        >
                        <div v-if="formErrors.incarcare_cod_postal" class="text-danger small mt-1">
                            {{ formErrors.incarcare_cod_postal[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="incarcare_tara_id" class="form-label">Țară încărcare</label>
                        <select
                            id="incarcare_tara_id"
                            class="form-select"
                            v-model="form.incarcare_tara_id"
                            :disabled="saving"
                        >
                            <option value="">Alegeți țara</option>
                            <option v-for="tara in countries" :key="tara.id" :value="String(tara.id)">
                                {{ tara.nume }}
                            </option>
                        </select>
                        <div v-if="formErrors.incarcare_tara_id" class="text-danger small mt-1">
                            {{ formErrors.incarcare_tara_id[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <LocalitateAutocomplete
                            id="descarcare-localitate"
                            label="Localitate descărcare"
                            :endpoint="localitatiEndpoint"
                            v-model="form.descarcare_localitate"
                            :disabled="saving"
                        />
                        <div v-if="formErrors.descarcare_localitate" class="text-danger small mt-1">
                            {{ formErrors.descarcare_localitate[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="descarcare_cod_postal" class="form-label">Cod poștal descărcare</label>
                        <input
                            id="descarcare_cod_postal"
                            type="text"
                            class="form-control"
                            v-model="form.descarcare_cod_postal"
                            :disabled="saving"
                        >
                        <div v-if="formErrors.descarcare_cod_postal" class="text-danger small mt-1">
                            {{ formErrors.descarcare_cod_postal[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="descarcare_tara_id" class="form-label">Țară descărcare</label>
                        <select
                            id="descarcare_tara_id"
                            class="form-select"
                            v-model="form.descarcare_tara_id"
                            :disabled="saving"
                        >
                            <option value="">Alegeți țara</option>
                            <option v-for="tara in countries" :key="tara.id" :value="String(tara.id)">
                                {{ tara.nume }}
                            </option>
                        </select>
                        <div v-if="formErrors.descarcare_tara_id" class="text-danger small mt-1">
                            {{ formErrors.descarcare_tara_id[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="data_cursa_date" class="form-label">Data cursei</label>
                        <input
                            id="data_cursa_date"
                            type="date"
                            class="form-control"
                            v-model="form.data_cursa_date"
                            :disabled="saving"
                        >
                        <div v-if="formErrors.data_cursa || formErrors.data_cursa_date" class="text-danger small mt-1">
                            {{ resolveError('data_cursa_date') }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6" v-if="showTimeField">
                        <label for="data_cursa_time" class="form-label">Ora cursei</label>
                        <input
                            id="data_cursa_time"
                            type="time"
                            class="form-control"
                            v-model="form.data_cursa_time"
                            :disabled="saving"
                        >
                        <div v-if="formErrors.data_cursa_time" class="text-danger small mt-1">
                            {{ formErrors.data_cursa_time[0] }}
                        </div>
                    </div>
                    <div class="col-12" v-else>
                        <button type="button" class="btn btn-outline-secondary w-100" @click="manualTimeToggle = true">
                            Adaugă ora cursei (opțional)
                        </button>
                    </div>
                    <div class="col-12">
                        <label for="observatii" class="form-label">Observații</label>
                        <textarea
                            id="observatii"
                            class="form-control"
                            rows="3"
                            v-model="form.observatii"
                            :disabled="saving"
                        ></textarea>
                        <div v-if="formErrors.observatii" class="text-danger small mt-1">
                            {{ formErrors.observatii[0] }}
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="km_bord" class="form-label">KM bord</label>
                        <input
                            id="km_bord"
                            type="number"
                            class="form-control"
                            min="0"
                            v-model="form.km_bord"
                            :disabled="saving"
                        >
                        <div v-if="formErrors.km_bord" class="text-danger small mt-1">
                            {{ formErrors.km_bord[0] }}
                        </div>
                    </div>
                    <div class="col-12 d-flex gap-3 flex-wrap">
                        <button type="submit" class="btn btn-success btn-lg" :disabled="saving">
                            {{ saving ? 'Se salvează...' : 'Salvează' }}
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" :disabled="saving" @click="cancelForm">
                            Anulează
                        </button>
                    </div>
                </form>
            </section>

            <section class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="fs-5 mb-0">Curse înregistrate</h3>
                    <button
                        v-if="!isFormVisible"
                        type="button"
                        class="btn btn-outline-primary btn-lg"
                        @click="startCreate"
                    >
                        Adaugă cursă
                    </button>
                </div>
                <div v-if="valabilitate.curse && valabilitate.curse.length" class="d-flex flex-column gap-3">
                    <article v-for="cursa in valabilitate.curse" :key="cursa.id" class="border rounded-4 p-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <p class="fw-semibold mb-1">{{ cursa.incarcare_localitate }} → {{ cursa.descarcare_localitate }}</p>
                                <p class="mb-0 text-muted small">
                                    {{ formatDateTime(cursa) }} · {{ cursa.descarcare_tara ?? 'Țară nedefinită' }}
                                </p>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-lg" @click="startEdit(cursa)">
                                    Editează
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-lg" @click="requestDelete(cursa)">
                                    Șterge
                                </button>
                            </div>
                        </div>
                        <div v-if="cursa.observatii" class="mt-3 text-muted">
                            {{ cursa.observatii }}
                        </div>
                    </article>
                </div>
                <div v-else class="alert alert-light border rounded-4">
                    Nu au fost adăugate curse pentru această valabilitate.
                </div>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import LocalitateAutocomplete from './LocalitateAutocomplete.vue';

const props = defineProps({
    valabilitate: {
        type: Object,
        required: true,
    },
    countries: {
        type: Array,
        default: () => [],
    },
    romaniaId: {
        type: [Number, null],
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    createCursa: {
        type: Function,
        required: true,
    },
    updateCursa: {
        type: Function,
        required: true,
    },
    deleteCursa: {
        type: Function,
        required: true,
    },
    localitatiEndpoint: {
        type: String,
        required: true,
    },
});

const formMode = ref(null);
const form = reactive(defaultForm());
const formErrors = reactive({});
const generalError = ref('');
const manualTimeToggle = ref(false);

const isFormVisible = computed(() => formMode.value !== null);
const formTitle = computed(() => (formMode.value === 'edit' ? 'Editează cursa' : 'Adaugă cursă'));

const intervalLabel = computed(() => {
    const start = props.valabilitate.data_inceput ? new Date(props.valabilitate.data_inceput) : null;
    const end = props.valabilitate.data_sfarsit ? new Date(props.valabilitate.data_sfarsit) : null;
    const options = { year: 'numeric', month: 'long', day: 'numeric' };

    if (start && end) {
        return `${start.toLocaleDateString(undefined, options)} – ${end.toLocaleDateString(undefined, options)}`;
    }

    if (start) {
        return `Din ${start.toLocaleDateString(undefined, options)}`;
    }

    if (end) {
        return `Până la ${end.toLocaleDateString(undefined, options)}`;
    }

    return 'Interval necunoscut';
});

const requiresOra = computed(() => {
    if (! isFormVisible.value) {
        return false;
    }

    const descarcareId = form.descarcare_tara_id ? parseInt(form.descarcare_tara_id, 10) : null;

    if (props.romaniaId !== null && descarcareId === props.romaniaId) {
        return true;
    }

    if (formMode.value === 'create') {
        return ! (props.valabilitate.has_curse ?? false);
    }

    if (formMode.value === 'edit') {
        return props.valabilitate.first_cursa_id === form.id;
    }

    return false;
});

const showTimeField = computed(() => requiresOra.value || manualTimeToggle.value || !!form.data_cursa_time);

watch(
    () => props.valabilitate,
    () => {
        resetForm();
    },
    { deep: true },
);

watch(
    () => form.descarcare_tara_id,
    (newValue, oldValue) => {
        if (! newValue || props.romaniaId === null) {
            return;
        }

        const parsedNew = parseInt(newValue, 10);
        const parsedOld = oldValue ? parseInt(oldValue, 10) : null;

        if (parsedNew === props.romaniaId && parsedOld !== props.romaniaId) {
            const confirmed = window.confirm('Descărcarea în România necesită completarea orei. Continuați?');

            if (! confirmed) {
                form.descarcare_tara_id = oldValue ?? '';
            }
        }
    },
);

function defaultForm() {
    return {
        id: null,
        incarcare_localitate: '',
        incarcare_cod_postal: '',
        incarcare_tara_id: '',
        descarcare_localitate: '',
        descarcare_cod_postal: '',
        descarcare_tara_id: '',
        data_cursa_date: '',
        data_cursa_time: '',
        observatii: '',
        km_bord: '',
    };
}

function resetForm() {
    Object.assign(form, defaultForm());
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);
    generalError.value = '';
    formMode.value = null;
    manualTimeToggle.value = false;
}

function startCreate() {
    resetForm();
    formMode.value = 'create';
}

function startEdit(cursa) {
    resetForm();
    formMode.value = 'edit';
    form.id = cursa.id;
    form.incarcare_localitate = cursa.incarcare_localitate ?? '';
    form.incarcare_cod_postal = cursa.incarcare_cod_postal ?? '';
    form.incarcare_tara_id = cursa.incarcare_tara_id ? String(cursa.incarcare_tara_id) : '';
    form.descarcare_localitate = cursa.descarcare_localitate ?? '';
    form.descarcare_cod_postal = cursa.descarcare_cod_postal ?? '';
    form.descarcare_tara_id = cursa.descarcare_tara_id ? String(cursa.descarcare_tara_id) : '';
    form.data_cursa_date = cursa.data_date ?? '';
    form.data_cursa_time = cursa.data_time ?? '';
    form.observatii = cursa.observatii ?? '';
    form.km_bord = cursa.km_bord ?? '';
    manualTimeToggle.value = !! form.data_cursa_time;
}

function cancelForm() {
    resetForm();
}

function resolveError(field) {
    if (formErrors[field]) {
        return formErrors[field][0];
    }

    if (formErrors.data_cursa) {
        return formErrors.data_cursa[0];
    }

    return null;
}

async function submitForm() {
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);
    generalError.value = '';

    const payload = {
        incarcare_localitate: form.incarcare_localitate,
        incarcare_cod_postal: form.incarcare_cod_postal,
        incarcare_tara_id: form.incarcare_tara_id || null,
        descarcare_localitate: form.descarcare_localitate,
        descarcare_cod_postal: form.descarcare_cod_postal,
        descarcare_tara_id: form.descarcare_tara_id || null,
        data_cursa_date: form.data_cursa_date || null,
        data_cursa_time: showTimeField.value ? (form.data_cursa_time || null) : null,
        observatii: form.observatii,
        km_bord: form.km_bord === '' ? null : form.km_bord,
    };

    try {
        if (formMode.value === 'edit') {
            await props.updateCursa(form.id, payload);
        } else {
            await props.createCursa(payload);
        }

        resetForm();
    } catch (error) {
        if (error?.type === 'validation') {
            Object.assign(formErrors, error.errors ?? {});
            generalError.value = 'Verificați câmpurile marcate și încercați din nou.';
        } else {
            generalError.value = 'A apărut o eroare neașteptată. Încercați mai târziu.';
            console.error(error);
        }
    }
}

async function requestDelete(cursa) {
    const confirmed = window.confirm('Sigur doriți să ștergeți această cursă?');

    if (! confirmed) {
        return;
    }

    try {
        await props.deleteCursa(cursa.id);
    } catch (error) {
        console.error(error);
        generalError.value = 'Cursa nu a putut fi ștearsă. Încercați din nou.';
    }
}

function formatDateTime(cursa) {
    if (! cursa.data_date) {
        return 'Dată necompletată';
    }

    const date = new Date(`${cursa.data_date}T${cursa.data_time ?? '00:00'}`);
    const dateOptions = { year: 'numeric', month: 'short', day: 'numeric' };

    if (cursa.data_time) {
        return `${date.toLocaleDateString(undefined, dateOptions)} · ${cursa.data_time}`;
    }

    return date.toLocaleDateString(undefined, dateOptions);
}
</script>
