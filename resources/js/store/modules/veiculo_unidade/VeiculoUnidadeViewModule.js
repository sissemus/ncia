export default {
    namespaced: true,

    state: {
        vinculos: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        vinculoPesquisa: {
            VEICULO_ID: null,
            UNIDADE_ID: null,
            STATUS: 'ativo'
        }
    },

    getters: {
        getVinculoPesquisa(state) {
            return state.vinculoPesquisa;
        },

        getPagination(state) {
            return state.pagination;
        },

        getVinculos(state) {
            return state.vinculos;
        }
    },

    mutations: {
        setVinculoPesquisa(state, vinculoPesquisa = null) {
            if (vinculoPesquisa) {
                state.vinculoPesquisa = JSON.parse(JSON.stringify(vinculoPesquisa));
            } else {
                state.vinculoPesquisa = {
                    VEICULO_ID: null,
                    UNIDADE_ID: null,
                    STATUS: 'ativo'
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

        setVinculos(state, vinculos) {
            state.vinculos = JSON.parse(JSON.stringify(vinculos));
        }
    },

    actions: {
        setVinculoPesquisa({ commit }, vinculoPesquisa) {
            commit('setVinculoPesquisa', vinculoPesquisa);
        },

        setPagination({ commit }, pagination) {
            commit('setPagination', pagination);
        },

        setVinculos({ commit }, vinculos) {
            commit('setVinculos', vinculos);
        },

        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl'];
            let page = context.state.pagination.current_page;

            axios({
                method: 'GET',
                url: `${baseUrl}/veiculo_unidade/search`,
                params: {
                    page,
                    ...context.state.vinculoPesquisa
                }
            }).then(r => {
                context.dispatch('setVinculos', r.data.data);
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
