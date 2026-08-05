export default {
    namespaced: true,
    state: {
        aplicacoes: [],
        hierarquias: [],
        sexos: [],
        tipoProfissionais: [],
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
    }
}
