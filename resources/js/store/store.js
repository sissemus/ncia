import Vue from 'vue'
import Vuex from 'vuex'
import BlockUIModule from "./modules/assets/BlockUIModule"
import TratarErroAjaxModule from "./modules/assets/TratarErroAjaxModule";
import VariaveisModule from "./modules/assets/VariaveisModule";
import MdNovoUsuarioModule from "./modules/usuario/MdNovoUsuarioModule";
import DominioModule from "./modules/assets/DominioModule";
import PerfectScrollbar from 'vue2-perfect-scrollbar'
import 'vue2-perfect-scrollbar/dist/vue2-perfect-scrollbar.css'
import MdOrientacoesModule from "./modules/MdOrientacoesModule"
import UsuarioViewModule from "./modules/usuario/UsuarioViewModule";
import MdGraficoSituacaoFilaModule from "./modules/grafico/MdGraficoSituacaoFilaModule";
import DoseViewModule from "./modules/dose/DoseViewModule";
import VacinaViewModule from "./modules/vacina/VacinaViewModule";
import VacinaLocalViewModule from "./modules/vacina_local/VacinaLocalViewModule";
import VacinacaoViewModule from "./modules/vacinacao/VacinacaoViewModule";

Vue.use(PerfectScrollbar)
Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        overlay: false
    },
    getters: {
        getOverlay(state) {
            return state.overlay;
        }
    },
    mutations: {
        setOverlay(state, value) {
            state.overlay = value;
        }
    },
    actions: {
        setOverlay({commit}, value) {
            commit('setOverlay', value);
        }
    },
    modules: {
        BlockUIModule,
        TratarErroAjaxModule,
        VariaveisModule,
        MdNovoUsuarioModule,
        DominioModule,
        MdOrientacoesModule,
        UsuarioViewModule,
        MdGraficoSituacaoFilaModule,
        DoseViewModule,
        VacinaViewModule,
        VacinaLocalViewModule,
        VacinacaoViewModule,
    }
});
