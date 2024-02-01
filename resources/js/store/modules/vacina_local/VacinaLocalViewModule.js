import VacinaLocal from "../payloads/VacinaLocal.json";

export default {
    namespaced: true,
    state: {
        vacinasLocais: [],
        vacinaLocal: JSON.parse(JSON.stringify(VacinaLocal)),
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        }
    },
    getters: {
        getPagination(state) {
            return state.pagination
        },
        getVacinaLocal(state) {
            return state.vacinaLocal
        },
        getVacinasLocais(state) {
            return state.vacinasLocais
        },
    },
    mutations: {
        setPagination(state, pagination = null) {
            if (pagination) {
                state.pagination = {
                    current_page: pagination.current_page,
                    total: pagination.total,
                    last_page: pagination.last_page
                }
            } else {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                }
            }
        },
        setVacinaLocal(state, vacinaLocal = null) {
            if (vacinaLocal) {
                state.vacinaLocal = JSON.parse(JSON.stringify(vacinaLocal))
            } else {
                state.vacinaLocal = JSON.parse(JSON.stringify(VacinaLocal))
            }
        },
        setVacinasLocais(state, vacinasLocais = []) {
            state.vacinasLocais = JSON.parse(JSON.stringify(vacinasLocais))
        },
    },
    actions: {
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setVacinaLocal({commit}, vacinaLocal) {
            commit('setVacinaLocal', vacinaLocal)
        },
        setVacinasLocais({commit}, vacinasLocais) {
            commit('setVacinasLocais', vacinasLocais)
        },
    }
}
