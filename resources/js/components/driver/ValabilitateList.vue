<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fs-5 mb-0">Valabilitățile mele</h2>
            <button class="btn btn-sm btn-outline-primary" type="button" @click="$emit('refresh')" :disabled="loading">
                <i class="fa-solid fa-rotate"></i>
                <span class="ms-1">Actualizează</span>
            </button>
        </div>

        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Încărcare...</span>
            </div>
        </div>

        <div v-else-if="valabilitati.length === 0" class="alert alert-light border rounded-4" role="status">
            Nu există valabilități active alocate utilizatorului curent.
        </div>

        <div v-else class="driver-scroll pe-1">
            <button
                v-for="item in valabilitati"
                :key="item.id"
                type="button"
                class="driver-card-button w-100 text-start p-4 mb-3"
                :class="{ active: item.id === selectedId }"
                @click="$emit('select', item.id)"
            >
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="d-block fw-semibold">{{ item.denumire }}</span>
                        <span class="text-muted small">{{ item.numar_auto }}</span>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-2">
                        {{ item.curse_count }} {{ item.curse_count === 1 ? 'cursă' : 'curse' }}
                    </span>
                </div>
                <div class="mt-3 text-muted small">
                    {{ formatInterval(item) }}
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    valabilitati: {
        type: Array,
        default: () => [],
    },
    selectedId: {
        type: Number,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

function formatInterval(item) {
    const start = item.data_inceput ? new Date(item.data_inceput) : null;
    const end = item.data_sfarsit ? new Date(item.data_sfarsit) : null;

    const formatOptions = { year: 'numeric', month: 'short', day: 'numeric' };

    if (start && end) {
        return `${start.toLocaleDateString(undefined, formatOptions)} – ${end.toLocaleDateString(undefined, formatOptions)}`;
    }

    if (start) {
        return `Din ${start.toLocaleDateString(undefined, formatOptions)}`;
    }

    if (end) {
        return `Până la ${end.toLocaleDateString(undefined, formatOptions)}`;
    }

    return 'Interval necunoscut';
}
</script>
