export default {
    namespaced: true,

    state: {
        unidades: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        unidadePesquisa: {
            UNIDADE_ID: null,
            UNIDADE_NOME: null,
            UNIDADE_SOLICITANTE: null,
            UNIDADE_ATIVO: null
        }
    },

    getters: {
        getUnidadePesquisa(state) {
            return state.unidadePesquisa;
        },

        getPagination(state) {
            return state.pagination;
        },

        getUnidades(state) {
            return state.unidades;
        }
    },

    mutations: {
        setUnidadePesquisa(state, unidadePesquisa = null) {
            if (unidadePesquisa) {
                state.unidadePesquisa = JSON.parse(JSON.stringify(unidadePesquisa));
            } else {
                state.unidadePesquisa = {
                    UNIDADE_ID: null,
                    UNIDADE_NOME: null,
                    UNIDADE_SOLICITANTE: null,
                    UNIDADE_ATIVO: null
                };
            }
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

        setUnidades(state, unidades) {
            state.unidades = JSON.parse(JSON.stringify(unidades));
        }
    },

    actions: {
        setUnidadePesquisa({ commit }, unidadePesquisa) {
            commit('setUnidadePesquisa', unidadePesquisa);
        },

        setPagination({ commit }, pagination) {
            commit('setPagination', pagination);
        },

        setUnidades({ commit }, unidades) {
            commit('setUnidades', unidades);
        },

        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl'];
            let page = context.state.pagination.current_page;

            axios({
                method: 'GET',
                url: `${baseUrl}/unidade/search`,
                params: {
                    page,
                    ...context.state.unidadePesquisa
                }
            }).then(r => {
                context.dispatch('setUnidades', r.data.data);
                context.dispatch('setPagination', r.data);
            }).catch(e => {
                console.error('ERRO: ', e);
                this.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: msgId,
                    response: e.response
                });
            });
        }
    }
}