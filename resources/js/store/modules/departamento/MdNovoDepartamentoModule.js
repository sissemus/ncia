export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        departamento: {
            DEPARTAMENTO_ID: null,
            HIERARQUIA_ID: null,
            DEPARTAMENTO_NOME: null,
            DEPARTAMENTO_SIGLA: null,
            DEPARTAMENTO_DESCRICAO: null,
            DEPARTAMENTO_ATIVO: null,
        }
    },
    getters: {
        getDepartamento(state) {
            return state.departamento
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setDepartamento(state, departamento = null) {
            if (departamento) {
                state.departamento = JSON.parse(JSON.stringify(departamento))
            } else {
                state.departamento = {
                    DEPARTAMENTO_ID: null,
                    HIERARQUIA_ID: null,
                    DEPARTAMENTO_NOME: null,
                    DEPARTAMENTO_SIGLA: null,
                    DEPARTAMENTO_DESCRICAO: null,
                    DEPARTAMENTO_ATIVO: null,
                }
            }
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        },
    },
    actions: {
        setDepartamento({ commit }, departamento) {
            commit('setDepartamento', departamento)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
