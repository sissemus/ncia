export default {
    namespaced: true,

    state: {
        chamados: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        chamadoPesquisa: {
            PACIENTE_NOME: null,
            CHAMADO_ID: null,
            TG_SITUACAO_ID: null,
            CHAMADO_DATA: null,
            TG_PRIORIDADE_ID: null
        }
    },

    getters: {
        getChamados(state) {
            return state.chamados;
        },
        getPagination(state) {
            return state.pagination;
        },
        getChamadoPesquisa(state) {
            return state.chamadoPesquisa;
        }
    },

    mutations: {
        setChamados(state, chamados) {
            state.chamados = JSON.parse(JSON.stringify(chamados));
        },
        setPagination(state, pagination = null) {
            if (pagination) {
                state.pagination = {
                    current_page: pagination.current_page,
                    total: pagination.total,
                    last_page: pagination.last_page
                };
            } else {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                };
            }
        },
        setChamadoPesquisa(state, chamadoPesquisa = null) {
            if (chamadoPesquisa) {
                state.chamadoPesquisa = JSON.parse(JSON.stringify(chamadoPesquisa));
            } else {
                state.chamadoPesquisa = {
                    PACIENTE_NOME: null,
                    CHAMADO_ID: null,
                    TG_SITUACAO_ID: null,
                    CHAMADO_DATA: null,
                    TG_PRIORIDADE_ID: null
                };
            }
        }
    },

    actions: {
        setChamados({ commit }, chamados) {
            commit('setChamados', chamados);
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination);
        },
        setChamadoPesquisa({ commit }, chamadoPesquisa) {
            commit('setChamadoPesquisa', chamadoPesquisa);
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl'];
            let page = context.state.pagination.current_page;

            return axios({
                method: 'GET',
                url: `${baseUrl}/chamado_acompanhamento/search`,
                params: {
                    page,
                    ...context.state.chamadoPesquisa
                }
            }).then(r => {
                context.dispatch('setChamados', r.data.data);
                context.dispatch('setPagination', r.data);
            }).catch(e => {
                console.error('ERRO AO BUSCAR ACOMPANHAMENTO: ', e);
                context.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: msgId,
                    response: e.response
                }, { root: true });
            });
        },
        buscarChamado(context, { id, msgId }) {
            let baseUrl = context.rootGetters['getBaseUrl'];
            context.dispatch('AtualizacaoClinicaModule/clear', null, { root: true });
            return axios({
                method: 'GET',
                url: `${baseUrl}/chamado_acompanhamento/buscar/${id}`
            }).then(r => {
                context.dispatch('AtualizacaoClinicaModule/setChamado', {
                    chamado: r.data,
                    contexto: 'acompanhamento'
                }, { root: true });
            }).catch(e => {
                console.error('ERRO AO BUSCAR DETALHES DO CHAMADO: ', e);
                context.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: msgId,
                    response: e.response
                }, { root: true });
            });
        }
    }
}
