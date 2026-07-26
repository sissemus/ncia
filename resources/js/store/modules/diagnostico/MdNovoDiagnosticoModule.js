export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        diagnostico: {
            DIAGNOSTICO_ID: null,
            DIAGNOSTICO_DESCRICAO: null,
            DIAGNOSTICO_ATIVO: null,
        }
    },
    getters: {
        getDiagnostico(state) {
            return state.diagnostico
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setDiagnostico(state, diagnostico = null) {
            if (diagnostico) {
                state.diagnostico = JSON.parse(JSON.stringify(diagnostico))
            } else {
                state.diagnostico = {
                    DIAGNOSTICO_ID: null,
                    DIAGNOSTICO_DESCRICAO: null,
                    DIAGNOSTICO_ATIVO: null,
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
        setDiagnostico({ commit }, diagnostico) {
            commit('setDiagnostico', diagnostico)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
