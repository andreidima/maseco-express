import './bootstrap';
import '../sass/driver.scss';

import { createApp } from 'vue';
import DriverApp from './components/driver/DriverApp.vue';

const element = document.getElementById('driver-app');

if (element) {
    const props = {
        valabilitatiEndpoint: element.dataset.valabilitatiEndpoint,
        localitatiEndpoint: element.dataset.localitatiEndpoint,
        tari: [],
        romaniaId: null,
        initialValabilitati: [],
    };

    if (element.dataset.tari) {
        try {
            props.tari = JSON.parse(element.dataset.tari);
        } catch (error) {
            console.error('Nu s-au putut încărca țările', error);
        }
    }

    if (element.dataset.romaniaId) {
        const parsed = parseInt(element.dataset.romaniaId, 10);
        props.romaniaId = Number.isNaN(parsed) ? null : parsed;
    }

    if (element.dataset.initialValabilitati) {
        try {
            props.initialValabilitati = JSON.parse(element.dataset.initialValabilitati);
        } catch (error) {
            console.warn('Datele inițiale pentru valabilități nu au putut fi interpretate.', error);
        }
    }

    createApp(DriverApp, props).mount(element);
}
