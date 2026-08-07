export default {
    namespaced: true,

    state: {
        showModal: false,
        fullScreen: false,
        vinculo: {
            VEICULO_UNIDADE_ID: null,
            VEICULO_ID: null,
            UNIDADE_ID: null,
        }
    },

    getters: {
        getVinculo(state) {
            return state.vinculo;
        },

        getShowModal(state) {
            return state.showModal;
        },

        getFullScreen(state) {
            return state.fullScreen;
        }
    },

    mutations: {
        setVinculo(state, vinculo = null) {
            if (vinculo) {
                state.vinculo = JSON.parse(JSON.stringify(vinculo));
            } else {
                state.vinculo = {
                    VEICULO_UNIDADE_ID: null,
                    VEICULO_ID: null,
                    UNIDADE_ID: null,
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
        setVinculo({ commit }, vinculo) {
            commit('setVinculo', vinculo);
        },

        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal);
        },

        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen);
        }
    }
}
