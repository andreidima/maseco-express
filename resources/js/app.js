/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

import '../sass/app.scss'
import '../css/andrei.css'

import { createApp } from 'vue';
// import { createApp } from 'vue/dist/vue.esm-bundler.js'

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

const app = createApp({});

import ExampleComponent from './components/ExampleComponent.vue';
// app.component('example-component', ExampleComponent);

import VueDatepickerNext from './components/DatePicker.vue';
// app.component('vue-datepicker-next', VueDatepickerNext);

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.component('vue-datepicker-next', VueDatepickerNext);

if (document.getElementById('app') != null) {
    app.mount('#app');
}



const camion = createApp({
    el: '#camion',
    data() {
        return {
            tip_camion: tipCamionVechi,
            tipuriCamioane: tipuriCamioane,
            tipuriCamioaneListaAutocomplete: [],

            firma_id: firmaIdVechi,
            firma_nume: '',
            firme: firme,
            firmeListaAutocomplete: []
        }
    },
    created: function () {
        if (this.firma_id) {
            for (var i = 0; i < this.firme.length; i++) {
                if (this.firme[i].id == this.firma_id) {
                    this.firma_nume = this.firme[i].nume;
                    break;
                }
            }
        }
    },
    methods: {
        autocompleteTipuriCamioane() {
            this.tipuriCamioaneListaAutocomplete = [];

            // var numarMaximDeElemente = 0;
            for (var i = 0; i < this.tipuriCamioane.length; i++) {
                if (this.tipuriCamioane[i].tip_camion && this.tipuriCamioane[i].tip_camion.toLowerCase().includes(this.tip_camion.toLowerCase())) {
                    this.tipuriCamioaneListaAutocomplete.push(this.tipuriCamioane[i]);
                    // numarMaximDeElemente++;
                }
                // if (numarMaximDeElemente >= 5){
                //     break;
                // }
            }
        },
        autocompleteFirme() {
            this.firmeListaAutocomplete = [];

            // var numarMaximDeElemente = 0;
            for (var i = 0; i < this.firme.length; i++) {
                if (this.firme[i].nume && this.firme[i].nume.toLowerCase().includes(this.firma_nume.toLowerCase())) {
                    this.firmeListaAutocomplete.push(this.firme[i]);
                    // numarMaximDeElemente++;
                }
                // if (numarMaximDeElemente >= 5) {
                //     break;
                // }
            }
        },
    }
});

if (document.getElementById('camion') != null) {
    camion.mount('#camion');
}





const formularComanda = createApp({
    el: '#formularComanda',
    data() {
        return {
            firmaTransportatorId: firmaTransportatorIdVechi,
            firmaTransportatorNume: '',
            firmeTransportatori: firmeTransportatori,
            firmeTransportatoriListaAutocomplete: [],

            firmaClientId: firmaClientIdVechi,
            firmaClientNume: '',
            firmeClienti: firmeClienti,
            firmeClientiListaAutocomplete: [],

            camionId: camionIdVechi,
            camionNumarInmatriculare: '',
            camionTipCamion: '',
            camioane: camioane,
            camioaneListaAutocomplete: [],

            locuriOperareIncarcari: [],
            locuriOperareDescarcari: [],
            incarcari: ((typeof incarcari !== 'undefined') ? incarcari : []),
            descarcari: ((typeof descarcari !== 'undefined') ? descarcari : []),
        }
    },
    created: function () {
        if (this.firmaTransportatorId) {
            for (var i = 0; i < this.firmeTransportatori.length; i++) {
                if (this.firmeTransportatori[i].id == this.firmaTransportatorId) {
                    this.firmaTransportatorNume = this.firmeTransportatori[i].nume;
                }
            }
        }
        if (this.firmaClientId) {
            for (var i = 0; i < this.firmeClienti.length; i++) {
                if (this.firmeClienti[i].id == this.firmaClientId) {
                    this.firmaClientNume = this.firmeClienti[i].nume;
                }
            }
        }
        if (this.camionId) {
            for (var i = 0; i < this.camioane.length; i++) {
                if (this.camioane[i].id == this.camionId) {
                    this.camionNumarInmatriculare = this.camioane[i].numar_inmatriculare;
                    this.camionTipCamion = this.camioane[i].tip_camion;
                }
            }
        }
    },
    methods: {
        autocompleteFirmeTransportatori() {
            this.firmeTransportatoriListaAutocomplete = [];

            for (var i = 0; i < this.firmeTransportatori.length; i++) {
                if (this.firmeTransportatori[i].nume && this.firmeTransportatori[i].nume.toLowerCase().includes(this.firmaTransportatorNume.toLowerCase())) {
                    this.firmeTransportatoriListaAutocomplete.push(this.firmeTransportatori[i]);
                }
            }
        },
        autocompleteFirmeClienti() {
            this.firmeClientiListaAutocomplete = [];

            for (var i = 0; i < this.firmeClienti.length; i++) {
                if (this.firmeClienti[i].nume && this.firmeClienti[i].nume.toLowerCase().includes(this.firmaClientNume.toLowerCase())) {
                    this.firmeClientiListaAutocomplete.push(this.firmeClienti[i]);
                }
            }
        },
        autocompleteCamioane() {
            this.camioaneListaAutocomplete = [];

            for (var i = 0; i < this.camioane.length; i++) {
                if (this.camioane[i].numar_inmatriculare && this.camioane[i].numar_inmatriculare.toLowerCase().includes(this.camionNumarInmatriculare.toLowerCase())) {
                    this.camioaneListaAutocomplete.push(this.camioane[i]);
                }
            }
        },
        // getLocuriOperareIncarcari(incarcare, value, categorie) {
        getLocuriOperareIncarcari(incarcare) {
            this.locuriOperareIncarcari = [];
            // console.log(incarcare, value, categorie);
            // console.log(incarcari[incarcare].nume, incarcari[incarcare].oras);
            // if (value.length > 2) {
            if ((incarcari[incarcare].nume && incarcari[incarcare].nume.length > 2) || (incarcari[incarcare].oras && (incarcari[incarcare].oras.length > 2))) {
                axios.get('/axios/locuri-operare', {
                    params: {
                        request: 'locuriOperare',
                        // nume: value,
                        // categorie: categorie,
                        nume: incarcari[incarcare].nume,
                        oras: incarcari[incarcare].oras,
                    }
                })
                .then(
                    response => (this.locuriOperareIncarcari[incarcare] = response.data.raspuns)
                );
            }
        },
        // getLocuriOperareDescarcari(descarcare, value, categorie) {
        getLocuriOperareDescarcari(descarcare) {
            this.locuriOperareDescarcari = [];
            // if (value.length > 2) {
            if ((descarcari[descarcare].nume && descarcari[descarcare].nume.length > 2) || (descarcari[descarcare].oras && (descarcari[descarcare].oras.length > 2))) {
                axios.get('/axios/locuri-operare', {
                    params: {
                        request: 'locuriOperare',
                        // nume: value,
                        // categorie: categorie,
                        nume: descarcari[descarcare].nume,
                        oras: descarcari[descarcare].oras,
                    }
                })
                    .then(
                        response => (this.locuriOperareDescarcari[descarcare] = response.data.raspuns)
                    );
            }
        },
        adaugaIncarcareGoala() {
            let locOperare =
                {
                    // id : '',
                    // nume: '',
                    // oras: '',
                    // tara: { id: '', nume: '' },
                    tara: {},
                    pivot: {},
                };
            this.incarcari.push(locOperare);
        },
        adaugaDescarcareGoala() {
            let locOperare =
                {
                    // id : '',
                    // nume: '',
                    // oras: '',
                    tara: { id: '', nume: '' },
                    pivot: {}
                };
            this.descarcari.push(locOperare);
        },
    }
});

const clickOutside = {
    beforeMount: (el, binding) => {
        el.clickOutsideEvent = event => {
            if (!(el == event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted: el => {
        document.removeEventListener("click", el.clickOutsideEvent);
    },
};

formularComanda.directive("clickOut", clickOutside);
formularComanda.component('vue-datepicker-next', VueDatepickerNext);

if (document.getElementById('formularComanda') != null) {
    formularComanda.mount('#formularComanda');
}


// Incarcare statusuri in pagina principala
const statusuri = createApp({
    el: '#statusuri',
    data() {
        return {
            statusuri: [],
            timer: null,
            mesajLipsaStatusuri: '',

            comandaId: '',
        }
    },
    created: function () {
        // this.getStatusuri();
    },
    mounted: function () {
        this.timer = setInterval(() => {
            this.getStatusuri();
        }, 10000)
    },
    beforeUnmount() {
        clearInterval(this.timer)
    },
    methods: {
        getStatusuri() {
            if (this.comandaId !== '') {
                axios.get('/axios/statusuri', {
                        params: { comanda_id: this.comandaId }
                    })
                    .then(
                        response => {
                            this.statusuri = response.data.raspuns;
                            if (this.statusuri.length === 0) {
                                this.mesajLipsaStatusuri = 'Nu există „statusuri” în baza de date pentru această comandă';
                            } else {
                                this.mesajLipsaStatusuri = '';
                            }
                        }
                    );
            } else {
            }
        },
    }
});

if (document.getElementById('statusuri') != null) {
    statusuri.mount('#statusuri');
}



// Incarcare alerte la mementouri
const mementoAlerte = createApp({
    el: '#mementoAlerte',
    data() {
        return {
            dataSelectata: '',
            dateSelectate: dateSelectate,
        }
    },
    methods: {
        captureDataDeLaCopil(data){
            this.dataSelectata = data;
        },
        adaugaAlerta: function () {
            if ((this.dataSelectata !== null) && (!this.dateSelectate.includes(this.dataSelectata))) {
                this.dateSelectate.push(this.dataSelectata);
                // var data = new Date(this.dataSelectata);
                // this.dateSelectate.push(data.toLocaleDateString("ro-RO"));
            }
        },
        stergeAlerta: function (data) {
            for (var i = 0; i < this.dateSelectate.length; i++) {
                if (this.dateSelectate[i] == data) {
                    this.dateSelectate.splice(i, 1);
                    break;
                }
            }
        },
    }
});
mementoAlerte.component('vue-datepicker-next', VueDatepickerNext);
if (document.getElementById('mementoAlerte') != null) {
    mementoAlerte.mount('#mementoAlerte');
}



// Creare factura
const creareFactura = createApp({
    el: '#creareFactura',
    data() {
        return {
            serieSiNumarDeCautat: '',
            comandaGasita: '',
            afisareMesajAtentionareNegasireComanda: false,

            comandaId: comandaId,
            client: client,
            cif: cif,
            adresa: adresa,
            tara: tara,
            produse: produse,
            valoare_contract: valoare_contract,
            moneda: moneda,
            procent_tva: procent_tva,
            zile_scadente: zile_scadente,
        }
    },
    methods: {
        axiosCautaComanda() {
            axios
                .post('/facturi/axios/cauta-comanda',
                    {
                        serieSiNumarDeCautat: this.serieSiNumarDeCautat
                    },
                    {
                        params: {
                            // request: 'actualizareSuma',
                        }
                    })
                .then(response => {
                    this.comandaGasita = response.data.comanda;

                    // Daca nu se gaseste comanda, se afiseaza mesaj de atentionare
                    if (this.comandaGasita && this.comandaGasita.client) {
                        this.afisareMesajAtentionareNegasireComanda = false;

                        this.comandaId = this.comandaGasita.id
                        this.client = this.comandaGasita.client.nume;
                        this.cif = this.comandaGasita.client.cif;
                        this.adresa = this.comandaGasita.client.adresa;
                        this.tara = this.comandaGasita.client.tara ? this.comandaGasita.client.tara.nume : '';

                        this.produse = this.comandaGasita.client_contract ? (this.client_contract + ' // ') : '';
                        for (var i = 0; i < this.comandaGasita.locuri_operare_incarcari.length; i++) {
                            if (this.comandaGasita.locuri_operare_incarcari[i].pivot){
                                this.produse += this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(8, 10) + '.' + this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(5, 7) + '.' + this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(0, 4);
                            }
                            this.produse += ' ' + this.comandaGasita.locuri_operare_incarcari[i].oras;
                            this.produse += ' ' + this.comandaGasita.locuri_operare_incarcari[i].tara.nume + ' / ';
                        }
                        for (var i = 0; i < this.comandaGasita.locuri_operare_descarcari.length; i++) {
                            if (this.comandaGasita.locuri_operare_descarcari[i].pivot) {
                                this.produse += this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(8, 10) + '.' + this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(5, 7) + '.' + this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(0, 4);
                            }
                            this.produse += ' ' + this.comandaGasita.locuri_operare_descarcari[i].oras;
                            this.produse += ' ' + this.comandaGasita.locuri_operare_descarcari[i].tara.nume;
                            if (i < this.comandaGasita.locuri_operare_descarcari.length - 1){
                                this.produse += ' / '
                            }
                        }

                        this.valoare_contract = this.comandaGasita.client_valoare_contract;
                        if (this.comandaGasita.client_moneda){
                            this.moneda = this.comandaGasita.client_moneda.id;
                        }
                        if (this.comandaGasita.client_procent_tva){
                            this.procent_tva = this.comandaGasita.client_procent_tva.nume;
                        }
                        this.zile_scadente = this.comandaGasita.client_zile_scadente;
                    } else {
                        this.afisareMesajAtentionareNegasireComanda = true;
                    }

                });
        },
    }
});
creareFactura.component('vue-datepicker-next', VueDatepickerNext);
if (document.getElementById('creareFactura') != null) {
    creareFactura.mount('#creareFactura');
}
