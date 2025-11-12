<template>
    <div class="driver-autocomplete">
        <label :for="id" class="form-label">{{ label }}</label>
        <input
            :id="id"
            type="text"
            class="form-control"
            :placeholder="placeholder"
            :value="modelValue"
            :disabled="disabled"
            @input="onInput"
            @focus="handleFocus"
            @blur="handleBlur"
            autocomplete="off"
        >
        <div v-if="showSuggestions" class="driver-autocomplete__list mt-1">
            <button
                v-for="item in suggestions"
                :key="item"
                type="button"
                class="driver-autocomplete__item w-100 text-start bg-transparent border-0"
                @mousedown.prevent="select(item)"
            >
                {{ item }}
            </button>
            <div v-if="suggestions.length === 0" class="px-3 py-2 text-muted small">
                Nu am găsit rezultate pentru "{{ modelValue }}".
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, ref } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        required: true,
    },
    endpoint: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const suggestions = ref([]);
const showSuggestions = ref(false);
let active = true;

const performSearch = debounce(async (query) => {
    if (! query || query.trim().length < 2) {
        suggestions.value = [];

        return;
    }

    try {
        const response = await window.axios.get(props.endpoint, {
            params: { term: query },
        });

        if (! active) {
            return;
        }

        suggestions.value = response?.data?.localitati ?? [];
    } catch (error) {
        console.error('Autocomplete localitate', error);
        suggestions.value = [];
    }
}, 200);

onBeforeUnmount(() => {
    active = false;
});

function onInput(event) {
    const value = event.target.value;
    emit('update:modelValue', value);
    performSearch(value);
}

function handleFocus(event) {
    showSuggestions.value = true;
    performSearch(event.target.value);
}

function handleBlur() {
    setTimeout(() => {
        showSuggestions.value = false;
    }, 120);
}

function select(value) {
    emit('update:modelValue', value);
    showSuggestions.value = false;
}
</script>
