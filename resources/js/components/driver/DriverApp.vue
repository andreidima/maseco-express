<template>
    <div class="driver-app">
        <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
        </div>

        <div class="row gy-4">
            <div class="col-12 col-lg-4">
                <ValabilitateList
                    :valabilitati="valabilitati"
                    :loading="listLoading"
                    :selected-id="selectedId"
                    @select="handleSelect"
                    @refresh="fetchValabilitati"
                />
            </div>
            <div class="col-12 col-lg-8">
                <ValabilitateDetail
                    v-if="activeValabilitate"
                    :valabilitate="activeValabilitate"
                    :countries="tari"
                    :romania-id="romaniaId"
                    :loading="detailLoading"
                    :saving="mutationInFlight"
                    :create-cursa="createCursa"
                    :update-cursa="updateCursa"
                    :delete-cursa="deleteCursa"
                    :localitati-endpoint="localitatiEndpoint"
                />
                <div v-else-if="!listLoading" class="alert alert-info" role="status">
                    Nu există valabilități active în acest moment.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import ValabilitateList from './ValabilitateList.vue';
import ValabilitateDetail from './ValabilitateDetail.vue';

const props = defineProps({
    valabilitatiEndpoint: {
        type: String,
        required: true,
    },
    localitatiEndpoint: {
        type: String,
        required: true,
    },
    tari: {
        type: Array,
        default: () => [],
    },
    romaniaId: {
        type: [Number, null],
        default: null,
    },
    initialValabilitati: {
        type: Array,
        default: () => [],
    },
});

const listLoading = ref(false);
const detailLoading = ref(false);
const mutationInFlight = ref(false);
const error = ref('');

const valabilitati = ref(normalizeList(props.initialValabilitati));
const selectedId = ref(valabilitati.value[0]?.id ?? null);
const activeValabilitate = ref(null);

onMounted(() => {
    if (selectedId.value) {
        loadValabilitate(selectedId.value);
    } else {
        fetchValabilitati();
    }
});

watch(() => selectedId.value, (newId) => {
    if (! newId) {
        activeValabilitate.value = null;

        return;
    }

    loadValabilitate(newId);
});

function normalizeList(items) {
    return (items ?? []).map((item) => ({
        id: item.id,
        denumire: item.denumire,
        numar_auto: item.numar_auto,
        data_inceput: item.data_inceput ?? null,
        data_sfarsit: item.data_sfarsit ?? null,
        curse_count: item.curse_count ?? 0,
    }));
}

async function fetchValabilitati() {
    listLoading.value = true;
    error.value = '';

    try {
        const response = await window.axios.get(props.valabilitatiEndpoint);
        const payload = response?.data?.valabilitati ?? [];
        valabilitati.value = normalizeList(payload);

        if (! selectedId.value && valabilitati.value.length > 0) {
            selectedId.value = valabilitati.value[0].id;
        }
    } catch (exception) {
        console.error(exception);
        error.value = 'Nu am putut încărca lista de valabilități. Încercați din nou.';
    } finally {
        listLoading.value = false;
    }
}

async function loadValabilitate(id) {
    if (! id) {
        return;
    }

    detailLoading.value = true;
    error.value = '';

    try {
        const response = await window.axios.get(`${props.valabilitatiEndpoint}/${id}`);
        setActiveValabilitate(response?.data?.valabilitate ?? null);
    } catch (exception) {
        console.error(exception);
        error.value = 'Nu am putut încărca detaliile valabilității selectate.';
    } finally {
        detailLoading.value = false;
    }
}

function setActiveValabilitate(valabilitate) {
    if (! valabilitate) {
        activeValabilitate.value = null;

        return;
    }

    activeValabilitate.value = valabilitate;
    upsertSummary(valabilitate);
}

function upsertSummary(valabilitate) {
    const summary = {
        id: valabilitate.id,
        denumire: valabilitate.denumire,
        numar_auto: valabilitate.numar_auto,
        data_inceput: valabilitate.data_inceput,
        data_sfarsit: valabilitate.data_sfarsit,
        curse_count: valabilitate.curse_count ?? (valabilitate.curse?.length ?? 0),
    };

    const index = valabilitati.value.findIndex((item) => item.id === summary.id);

    if (index === -1) {
        valabilitati.value.push(summary);
    } else {
        valabilitati.value.splice(index, 1, summary);
    }
}

function handleSelect(id) {
    if (selectedId.value === id) {
        return;
    }

    selectedId.value = id;
}

async function createCursa(payload) {
    if (! activeValabilitate.value) {
        return;
    }

    mutationInFlight.value = true;

    try {
        const response = await window.axios.post(
            `${props.valabilitatiEndpoint}/${activeValabilitate.value.id}/curse`,
            payload,
        );

        setActiveValabilitate(response?.data?.valabilitate ?? null);
    } catch (exception) {
        if (exception?.response?.status === 422) {
            throw {
                type: 'validation',
                errors: exception.response.data.errors ?? {},
            };
        }

        console.error(exception);
        throw exception;
    } finally {
        mutationInFlight.value = false;
    }
}

async function updateCursa(cursaId, payload) {
    if (! activeValabilitate.value) {
        return;
    }

    mutationInFlight.value = true;

    try {
        const response = await window.axios.put(
            `${props.valabilitatiEndpoint}/${activeValabilitate.value.id}/curse/${cursaId}`,
            payload,
        );

        setActiveValabilitate(response?.data?.valabilitate ?? null);
    } catch (exception) {
        if (exception?.response?.status === 422) {
            throw {
                type: 'validation',
                errors: exception.response.data.errors ?? {},
            };
        }

        console.error(exception);
        throw exception;
    } finally {
        mutationInFlight.value = false;
    }
}

async function deleteCursa(cursaId) {
    if (! activeValabilitate.value) {
        return;
    }

    mutationInFlight.value = true;

    try {
        const response = await window.axios.delete(
            `${props.valabilitatiEndpoint}/${activeValabilitate.value.id}/curse/${cursaId}`,
        );

        setActiveValabilitate(response?.data?.valabilitate ?? null);
    } catch (exception) {
        console.error(exception);
        throw exception;
    } finally {
        mutationInFlight.value = false;
    }
}
</script>
