export default {
    namespaced: true,
    state: {
        usuariosPesquisa: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        valorPesquisa: null,
    },
    getters: {
        getPagination(state) {
            return state.pagination
        },
        getValorPesquisa(state) {
            return state.valorPesquisa
        },
        getUsuariosPesquisa(state) {
            return state.usuariosPesquisa
        },
    },
    mutations: {
        setPagination(state, pagination = null) {
            if (pagination === null) {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                }
            } else {
                state.pagination = pagination
            }
        },
        setValorPesquisa(state, valorPesquisa) {
            state.valorPesquisa = valorPesquisa
        },
        setUsuariosPesquisa(state, usuariosPesquisa) {
            state.usuariosPesquisa = JSON.parse(JSON.stringify(usuariosPesquisa))
        },
    },
    actions: {
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setValorPesquisa({commit}, valorPesquisa) {
            commit('setValorPesquisa', valorPesquisa)
        },
        setUsuariosPesquisa({commit}, usuariosPesquisa) {
            commit('setUsuariosPesquisa', usuariosPesquisa)
        },
    }
}
