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
            VEICULO_ATIVO: 1,
            UNIDADE_ID: null,
            VEICULO_UNIDADE_DT_INI: null
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
            const today = new Date();
            const localDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

            if (veiculo) {
                state.veiculo = JSON.parse(JSON.stringify(veiculo));
                
                const vinculo = veiculo.vinculoAtivo || veiculo.vinculo_ativo;
                if (vinculo) {
                    state.veiculo.UNIDADE_ID = vinculo.UNIDADE_ID ? Number(vinculo.UNIDADE_ID) : null;
                    if (vinculo.VEICULO_UNIDADE_DT_INI) {
                        state.veiculo.VEICULO_UNIDADE_DT_INI = String(vinculo.VEICULO_UNIDADE_DT_INI).substring(0, 10);
                    } else {
                        state.veiculo.VEICULO_UNIDADE_DT_INI = localDate;
                    }
                } else {
                    state.veiculo.UNIDADE_ID = null;
                    state.veiculo.VEICULO_UNIDADE_DT_INI = localDate;
                }
            } else {
                state.veiculo = {
                    VEICULO_ID: null,
                    VEICULO_IDENTIFICACAO: null,
                    VEICULO_PLACA: null,
                    TG_TIPO_VEICULO_ID: null,
                    TG_SITUACAO_VEICULO_ID: null,
                    VEICULO_ATIVO: 1,
                    UNIDADE_ID: null,
                    VEICULO_UNIDADE_DT_INI: localDate
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
