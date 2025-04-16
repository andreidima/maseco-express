/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

import '../sass/app.scss'
import '../css/andrei.css'


/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

import { createApp } from 'vue';

// Import other components
import VueDatepickerNext from './components/DatePicker.vue';
import TiptapEditor from './components/TiptapEditor.vue'; // Import your Tiptap component
import TogglePredat from './components/intermedieri/TogglePredat.vue';
import InlineObservatiiEditor from './components/keyPerformanceIndicators/InlineObservatiiEditor.vue';
import DirectoryTree from './components/fileManager/DirectoryTree.vue';
import JspreadsheetComponent from './components/JspreadsheetComponent.vue';

// Create Vue app
const app = createApp({});

// Register components globally (optional but recommended for reuse)
app.component('VueDatepickerNext', VueDatepickerNext);
// app.component('TiptapEditor', TiptapEditor);

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

if (document.getElementById('app') != null) {
    app.mount('#app');
}


// App pentru DatePicker
const datePicker = createApp({});
datePicker.component('vue-datepicker-next', VueDatepickerNext);
if (document.getElementById('datePicker') != null) {
    datePicker.mount('#datePicker');
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

            // Comented on 14.01.2025 - after that we went to more that one client to a command
            firmaClientId: firmaClientIdVechi,
            firmaClientNume: '',
            firmeClienti: firmeClienti,
            firmeClientiListaAutocomplete: [],
            // Added on 14.01.2025 - after that we went to more that one client to a command
            clientiListaTotiDinDB: [],
            clientiAtasatiLaComanda: ((typeof clientiAtasatiLaComanda !== 'undefined') ? clientiAtasatiLaComanda : []),

            camionId: camionIdVechi,
            camionNumarInmatriculare: '',
            camionTipCamion: '',
            camionPretKmGoi: '',
            camionPretKmPlini: '',
            camioane: camioane,
            camioaneListaAutocomplete: [],

            locuriOperareIncarcari: [],
            locuriOperareDescarcari: [],
            incarcari: ((typeof incarcari !== 'undefined') ? incarcari : []),
            descarcari: ((typeof descarcari !== 'undefined') ? descarcari : []),

            transportatorTarifPeKm: ((typeof transportatorTarifPeKmVechi !== 'undefined') ? transportatorTarifPeKmVechi : ''),
            transportatorPretKmGoi: ((typeof transportatorPretKmGoiVechi !== 'undefined') ? transportatorPretKmGoiVechi : ''),
            transportatorPretKmPlini: ((typeof transportatorPretKmPliniVechi !== 'undefined') ? transportatorPretKmPliniVechi : ''),
            transportatorKmGoi: ((typeof transportatorKmGoiVechi !== 'undefined') ? transportatorKmGoiVechi : ''),
            transportatorKmPlini: ((typeof transportatorKmPliniVechi !== 'undefined') ? transportatorKmPliniVechi : ''),
            transportatorValoareKmGoi: ((typeof transportatorValoareKmGoiVechi !== 'undefined') ? transportatorValoareKmGoiVechi : ''),
            transportatorValoareKmPlini: ((typeof transportatorValoareKmPliniVechi !== 'undefined') ? transportatorValoareKmPliniVechi : ''),
            transportatorValoareContract: ((typeof transportatorValoareContractVechi !== 'undefined') ? transportatorValoareContractVechi : ''),

            transportatorPretAutostrada: ((typeof transportatorPretAutostradaVechi !== 'undefined') ? transportatorPretAutostradaVechi : ''),
            transportatorPretFerry: ((typeof transportatorPretFerryVechi !== 'undefined') ? transportatorPretFerryVechi : ''),

            clientValoareContractInitiala: ((typeof clientValoareContractInitialaVechi !== 'undefined') ? clientValoareContractInitialaVechi : ''),
            clientValoareContract: ((typeof clientValoareContractVechi !== 'undefined') ? clientValoareContractVechi : ''),

            alertaCampuriNecompletate: '',
        }
    },
    created: function () {
        // console.log(this.transportatorTarifPeKm, "Asd");

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
                    this.camionPretKmGoi = this.camioane[i].pret_km_goi;
                    this.camionPretKmPlini = this.camioane[i].pret_km_plini;
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
        getClienti(client) {
            this.clientiListaTotiDinDB = [];
            // console.log(incarcare, value, categorie);
            // console.log(incarcari[incarcare].nume, incarcari[incarcare].oras);
            // if (value.length > 2) {
            if ((clientiAtasatiLaComanda[client].nume && clientiAtasatiLaComanda[client].nume.length > 2)) {
                axios.get('/axios/clienti', {
                    params: {
                        request: 'clienti',
                        nume: clientiAtasatiLaComanda[client].nume,
                    }
                })
                    .then(
                        response => (this.clientiListaTotiDinDB[client] = response.data.raspuns)
                    );
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
        adaugaClientGol() {
            let client =
            {
                // id : '',
                // nume: '',
                // oras: '',
                // tara: { id: '', nume: '' },
                tara: {},
                pivot: { moneda_id: 2 }
            };
            this.clientiAtasatiLaComanda.push(client);
        },
        adaugaIncarcareGoala() {
            let locOperare =
                {
                    // id : '',
                    // nume: '',
                    data_ora: 'qweqweqwe',
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
            tip: tip,

            dataExpirare: '',

            dataSelectata: '',
            dateSelectate: dateSelectate,
        }
    },
    methods: {
        captureDataExpirare(data) {
            if (this.tip == 2) { // RCA
                var zileInainte = [3, 15];
            } else if (this.tip == 3) { // ITP + rovinieta
                var zileInainte = [3, 30, 60];
            }

            if (zileInainte) {
                if (this.dataExpirare !== data) { // if dataExpirare was changed
                    this.dataExpirare = data;
                    this.dateSelectate = [];

                    for (var i = 0; i < zileInainte.length; i++) {
                        var dataAlerta = new Date(data); // we set him as date to could work with it
                        dataAlerta = dataAlerta.setDate(dataAlerta.getDate() - zileInainte[i]); // we remove from data a number of days (zileInainte[i])
                        dataAlerta = new Date(dataAlerta).toLocaleDateString('en-CA'); // we set the date toLocaleDateString('en-CA'), eg. yyyy-mm-dd, so it can be saved in the database
                        if ((dataAlerta !== null) && (!this.dateSelectate.includes(dataAlerta))) { // we add the date to dateSelectate
                            this.dateSelectate.push(dataAlerta);
                        }
                    }
                }
            }
        },
        captureDataDeLaCopil(data) {
            if (this.dataSelectata !== data) {
                this.dataSelectata = data;
                // if ((this.dataSelectata !== null) && (!this.dateSelectate.includes(this.dataSelectata))) {
                //     this.dateSelectate.push(this.dataSelectata);
                // }
            }
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
            // cautarea si aducerea clientului din baza de date
            firmeClienti: firmeClienti,
            firmeClientiListaAutocomplete: [],
            firmaClient: '',

            // datele clientului ce se adauga la factura
            client_id: client_id,
            client_nume: client_nume,
            client_reg_com: client_reg_com,
            client_cif: client_cif,
            client_adresa: client_adresa,
            client_tara_id: client_tara_id,
            client_telefon: client_telefon,
            client_email: client_email,

            // datele comenzii ce se adauga in lista de produse
            numarDeCautat: '',
            comandaGasita: '',
            afisareMesajAtentionareNegasireComanda: false,

            // detaliile produsului gasit - se creeaza denumirea
            produsGasitDenumire: '',

            // datele neesarea pentru adaugarea unui produs la factura
            produsDenumire: '',
            produsUm: '',
            produsCantitate: '',
            produsPret: '',
            // produsProcentTvaId: '',
            produsPretulIncludeTva: 0,

            comandaId: comandaId,
            produse: produse,

            moneda_id: moneda_id,

            procenteTva: procenteTva,
            procent_tva_id: procent_tva_id,

            zile_scadente: zile_scadente,

            showInfoAlerteScadenta: false, // arata sau ascunde din formular informatiile suplimentare despre cum trebuie completat campul alerte_scadenta
            showDateProdusIncomplete: '', // arata eroarea ca nu au fost completate corect toate datele unui produs

            total_fara_tva_moneda: 0,
            total_tva_moneda: 0,
            total_fara_tva_lei: 0,
            total_tva_lei: 0,

            chitanta_suma_incasata: chitanta_suma_incasata,

            dateFacturiIntocmitDeVechi: dateFacturiIntocmitDeVechi,
            dateFacturiDelegatVechi: dateFacturiDelegatVechi,
            dateFacturiMentiuniVechi: dateFacturiMentiuniVechi,
            dateFacturiIntocmitDeVechiListaAutocomplete: [],
            dateFacturiDelegatVechiListaAutocomplete: [],
            dateFacturiMentiuniVechiListaAutocomplete: [],
            intocmit_de: intocmit_de,
            cnp: cnp,
            delegat: delegat,
            buletin: buletin,
            auto: auto,
            mentiuni: mentiuni,
        }
    },
    watch: {
        produse: {
            handler: function (newValue) {
                this.calculeazaSumeTotale();
            },
            deep: true
        }
        // produse() {
        //     this.calculeazaSumeTotale();
        //     console.log('aa');
        // },
        // {deep : true},
    },
    created: function () {
        if (this.firmaClientId) {
            for (var i = 0; i < this.firmeClienti.length; i++) {
                if (this.firmeClienti[i].id == this.firmaClientId) {
                    this.client_nume = this.firmeClienti[i].nume;
                }
            }
        }

        this.calculeazaSumeTotale();
    },
    methods: {
        autocompleteFirmeClienti() {
            this.firmeClientiListaAutocomplete = [];

            for (var i = 0; i < this.firmeClienti.length; i++) {
                if (this.firmeClienti[i].nume && this.firmeClienti[i].nume.toLowerCase().includes(this.client_nume.toLowerCase())) {
                    this.firmeClientiListaAutocomplete.push(this.firmeClienti[i]);
                }
            }
        },
        axiosCautaClient(firmaId) {
            axios.get('/facturi/axios/cauta-client', {
                params: {
                    client_id: firmaId,
                }
            })
                .then(
                    response => {
                        if (response.data.client) {
                            this.firmaClient = response.data.client;
                            this.preiaDateClient();
                        }
                        this.firmeClientiListaAutocomplete = ''
                    }
                );
        },
        preiaDateClient() {
            if (this.firmaClient){
                this.client_id = this.firmaClient.id;
                this.client_nume = this.firmaClient.nume;
                this.client_reg_com = this.firmaClient.reg_com;
                this.client_cif = this.firmaClient.cif;
                this.client_adresa = this.firmaClient.adresa;
                this.client_tara_id = this.firmaClient.tara_id;
                this.client_email = this.firmaClient.email;
            }
        },
        preiaDateFacturare() {
            if (this.comandaGasita) {
                if (this.comandaGasita.client_moneda) {
                    this.moneda_id = this.comandaGasita.client_moneda.id;
                }
                this.procent_tva_id = this.comandaGasita.client_procent_tva_id;
                this.zile_scadente = this.comandaGasita.client_zile_scadente;
            }
        },
        preiaDateProdus() {
            if (this.comandaGasita) {
                this.produsDenumire = this.produsGasitDenumire;
                this.produsUm = 'um';
                this.produsCantitate = 1;
                this.produsPret = this.comandaGasita.client_valoare_contract;
                // this.produsProcentTvaId = this.comandaGasita.client_procent_tva_id;
                this.produsPretulIncludeTva = 0;
            }
        },
        adaugaProdusLaFactura() {
            // Se transforma din string in numere campurile necesare
            this.produsCantitate = parseInt(this.produsCantitate);
            this.produsPret = parseFloat(this.produsPret);

            // Se verifica daca datele sunt completate toate si corect
            this.showDateProdusIncomplete = '';
            if (!this.produsDenumire){
                this.showDateProdusIncomplete = 'Completați denumirea. ';
            } else if (!this.produsUm) {
                this.showDateProdusIncomplete = 'Completați unitatea de măsură. ';
            } else if (!this.produsCantitate) {
                this.showDateProdusIncomplete = 'Completați cantitatea. ';
            } else if (!this.produsPret) {
                this.showDateProdusIncomplete = 'Completați prețul. ';
            } else if (!this.procent_tva_id) {
                this.showDateProdusIncomplete = 'Alegeți procentul TVA. ';
            } else if (!Number.isInteger(this.produsCantitate)) {
                this.showDateProdusIncomplete += "Cantitatea trebuie să fie un număr întreg. ";
            } else if (typeof this.produsPret !== 'number') {
                this.showDateProdusIncomplete += "Prețul trebuie să fie un număr. ";
            } else {
                this.showDateProdusIncomplete = '';
            }

            // Daca variabila contine erori, se iese din functie
            if (this.showDateProdusIncomplete){
                return;
            }

            // Se afla care este procentul TVA in functie de id
            let procentTVA = 0;
            for (var i = 0; i < this.procenteTva.length; i++) {
                if (this.procenteTva[i].id == this.procent_tva_id) {
                    procentTVA = parseInt(this.procenteTva[i].nume);
                }
            }

            // Daca valorile care trebuie calculate nu sunt toate numere, se iese din functie
            if (isNaN(this.produsCantitate) || isNaN(this.produsPret) || isNaN(procentTVA)) {
                return;
            }


            let pret_unitar_fara_tva = 0;
            let valoare_tva = 0;
            if (procentTVA === 0){
                pret_unitar_fara_tva = this.produsPret;
                valoare_tva = 0;
            } else if (parseInt(this.produsPretulIncludeTva) === 0) {
                pret_unitar_fara_tva = this.produsPret;
                valoare_tva = this.produsPret * procentTVA / 100;
            } else if (parseInt(this.produsPretulIncludeTva) === 1) {
                valoare_tva = this.produsPret * procentTVA / 100;
                pret_unitar_fara_tva = this.produsPret - valoare_tva;
            }

            let produs =
            {
                comanda_id: this.comandaGasita.id,
                denumire: this.produsDenumire,
                um: 'buc',
                cantitate: this.produsCantitate,
                pret_unitar_fara_tva: pret_unitar_fara_tva,
                valoare: this.produsCantitate * pret_unitar_fara_tva,
                valoare_tva: this.produsCantitate * valoare_tva
            };
            this.produse.push(produs);
        },
        axiosCautaComanda() {
            axios
                .post('/facturi/axios/cauta-comanda',
                    {
                        numarDeCautat: this.numarDeCautat
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

                        this.firmaClient = this.comandaGasita.client;

                        // this.axiosCautaClient(this.comandaGasita.client.id);

                        this.comandaId = this.comandaGasita.id;

                        this.produsGasitDenumire = this.comandaGasita.client_contract ? (this.comandaGasita.client_contract + ' // ') : '';
                        for (var i = 0; i < this.comandaGasita.locuri_operare_incarcari.length; i++) {
                            if (this.comandaGasita.locuri_operare_incarcari[i].pivot && this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora){
                                this.produsGasitDenumire += this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(8, 10) + '.' + this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(5, 7) + '.' + this.comandaGasita.locuri_operare_incarcari[i].pivot.data_ora.slice(0, 4);
                            }
                            if (this.comandaGasita.locuri_operare_incarcari[i].oras){
                                this.produsGasitDenumire += ' ' + this.comandaGasita.locuri_operare_incarcari[i].oras;
                            }
                            if (this.comandaGasita.locuri_operare_incarcari[i].tara && this.comandaGasita.locuri_operare_incarcari[i].tara.nume) {
                                this.produsGasitDenumire += ' ' + this.comandaGasita.locuri_operare_incarcari[i].tara.nume + ' / ';
                            }
                        }
                        for (var i = 0; i < this.comandaGasita.locuri_operare_descarcari.length; i++) {
                            if (this.comandaGasita.locuri_operare_descarcari[i].pivot) {
                                this.produsGasitDenumire += this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(8, 10) + '.' + this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(5, 7) + '.' + this.comandaGasita.locuri_operare_descarcari[i].pivot.data_ora.slice(0, 4);
                            }
                            if (this.comandaGasita.locuri_operare_descarcari[i].oras) {
                                this.produsGasitDenumire += ' ' + this.comandaGasita.locuri_operare_descarcari[i].oras;
                            }
                            if (this.comandaGasita.locuri_operare_descarcari[i].tara && this.comandaGasita.locuri_operare_descarcari[i].tara.nume) {
                                this.produsGasitDenumire += ' ' + this.comandaGasita.locuri_operare_descarcari[i].tara.nume;
                            }
                            if (i < this.comandaGasita.locuri_operare_descarcari.length - 1){
                                this.produsGasitDenumire += ' / '
                            }
                        }
                    } else {
                        this.afisareMesajAtentionareNegasireComanda = true;
                    }

                });
        },
        calculeazaSumeTotale() {
            this.total_fara_tva_moneda = 0;
            this.total_tva_moneda = 0;
            this.total_fara_tva_lei = 0;
            this.total_tva_lei = 0;
            this.total_lei = 0;

            if (this.produse){
                for (var i = 0; i < this.produse.length; i++) {
                    this.total_fara_tva_moneda += parseFloat(this.produse[i].valoare);
                    this.total_tva_moneda += parseFloat(this.produse[i].valoare_tva);
                };

                // this.total_fara_tva_lei = 0;
                // this.total_tva_lei = 0;
                // this.total_lei = 0;
            }
        },
        autocompleteDateFacturiIntocmitDeVechi() {
            this.dateFacturiIntocmitDeVechiListaAutocomplete = [];

            for (var i = 0; i < this.dateFacturiIntocmitDeVechi.length; i++) {
                if (this.dateFacturiIntocmitDeVechi[i].intocmit_de && this.dateFacturiIntocmitDeVechi[i].intocmit_de.toLowerCase().includes(this.intocmit_de.toLowerCase())) {
                    this.dateFacturiIntocmitDeVechiListaAutocomplete.push(this.dateFacturiIntocmitDeVechi[i]);
                }
            }
        },
        autocompleteDateFacturiDelegatVechi() {
            this.dateFacturiDelegatVechiListaAutocomplete = [];

            for (var i = 0; i < this.dateFacturiDelegatVechi.length; i++) {
                if (this.dateFacturiDelegatVechi[i].delegat && this.dateFacturiDelegatVechi[i].delegat.toLowerCase().includes(this.delegat.toLowerCase())) {
                    this.dateFacturiDelegatVechiListaAutocomplete.push(this.dateFacturiDelegatVechi[i]);
                }
            }
        },
        autocompleteDateFacturiMentiuniVechi() {
            this.dateFacturiMentiuniVechiListaAutocomplete = [];

            for (var i = 0; i < this.dateFacturiMentiuniVechi.length; i++) {
                if (this.dateFacturiMentiuniVechi[i].mentiuni && this.dateFacturiMentiuniVechi[i].mentiuni.toLowerCase().includes(this.mentiuni.toLowerCase())) {
                    this.dateFacturiMentiuniVechiListaAutocomplete.push(this.dateFacturiMentiuniVechi[i]);
                }
            }
        },

    }
});

creareFactura.directive("clickOut", clickOutside);
creareFactura.component('vue-datepicker-next', VueDatepickerNext);
if (document.getElementById('creareFactura') != null) {
    creareFactura.mount('#creareFactura');
}


// Trimitere email cu codul de autentificare
const trimitereCodAutentificarePrinEmail = createApp({
    el: '#trimitereCodAutentificarePrinEmail',
    data() {
        return {
            email: email,
            mesajDeAfisat: '',
        }
    },
    methods: {
        trimiteEmail() {
            // console.log('da');
            if (this.email) {
                axios.get('/axios/trimitere-cod-autentificare-prin-email', {
                    params: { email: this.email }
                })
                    .then(
                        response => {
                            this.mesajDeAfisat = response.data.raspuns;
                        }
                    );
            } else {
                this.mesajDeAfisat = "<span class='text-danger' style='font-size:80%'>Introdu emailul.</span>";
            }
        },
    }
});
if (document.getElementById('trimitereCodAutentificarePrinEmail') != null) {
    trimitereCodAutentificarePrinEmail.mount('#trimitereCodAutentificarePrinEmail');
}

// Disable button to prevent multiple submissions
const disableButton1 = createApp({
    el: '#app1',
    data() {
        return {
            disableButton: false,
        }
    },
});
if (document.getElementById('disableButton1') != null) {
    disableButton1.mount('#disableButton1');
}

// Disable button to prevent multiple submissions
const disableButton2 = createApp({
    el: '#app1',
    data() {
        return {
            disableButton: false,
        }
    },
});
if (document.getElementById('disableButton2') != null) {
    disableButton2.mount('#disableButton2');
}

// Disable button to prevent multiple submissions
const disableButton3 = createApp({
    el: '#app1',
    data() {
        return {
            disableButton: false,
        }
    },
});
if (document.getElementById('disableButton3') != null) {
    disableButton3.mount('#disableButton3');
}

// Disable button to prevent multiple submissions
const disableButton4 = createApp({
    el: '#app1',
    data() {
        return {
            disableButton: false,
        }
    },
});
if (document.getElementById('disableButton4') != null) {
    disableButton4.mount('#disableButton4');
}

// WYSIWYG editors testing and implementation
const wysiwyg = createApp({
    el: '#app1',
    data() {
        return {
            disableButton: false,
        }
    },
});
wysiwyg.component('TiptapEditor', TiptapEditor);
if (document.getElementById('wysiwyg') != null) {
    wysiwyg.mount('#wysiwyg');
}


// Added on 14.01.2025 - to set more clients to a command, not just one
// Tablou comenzi - factura
const facturaMemento = createApp({
    el: '#facturaMemento',
    data() {
        return {
            facturi: facturi,
        }
    }
});

facturaMemento.component('vue-datepicker-next', VueDatepickerNext);
if (document.getElementById('facturaMemento') != null) {
    facturaMemento.mount('#facturaMemento');
}

const tabelIntermedieri = createApp({});
tabelIntermedieri.component('toggle-predat', TogglePredat);
if (document.getElementById('tabelIntermedieri') != null) {
    tabelIntermedieri.mount('#tabelIntermedieri');
}

const keyPerformanceIndicatorsMainPage = createApp({
    data() {
        return {
            lastUpdatedKpi: null, // Track the last updated KPI ID
        };
    },
    methods: {
        handleUpdateSuccess(kpiId) {
            this.lastUpdatedKpi = kpiId; // Update the last updated KPI ID
            setTimeout(() => {
                this.lastUpdatedKpi = null; // Remove after 5 seconds
            }, 5000);
        },
    },
});
keyPerformanceIndicatorsMainPage.component('inline-observatii-editor', InlineObservatiiEditor);
if (document.getElementById('keyPerformanceIndicatorsMainPage') != null) {
    keyPerformanceIndicatorsMainPage.mount('#keyPerformanceIndicatorsMainPage');
}

const directoryTree = createApp({});
directoryTree.component('directory-tree', DirectoryTree);
if (document.getElementById('directoryTree') != null) {
    directoryTree.mount('#directoryTree');
}

const excel = createApp({});
excel.component('jspreadsheet-component', JspreadsheetComponent);
if (document.getElementById('excel') != null) {
    excel.mount('#excel');
}
