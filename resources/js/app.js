/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import Vue from "vue";
require('./bootstrap');
import store from './store/store';
import Vuetify from "../plugins/vuetify";
import 'vuetify/dist/vuetify.min.css'
import "../plugins/vuetify-money.js";
import VueTheMask from 'vue-the-mask';
import VueApexCharts from 'vue-apexcharts'

Vue.use(VueApexCharts)
Vue.use(VueTheMask)

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('login', require('./components/auth/Login').default);
Vue.component('app', require('./components/App').default);
Vue.component('home', require('./components/Home').default);
Vue.component('public', require('./components/Public').default);
Vue.component('local-view', require('./components/local/LocalView').default);
Vue.component('usuario-view', require('./components/usuario/UsuarioView').default);
Vue.component('dose-view', require('./components/dose/DoseView').default);
Vue.component('vacina-view', require('./components/vacina/VacinaView').default);
Vue.component('vacina-local-view', require('./components/vacina_local/VacinaLocalView').default);
Vue.component('vacinacao-view', require('./components/vacinacao/VacinacaoView').default);

Vue.component('LineChart', require('./components/grafico/LineChart').default);
Vue.component('apexchart', VueApexCharts);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const vm = new Vue({
    el: '#app',
    vuetify: Vuetify,
    store
});

export default vm;
