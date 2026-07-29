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
import UUID from "vue-uuid";

Vue.use(VueTheMask)
Vue.use(UUID);
// window.Vue = require('vue').default;

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
Vue.component('home', require('./components/home/Home').default);
Vue.component('tabela-generica-view', require('./components/tabela_generica/TabelaGenericaView').default);
Vue.component('usuario-view', require('./components/usuario/UsuarioView').default);
Vue.component('usuario-alterar-senha-view', require('./components/usuario/UsuarioAlterarSenhaView').default);
Vue.component('aplicacao-view', require('./components/aplicacao/AplicacaoView').default);
Vue.component('perfil-view', require('./components/perfil/PerfilView').default);
Vue.component('procedimento-view', require('./components/procedimento/ProcedimentoView.vue').default);
Vue.component('diagnostico-view', require('./components/diagnostico/DiagnosticoView.vue').default);
Vue.component('unidade-view', require('./components/unidade/UnidadeView.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const vm = new Vue({
    el: '#app',
    vuetify: Vuetify,
    uuid: UUID,
    store
});

export default vm;
