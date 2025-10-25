export default {
    namespaced: true,
    state: {
        aplicacoes: [],
    },
    getters: {
        getAplicacoes(state) {
            return state.aplicacoes
        },
    },
    mutations: {
        setAplicacoes(state, aplicacoes) {
            state.aplicacoes = JSON.parse(JSON.stringify(aplicacoes))
        },
    },
    actions: {
        setAplicacoes({commit}, aplicacoes) {
            commit('setAplicacoes', aplicacoes)
        },
    }
}
