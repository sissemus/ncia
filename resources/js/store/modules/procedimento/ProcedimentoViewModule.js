export default {
    namespaced: true,
    state: {
        procedimentos: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        procedimentoPesquisa: {
            PROCEDIMENTO_ID: null,
            PROCEDIMENTO_DESCRICAO: null,
            PROCEDIMENTO_ATIVO: null,
        }
    },
    getters: {
        getProcedimentoPesquisa(state) {
            return state.procedimentoPesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getProcedimentos(state) {
            return state.procedimentos
        },
    },
    mutations: {
        setProcedimentoPesquisa(state, procedimentoPesquisa = null) {
            if (procedimentoPesquisa) {
                state.procedimentoPesquisa = JSON.parse(JSON.stringify(procedimentoPesquisa))
            } else {
                state.procedimentoPesquisa = {
                    PROCEDIMENTO_ID: null,
                    PROCEDIMENTO_DESCRICAO: null,
                    PROCEDIMENTO_ATIVO: null,
                }
            }
        },
        setPagination(state, pagination = null) {
            if (pagination) {
                state.pagination = {
                    current_page: pagination.current_page,
                    total: pagination.total,
                    last_page: pagination.last_page
                }
            } else {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                }
            }
        },
        setProcedimentos(state, procedimentos) {
            state.procedimentos = JSON.parse(JSON.stringify(procedimentos))
        },
    },
    actions: {
        setProcedimentoPesquisa({ commit }, procedimentoPesquisa) {
            commit('setProcedimentoPesquisa', procedimentoPesquisa)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setProcedimentos({ commit }, procedimentos) {
            commit('setProcedimentos', procedimentos)
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/procedimento/search`,
                params: {
                    page: page,
                    ...context.state.procedimentoPesquisa,
                }
            }).then(r => {
                context.dispatch('setProcedimentos', r.data['data']).then()
                context.dispatch('setPagination', r.data).then()
            }).catch(e => {
                console.error('ERRO: ', e)
                this.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: msgId,
                    response: e.response
                })
            })
        }
    }
}
