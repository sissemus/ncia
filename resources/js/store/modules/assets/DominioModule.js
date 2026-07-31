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
    },
    mutations: {
        setAplicacoes(state, aplicacoes) {
            state.aplicacoes = JSON.parse(JSON.stringify(aplicacoes))
        },
        setHierarquias(state, hierarquias) {
            state.hierarquias = JSON.parse(JSON.stringify(hierarquias))
        },
    },
    actions: {
        setAplicacoes({commit}, aplicacoes) {
            commit('setAplicacoes', aplicacoes)
        },
        setHierarquias({commit}, hierarquias) {
            commit('setHierarquias', hierarquias)
        },
    }
}
