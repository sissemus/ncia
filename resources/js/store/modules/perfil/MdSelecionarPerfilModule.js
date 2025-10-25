export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        quemChamou: '',
        perfis: [],
        modulo: null,
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        }
    },
    getters: {
        getModulo(state) {
            return state.modulo
        },
        getPagination(state) {
            return state.pagination
        },
        getModulo(state) {
            return state.modulo
        },
        getPerfis(state) {
            return state.perfis
        },
        getQuemChamou(state) {
            return state.quemChamou
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setModulo(state, modulo) {
            state.modulo = modulo
        },
        setPagination(state, pagination) {
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
        setPerfis(state, perfis) {
            state.perfis = JSON.parse(JSON.stringify(perfis))
        },
        setQuemChamou(state, quemChamou) {
            state.quemChamou = quemChamou
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        }
    },
    actions: {
        setModulo({commit}, modulo) {
            commit('setModulo', modulo)
        },
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setPerfis({commit}, perfis) {
            commit('setPerfis', perfis)
        },
        setQuemChamou({commit}, quemChamou) {
            commit('setQuemChamou', quemChamou)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        }
    }
}
