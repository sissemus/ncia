export default {
    namespaced: true,
    state: {
        aplicacoes: [],
        hierarquias: [],
        sexos: [],
        tipoProfissionais: [],
        tipoVeiculos: [],
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
    }
}
