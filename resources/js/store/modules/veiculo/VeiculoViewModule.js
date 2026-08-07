export default {
    namespaced: true,

    state: {
        veiculos: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        veiculoPesquisa: {
            VEICULO_ID: null,
            VEICULO_IDENTIFICACAO: null,
            TG_TIPO_VEICULO_ID: null,
            TG_SITUACAO_VEICULO_ID: null,
            VEICULO_ATIVO: null
        }
    },

    getters: {
        getVeiculoPesquisa(state) {
            return state.veiculoPesquisa;
        },

        getPagination(state) {
            return state.pagination;
        },

        getVeiculos(state) {
            return state.veiculos;
        }
    },

    mutations: {
        setVeiculoPesquisa(state, veiculoPesquisa = null) {
            if (veiculoPesquisa) {
                state.veiculoPesquisa = JSON.parse(JSON.stringify(veiculoPesquisa));
            } else {
                state.veiculoPesquisa = {
                    VEICULO_ID: null,
                    VEICULO_IDENTIFICACAO: null,
                    TG_TIPO_VEICULO_ID: null,
                    TG_SITUACAO_VEICULO_ID: null,
                    VEICULO_ATIVO: null
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

        setVeiculos(state, veiculos) {
            state.veiculos = JSON.parse(JSON.stringify(veiculos));
        }
    },

    actions: {
        setVeiculoPesquisa({ commit }, veiculoPesquisa) {
            commit('setVeiculoPesquisa', veiculoPesquisa);
        },

        setPagination({ commit }, pagination) {
            commit('setPagination', pagination);
        },

        setVeiculos({ commit }, veiculos) {
            commit('setVeiculos', veiculos);
        },

        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl'];
            let page = context.state.pagination.current_page;

            axios({
                method: 'GET',
                url: `${baseUrl}/veiculo/search`,
                params: {
                    page,
                    ...context.state.veiculoPesquisa
                }
            }).then(r => {
                context.dispatch('setVeiculos', r.data.data);
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
