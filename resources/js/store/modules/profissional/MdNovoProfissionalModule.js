export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        profissional: {
            PROFISSIONAL_ID: null,
            PROFISSIONAL_NOME: null,
            PROFISSIONAL_CPF: null,
            PROFISSIONAL_NASCIMENTO: null,
            TG_SEXO_ID: null,
            TG_TIPO_PROFISSIONAL_ID: null,
            PROFISSIONAL_ATIVO: 1
        }
    },
    getters: {
        getProfissional(state) {
            return state.profissional
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        }
    },
    mutations: {
        setProfissional(state, profissional = null) {
            if (profissional) {
                state.profissional = JSON.parse(JSON.stringify(profissional))

                if (state.profissional.PROFISSIONAL_NASCIMENTO)
                    state.profissional.PROFISSIONAL_NASCIMENTO =
                        state.profissional.PROFISSIONAL_NASCIMENTO.substring(0, 10)
            } else {
                state.profissional = {
                    PROFISSIONAL_ID: null,
                    PROFISSIONAL_NOME: null,
                    PROFISSIONAL_CPF: null,
                    PROFISSIONAL_NASCIMENTO: null,
                    TG_SEXO_ID: null,
                    TG_TIPO_PROFISSIONAL_ID: null,
                    PROFISSIONAL_ATIVO: 1
                }
            }
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        }
    },
    actions: {
        setProfissional({ commit }, profissional) {
            commit('setProfissional', profissional)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        }
    }
}