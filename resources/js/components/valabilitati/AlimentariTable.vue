<template>
    <div class="table-responsive">
        <table class="table table-sm alimentari-table align-middle">
            <thead>
                <tr>
                    <th>Dată / oră alimentare</th>
                    <th class="text-end">Litrii</th>
                    <th class="text-end">Preț / litru</th>
                    <th class="text-end">Total preț</th>
                    <th class="actions-column text-center">Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!rowsState.length">
                    <td colspan="5" class="text-center py-4">Nu există alimentări salvate.</td>
                </tr>
                <tr v-for="row in rowsState" :key="row.id">
                    <td class="fw-semibold">
                        <div class="d-flex align-items-center gap-2">
                            <input
                                type="datetime-local"
                                class="form-control form-control-sm"
                                v-model="row.editValues.data_ora_alimentare"
                                @blur="triggerSave(row.id, 'data_ora_alimentare')"
                                @keydown.enter.prevent="triggerSave(row.id, 'data_ora_alimentare')"
                                :aria-label="`Data alimentare pentru rândul ${row.id}`"
                            />
                            <StatusBadge :state="row.statuses.data_ora_alimentare" />
                        </div>
                        <small class="text-muted">{{ row.displayValues.data_ora_alimentare }}</small>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control form-control-sm text-end"
                                v-model="row.editValues.litrii"
                                @blur="triggerSave(row.id, 'litrii')"
                                @keydown.enter.prevent="triggerSave(row.id, 'litrii')"
                                :aria-label="`Litrii pentru rândul ${row.id}`"
                            />
                            <StatusBadge :state="row.statuses.litrii" />
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                class="form-control form-control-sm text-end"
                                v-model="row.editValues.pret_pe_litru"
                                @blur="triggerSave(row.id, 'pret_pe_litru')"
                                @keydown.enter.prevent="triggerSave(row.id, 'pret_pe_litru')"
                                :aria-label="`Preț pe litru pentru rândul ${row.id}`"
                            />
                            <StatusBadge :state="row.statuses.pret_pe_litru" />
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <input
                                type="number"
                                step="0.0001"
                                min="0"
                                class="form-control form-control-sm text-end"
                                v-model="row.editValues.total_pret"
                                @blur="triggerSave(row.id, 'total_pret')"
                                @keydown.enter.prevent="triggerSave(row.id, 'total_pret')"
                                :aria-label="`Total preț pentru rândul ${row.id}`"
                            />
                            <StatusBadge :state="row.statuses.total_pret" />
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center flex-wrap alimentari-actions">
                            <button
                                class="btn btn-sm btn-outline-primary border border-dark"
                                type="button"
                                data-bs-toggle="modal"
                                :data-bs-target="`#alimentareEditModal-${row.id}`"
                            >
                                <i class="fa-solid fa-pen-to-square me-1"></i>
                            </button>
                            <form
                                method="POST"
                                :action="row.delete_url"
                                class="d-inline"
                                @submit.prevent="confirmDelete($event)"
                            >
                                <input type="hidden" name="_token" :value="csrfToken">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger border border-dark">
                                    <i class="fa-solid fa-trash me-1"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { defineComponent, reactive } from 'vue';

const props = defineProps({
    rows: {
        type: Array,
        default: () => [],
    },
    csrfToken: {
        type: String,
        required: true,
    },
});

const editableFields = ['data_ora_alimentare', 'litrii', 'pret_pe_litru', 'total_pret'];

const rowsState = reactive(
    props.rows.map((row) => ({
        ...row,
        values: { ...row.values },
        displayValues: { ...row.display_values },
        editValues: { ...row.values },
        statuses: editableFields.reduce((acc, field) => {
            acc[field] = { status: null, message: '' };
            return acc;
        }, {}),
    })),
);

const StatusBadge = defineComponent({
    name: 'StatusBadge',
    props: {
        state: {
            type: Object,
            required: true,
        },
    },
    template: `
        <span v-if="state.status === 'saved'" class="text-success small">✓</span>
        <span v-else-if="state.status === 'saving'" class="text-muted small">...</span>
        <span v-else-if="state.status === 'error'" class="text-danger small" :title="state.message">⚠</span>
    `,
});

const formatNumber = (value, decimals = 2) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const trimmed = Number(value).toFixed(decimals).replace(/\.0+$/, '').replace(/\.([0-9]*?)0+$/, '.$1');
    return trimmed === '-0' ? '0' : trimmed;
};

const triggerSave = (rowId, field) => {
    const row = rowsState.find((item) => item.id === rowId);
    if (!row || !editableFields.includes(field)) {
        return;
    }

    const newValue = row.editValues[field];
    const previousValue = row.values[field];

    if (`${newValue}` === `${previousValue}`) {
        return;
    }

    saveField(row, field, newValue);
};

const saveField = async (row, field, value) => {
    row.statuses[field] = { status: 'saving', message: '' };

    try {
        const response = await fetch(row.update_url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
            },
            body: JSON.stringify({ field, value }),
        });

        const data = await response.json();

        if (!response.ok || data.status !== 'success') {
            throw new Error(data.message || 'Nu am putut salva câmpul.');
        }

        const savedValue = data.value ?? value;

        row.values[field] = savedValue;
        row.editValues[field] = savedValue;
        row.displayValues[field] = data.displayValue ?? row.displayValues[field];

        if (['litrii', 'pret_pe_litru', 'total_pret'].includes(field)) {
            row.editValues[field] = formatNumber(value, field === 'litrii' ? 2 : 4);
        }

        row.statuses[field] = { status: 'saved', message: 'Salvat' };
        setTimeout(() => {
            row.statuses[field] = { status: null, message: '' };
        }, 1500);
    } catch (error) {
        row.editValues[field] = row.values[field];
        row.statuses[field] = { status: 'error', message: error.message };
    }
};

const confirmDelete = (event) => {
    const formElement = event.target?.closest('form') ?? event.target;

    if (formElement && typeof formElement.submit === 'function' && confirm('Sigur ștergi această alimentare?')) {
        formElement.submit();
    }
};
</script>
