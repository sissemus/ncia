const estadoInicial = () => ({
    contexto: null,
    chamado: null,
    atualizacoes: [],
    valoresAtuais: {},
    prioridadeAnteriorId: null,
    podeCriar: false,
    carregando: false,
    processando: false,
    dialog: false,
    fullScreen: false,
    form: {}
});

export default {
    namespaced: true,

    state: estadoInicial(),

    getters: {
        getContexto: state => state.contexto,
        getChamado: state => state.chamado,
        getAtualizacoes: state => state.atualizacoes,
        getValoresAtuais: state => state.valoresAtuais,
        getPrioridadeAnteriorId: state => state.prioridadeAnteriorId,
        getPodeCriar: state => state.podeCriar,
        getCarregando: state => state.carregando,
        getProcessando: state => state.processando,
        getDialog: state => state.dialog,
        getFullScreen: state => state.fullScreen,
        getForm: state => state.form
    },

    mutations: {
        setChamado(state, { chamado = null, contexto = null } = {}) {
            const chamadoAnteriorId = state.chamado && state.chamado.CHAMADO_ID;
            const novoChamadoId = chamado && chamado.CHAMADO_ID;
            const mesmoChamado = chamadoAnteriorId !== null
                && chamadoAnteriorId !== undefined
                && novoChamadoId !== null
                && novoChamadoId !== undefined
                && Number(chamadoAnteriorId) === Number(novoChamadoId);
            const chamadoClonado = chamado ? JSON.parse(JSON.stringify(chamado)) : null;

            if (mesmoChamado && chamadoClonado
                && !chamadoClonado.atualizacoesClinicas
                && !chamadoClonado.atualizacoes_clinicas) {
                chamadoClonado.atualizacoesClinicas = JSON.parse(JSON.stringify(state.atualizacoes));
            }

            state.contexto = contexto;
            state.chamado = chamadoClonado;

            if (!mesmoChamado) {
                state.atualizacoes = [];
                state.valoresAtuais = {};
                state.prioridadeAnteriorId = null;
                state.podeCriar = false;
                state.carregando = false;
                state.processando = false;
                state.dialog = false;
                state.fullScreen = false;
                state.form = {};
            }
        },
        setDadosClinicos(state, dados = {}) {
            state.atualizacoes = JSON.parse(JSON.stringify(dados.atualizacoes || []));
            state.valoresAtuais = JSON.parse(JSON.stringify(dados.valoresAtuais || {}));
            state.podeCriar = !!dados.podeCriar;

            if (state.chamado) {
                state.chamado = {
                    ...state.chamado,
                    atualizacoesClinicas: JSON.parse(JSON.stringify(state.atualizacoes))
                };
            }
        },
        aplicarSalvamento(state, dados = {}) {
            state.atualizacoes = JSON.parse(JSON.stringify(dados.atualizacoes || []));

            if (state.chamado) {
                state.chamado = {
                    ...state.chamado,
                    TG_PRIORIDADE_ID: dados.prioridadeAtual,
                    atualizacoesClinicas: JSON.parse(JSON.stringify(state.atualizacoes))
                };
            }
        },
        setPrioridadeAnteriorId(state, prioridadeAnteriorId) {
            state.prioridadeAnteriorId = prioridadeAnteriorId;
        },
        setCarregando(state, carregando) {
            state.carregando = carregando;
        },
        setProcessando(state, processando) {
            state.processando = processando;
        },
        setDialog(state, dialog) {
            state.dialog = dialog;
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen;
        },
        setForm(state, form) {
            state.form = JSON.parse(JSON.stringify(form || {}));
        },
        clear(state) {
            Object.assign(state, estadoInicial());
        }
    },

    actions: {
        setChamado({ commit }, payload) {
            commit("setChamado", payload);
        },
        carregar({ state, commit, dispatch, rootGetters }, msgId = "msgAtualizacaoClinica") {
            const chamadoId = state.chamado && state.chamado.CHAMADO_ID;

            if (!chamadoId) {
                return Promise.resolve(null);
            }

            commit("setCarregando", true);
            const baseUrl = rootGetters.getBaseUrl;

            return axios.get(`${baseUrl}/atualizacao_clinica/chamado/${chamadoId}`)
                .then(response => {
                    if (state.chamado && Number(state.chamado.CHAMADO_ID) === Number(chamadoId)) {
                        commit("setDadosClinicos", response.data);
                    }
                    return response.data;
                })
                .catch(error => {
                    dispatch("TratarErroAjaxModule/tratarErro", {
                        id: msgId,
                        response: error && error.response
                    }, { root: true });
                    return null;
                })
                .finally(() => {
                    if (state.chamado && Number(state.chamado.CHAMADO_ID) === Number(chamadoId)) {
                        commit("setCarregando", false);
                    }
                });
        },
        abrirNova({ state, commit }) {
            if (!state.chamado) return;

            const valoresAtuais = JSON.parse(JSON.stringify(state.valoresAtuais || {}));
            const prioridadeAnteriorId = valoresAtuais.TG_PRIORIDADE_ID !== undefined
                ? valoresAtuais.TG_PRIORIDADE_ID
                : state.chamado.TG_PRIORIDADE_ID;

            commit("setPrioridadeAnteriorId", prioridadeAnteriorId);
            commit("setForm", {
                ...valoresAtuais,
                CHAMADO_ID: state.chamado.CHAMADO_ID,
                TG_PRIORIDADE_ID: null,
                ATUALIZACAO_CLINICA_JUSTIFICATIVA_MEDICA: ""
            });
            commit("setFullScreen", false);
            commit("setDialog", true);
        },
        fechar({ state, commit }) {
            if (state.processando) return;
            commit("setDialog", false);
            commit("setFullScreen", false);
        },
        setDialog({ commit }, dialog) {
            commit("setDialog", dialog);
        },
        setFullScreen({ commit }, fullScreen) {
            commit("setFullScreen", fullScreen);
        },
        salvar({ state, commit, dispatch, rootGetters }, msgId = "msgAtualizacaoClinicaDialog") {
            if (state.processando || !state.chamado) {
                return Promise.resolve(null);
            }

            commit("setProcessando", true);
            const baseUrl = rootGetters.getBaseUrl;

            return axios.post(`${baseUrl}/atualizacao_clinica`, state.form)
                .then(response => {
                    commit("aplicarSalvamento", response.data);
                    commit("setDialog", false);
                    commit("setFullScreen", false);

                    return dispatch("carregar").then(() => {
                        if (state.contexto === "acompanhamento") {
                            return dispatch(
                                "ChamadoAcompanhamentoViewModule/search",
                                "msgChamadoAcompanhamentoView",
                                { root: true }
                            ).then(() => response.data);
                        }
                        return response.data;
                    });
                })
                .catch(error => {
                    dispatch("TratarErroAjaxModule/tratarErro", {
                        id: msgId,
                        response: error && error.response
                    }, { root: true });
                    return null;
                })
                .finally(() => commit("setProcessando", false));
        },
        clear({ commit }) {
            commit("clear");
        }
    }
};
