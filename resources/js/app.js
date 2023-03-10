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

            // locuriOperare: ((typeof locuriOperare !== 'undefined') ? locuriOperare : []),
            locuriOperareIncarcari: [],
            locuriOperareDescarcari: [],
            // locuriOperareListaAutocomplete: [],

            // Incarcari
            incarcari: ((typeof incarcari !== 'undefined') ? incarcari : []),
            descarcari: ((typeof descarcari !== 'undefined') ? descarcari : []),
            // numarIncarcari: ((typeof numarIncarcari !== 'undefined') ? numarIncarcari : 0),
            // incarcariId: ((typeof incarcariId !== 'undefined') ? incarcariId : []),
            // incarcariNume: ((typeof incarcariNume !== 'undefined') ? incarcariNume : []),
            // incarcariJudet: ((typeof incarcariJudet !== 'undefined') ? incarcariJudet : []),
            // incarcariOras: ((typeof incarcariOras !== 'undefined') ? incarcariOras : []),
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

        // Incarcari
        // this.incarcari[1] = ['a'];
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
        // autocompleteLocuriOperare(incarcare, value) {
        //     if (value.length <= 2) {
        //         this.locuriOperareListaAutocomplete = [];
        //     } else if (value.length > 2){
        //         this.locuriOperareListaAutocomplete = [];
        //         let locuriOperareListaAutocomplete = [];

        //         for (var i = 0; i < this.locuriOperare.length; i++) {
        //             if (this.locuriOperare[i].nume && this.locuriOperare[i].nume.toLowerCase().includes(value.toLowerCase())) {
        //                 locuriOperareListaAutocomplete.push(this.locuriOperare[i]);
        //             }
        //             if (locuriOperareListaAutocomplete.length >= 100){
        //                 break;
        //             }
        //         }
        //         this.locuriOperareListaAutocomplete[incarcare] = locuriOperareListaAutocomplete;
        //     }
        // },
        getLocuriOperareIncarcari(incarcare, value) {
            this.locuriOperareIncarcari = [];
            if (value.length > 2) {
                axios.get('/axios/locuri-operare', {
                    params: {
                        request: 'locuriOperare',
                        nume: value,
                    }
                })
                .then(
                    response => (this.locuriOperareIncarcari[incarcare] = response.data.raspuns)
                );
            }
        },
        getLocuriOperareDescarcari(descarcare, value) {
            this.locuriOperareDescarcari = [];
            if (value.length > 2) {
                axios.get('/axios/locuri-operare', {
                    params: {
                        request: 'locuriOperare',
                        nume: value,
                    }
                })
                    .then(
                        response => (this.locuriOperareDescarcari[descarcare] = response.data.raspuns)
                    );
            }
        },
        // golireCampuriIncarcari(incarcare) {
        //     delete this.incarcariId[incarcare];
        //     delete this.incarcariNume[incarcare];
        //     delete this.incarcariJudet[incarcare];
        //     delete this.incarcariOras[incarcare];
        // },
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
        // golireListe() {
        //     this.firmeTransportatoriListaAutocomplete = [];
        //     this.firmeClientiListaAutocomplete = [];
        //     this.camioaneListaAutocomplete = [];
        // },
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
        }, 5000)
    },
    beforeUnmount() {
        clearInterval(this.timer)
    },
    // watch: {
    //     comandaId() {
    //         if (this.comandaId !== '') {
    //             this.getStatusuri();
    //         } else {
    //             this.statusuri = [];
    //         }
    //     }
    // },
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
                                this.mesajLipsaStatusuri = 'Nu exist?? ???statusuri??? ??n baza de date pentru aceast?? comand??';
                            } else {
                                this.mesajLipsaStatusuri = '';
                            }
                        }
                    );
            } else {
                // this.statusuri = [];
            }
        },
    }
});

if (document.getElementById('statusuri') != null) {
    statusuri.mount('#statusuri');
}
