export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        equipe: {
            EQUIPE_PROFISSIONAL_ID: null,
            EQUIPE_ID: null,
            PROFISSIONAL_ID: null,
            EQUIPE_PROFISSIONAL_ATIVO: null
        }
    },
    getters: {
        getEquipeProfissional(state) {
            return state.equipeProfissional
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setEquipeProfissional(state, equipeProfissional = null) {
            if (equipeProfissional) {
                state.equipeProfissional = JSON.parse(JSON.stringify(equipeProfissional))
            } else {
                state.equipeProfissional = {
                    EQUIPE_PROFISSIONAL_ID: null,
                    EQUIPE_ID: null,
                    PROFISSIONAL_ID: null,
                    EQUIPE_PROFISSIONAL_ATIVO: null
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
        setEquipeProfissional({ commit }, equipeProfissional) {
            commit('setEquipeProfissional', equipeProfissional)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
