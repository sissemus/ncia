export default {
    namespaced: true,

    state: {
        showModal: false,
        fullScreen: false,
        veiculo: {
            VEICULO_ID: null,
            VEICULO_IDENTIFICACAO: null,
            VEICULO_PLACA: null,
            TG_TIPO_VEICULO_ID: null,
            TG_SITUACAO_VEICULO_ID: null,
            VEICULO_ATIVO: 1
        }
    },

    getters: {
        getVeiculo(state) {
            return state.veiculo;
        },

        getShowModal(state) {
            return state.showModal;
        },

        getFullScreen(state) {
            return state.fullScreen;
        }
    },

    mutations: {
        setVeiculo(state, veiculo = null) {
            if (veiculo) {
                state.veiculo = JSON.parse(JSON.stringify(veiculo));
            } else {
                state.veiculo = {
                    VEICULO_ID: null,
                    VEICULO_IDENTIFICACAO: null,
                    VEICULO_PLACA: null,
                    TG_TIPO_VEICULO_ID: null,
                    TG_SITUACAO_VEICULO_ID: null,
                    VEICULO_ATIVO: 1
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
        setVeiculo({ commit }, veiculo) {
            commit('setVeiculo', veiculo);
        },

        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal);
        },

        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen);
        }
    }
}
