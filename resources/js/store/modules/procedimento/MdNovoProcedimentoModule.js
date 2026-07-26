export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        procedimento: {
            PROCEDIMENTO_ID: null,
            PROCEDIMENTO_CODIGO: null,
            PROCEDIMENTO_DESCRICAO: null,
            PROCEDIMENTO_ATIVO: null,
        }
    },
    getters: {
        getProcedimento(state) {
            return state.procedimento
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setProcedimento(state, procedimento = null) {
            if (procedimento) {
                state.procedimento = JSON.parse(JSON.stringify(procedimento))
            } else {
                state.procedimento = {
                    PROCEDIMENTO_ID: null,
                    PROCEDIMENTO_CODIGO: null,
                    PROCEDIMENTO_DESCRICAO: null,
                    PROCEDIMENTO_ATIVO: null,
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
        setProcedimento({ commit }, procedimento) {
            commit('setProcedimento', procedimento)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
