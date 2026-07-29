import Vue from 'vue'
import Vuex from 'vuex'
import AuthModule from "./modules/assets/AuthModule"
import BlockUIModule from "./modules/assets/BlockUIModule"
import TratarErroAjaxModule from "./modules/assets/TratarErroAjaxModule";
import VariaveisModule from "./modules/assets/VariaveisModule";
import MdNovoUsuarioModule from "./modules/usuario/MdNovoUsuarioModule";
import DominioModule from "./modules/assets/DominioModule";
import TabelaGenericaViewModule from "./modules/tabela_generica/TabelaGenericaViewModule";
import MdNovaColunaModule from "./modules/tabela_generica/MdNovaColunaModule";
import MdNovaTabelaModule from "./modules/tabela_generica/MdNovaTabelaModule";
import PerfectScrollbar from 'vue2-perfect-scrollbar'
import 'vue2-perfect-scrollbar/dist/vue2-perfect-scrollbar.css'
import UsuarioViewModule from "./modules/usuario/UsuarioViewModule";
import TabUsuarioPerfilModule from "./modules/usuario/TabUsuarioPerfilModule";
import MdNovoPerfilModule from "./modules/perfil/MdNovoPerfilModule";
import AplicacaoViewModule from './modules/aplicacao/AplicacaoViewModule';
import MdNovaAplicacaoModule from './modules/aplicacao/MdNovaAplicacaoModule';
import PerfilViewModule from "./modules/perfil/PerfilViewModule";
import MdSelecionarPerfilModule from "./modules/perfil/MdSelecionarPerfilModule";
import ProcedimentoViewModule from "./modules/procedimento/ProcedimentoViewModule";
import MdNovoProcedimentoModule from "./modules/procedimento/MdNovoProcedimentoModule";
import DiagnosticoViewModule from "./modules/diagnostico/DiagnosticoViewModule";
import MdNovoDiagnosticoModule from "./modules/diagnostico/MdNovoDiagnosticoModule";
import UnidadeViewModule from "./modules/unidade/UnidadeViewModule";
import MdNovoUnidadeModule from "./modules/unidade/MdNovoUnidadeModule";

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
        setOverlay({ commit }, value) {
            commit('setOverlay', value);
        }
    },
    modules: {
        AuthModule,
        BlockUIModule,
        TratarErroAjaxModule,
        VariaveisModule,
        MdNovoUsuarioModule,
        DominioModule,
        TabelaGenericaViewModule,
        MdNovaColunaModule,
        MdNovaTabelaModule,
        UsuarioViewModule,
        AplicacaoViewModule,
        MdNovaAplicacaoModule,
        MdNovoPerfilModule,
        PerfilViewModule,
        MdSelecionarPerfilModule,
        TabUsuarioPerfilModule,
        ProcedimentoViewModule,
        MdNovoProcedimentoModule,
        DiagnosticoViewModule,
        MdNovoDiagnosticoModule,
        UnidadeViewModule,
        MdNovoUnidadeModule,
    }
});
