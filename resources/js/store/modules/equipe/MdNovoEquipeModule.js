export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        equipe: {
            EQUIPE_ID: null,
            VEICULO_ID: null,
            PROFISSIONAL_ID: null,
            EQUIPE_ATIVO: null,
            EQUIPE_DATA_INI: null,
            EQUIPE_DATA_FIM: null,

        }
    },
    getters: {
        getEquipe(state) {
            return state.equipe
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setEquipe(state, equipe = null) {
            if (equipe) {
                state.equipe = JSON.parse(JSON.stringify(equipe))
            } else {
                state.equipe = {
                    EQUIPE_ID: null,
                    VEICULO_ID: null,
                    PROFISSIONAL_ID: null,
                    EQUIPE_ATIVO: null,
                    EQUIPE_DATA_INI: null,
                    EQUIPE_DATA_FIM: null,
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
        setEquipe({ commit }, equipe) {
            commit('setEquipe', equipe)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
