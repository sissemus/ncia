export default {
    namespaced: true,
    state: {
        aplicacoes: [],
        hierarquias: [],
        sexos: [],
        tipoProfissionais: [],
        tipoVeiculos: [],
        prioridades: [],
        tiposChamado: [],
        tiposPrecaucao: [],
        suportesO2: [],
        suportesHemodinamicos: [],
    },
    getters: {
        getAplicacoes(state) {
            return state.aplicacoes
        },
        getHierarquias(state) {
            return state.hierarquias
        },
        getSexos(state) {
            return state.sexos
        },
        getTipoProfissionais(state) {
            return state.tipoProfissionais
        },
        getTipoVeiculos(state) {
            return state.tipoVeiculos
        },
        getPrioridades: state => state.prioridades,
        getTiposChamado: state => state.tiposChamado,
        getTiposPrecaucao: state => state.tiposPrecaucao,
        getSuportesO2: state => state.suportesO2,
        getSuportesHemodinamicos: state => state.suportesHemodinamicos,
    },
    mutations: {
        setAplicacoes(state, aplicacoes) {
            state.aplicacoes = JSON.parse(JSON.stringify(aplicacoes))
        },
        setHierarquias(state, hierarquias) {
            state.hierarquias = JSON.parse(JSON.stringify(hierarquias))
        },
        setSexos(state, sexos) {
            state.sexos = JSON.parse(JSON.stringify(sexos))
        },
        setTipoProfissionais(state, tipoProfissionais) {
            state.tipoProfissionais = JSON.parse(JSON.stringify(tipoProfissionais))
        },
        setTipoVeiculos(state, tipoVeiculos) {
            state.tipoVeiculos = JSON.parse(JSON.stringify(tipoVeiculos))
        },
        setAtualizacaoClinicaDominios(state, dominios = {}) {
            state.prioridades = JSON.parse(JSON.stringify(dominios.prioridades || []))
            state.tiposChamado = JSON.parse(JSON.stringify(dominios.tiposChamado || []))
            state.tiposPrecaucao = JSON.parse(JSON.stringify(dominios.tiposPrecaucao || []))
            state.suportesO2 = JSON.parse(JSON.stringify(dominios.suportesO2 || []))
            state.suportesHemodinamicos = JSON.parse(JSON.stringify(dominios.suportesHemodinamicos || []))
            state.sexos = JSON.parse(JSON.stringify(dominios.sexos || []))
        },
    },
    actions: {
        setAplicacoes({commit}, aplicacoes) {
            commit('setAplicacoes', aplicacoes)
        },
        setHierarquias({commit}, hierarquias) {
            commit('setHierarquias', hierarquias)
        },
        setSexos({commit}, sexos) {
            commit('setSexos', sexos)
        },
        setTipoProfissionais({commit}, tipoProfissionais) {
            commit('setTipoProfissionais', tipoProfissionais)
        },
        setTipoVeiculos({commit}, tipoVeiculos) {
            commit('setTipoVeiculos', tipoVeiculos)
        },
        setAtualizacaoClinicaDominios({commit}, dominios) {
            commit('setAtualizacaoClinicaDominios', dominios)
        },
    }
}
