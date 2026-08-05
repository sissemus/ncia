export default {
    namespaced: true,

    state: {
        showModal: false,
        fullScreen: false,
        unidade: {
            UNIDADE_ID: null,
            UNIDADE_NOME: null,
            UNIDADE_SOLICITANTE: 0,
            UNIDADE_ATIVO: 1
        }
    },

    getters: {
        getUnidade(state) {
            return state.unidade;
        },

        getShowModal(state) {
            return state.showModal;
        },

        getFullScreen(state) {
            return state.fullScreen;
        }
    },

    mutations: {
        setUnidade(state, unidade = null) {
            if (unidade) {
                state.unidade = JSON.parse(JSON.stringify(unidade));
            } else {
                state.unidade = {
                    UNIDADE_ID: null,
                    UNIDADE_NOME: null,
                    UNIDADE_SOLICITANTE: 0,
                    UNIDADE_ATIVO: 1
                };
            }
        },

        setShowModal(state, showModal) {
            state.showModal = showModal;
        },

        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen;
        }
    },

    actions: {
        setUnidade({ commit }, unidade) {
            commit('setUnidade', unidade);
        },

        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal);
        },

        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen);
        }
    }
}