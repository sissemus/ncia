export default {
    namespaced: true,

    state: {
        showModal: false,
        fullScreen: false,
        paciente: {
            PACIENTE_ID: null,
            PACIENTE_CODIGO_TEMPORARIO: null,
            PACIENTE_NOME: null,
            PACIENTE_CPF: null,
            PACIENTE_DT_NASCIMENTO: null,
            TG_SEXO_ID: null,
            PACIENTE_VULNERABILIDADE_SOCIAL: 0,
            USUARIO_ID: null,
            PACIENTE_DT_CAD: null,
            PACIENTE_DT_IDENTIFICACAO: null
        }
    },

    getters: {
        getPaciente(state) {
            return state.paciente;
        },

        getShowModal(state) {
            return state.showModal;
        },

        getFullScreen(state) {
            return state.fullScreen;
        }
    },

    mutations: {
        setPaciente(state, paciente = null) {
            if (paciente) {
                state.paciente = JSON.parse(JSON.stringify(paciente));

                if (state.paciente.PACIENTE_DT_NASCIMENTO)
                    state.paciente.PACIENTE_DT_NASCIMENTO = state.paciente.PACIENTE_DT_NASCIMENTO.substring(0, 10);

                if (state.paciente.PACIENTE_VULNERABILIDADE_SOCIAL === null)
                    state.paciente.PACIENTE_VULNERABILIDADE_SOCIAL = 0;
            } else {
                state.paciente = {
                    PACIENTE_ID: null,
                    PACIENTE_CODIGO_TEMPORARIO: null,
                    PACIENTE_NOME: null,
                    PACIENTE_CPF: null,
                    PACIENTE_DT_NASCIMENTO: null,
                    TG_SEXO_ID: null,
                    PACIENTE_VULNERABILIDADE_SOCIAL: 0,
                    USUARIO_ID: null,
                    PACIENTE_DT_CAD: null,
                    PACIENTE_DT_IDENTIFICACAO: null
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
        setPaciente({ commit }, paciente) {
            commit("setPaciente", paciente);
        },

        setShowModal({ commit }, showModal) {
            commit("setShowModal", showModal);
        },

        setFullScreen({ commit }, fullScreen) {
            commit("setFullScreen", fullScreen);
        }
    }
}