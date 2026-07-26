export default {
    namespaced: true,
    state: {
        diagnosticos: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        diagnosticoPesquisa: {
            DIAGNOSTICO_ID: null,
            DIAGNOSTICO_DESCRICAO: null,
            DIAGNOSTICO_ATIVO: null,
        }
    },
    getters: {
        getDiagnosticoPesquisa(state) {
            return state.diagnosticoPesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getDiagnosticos(state) {
            return state.diagnosticos
        },
    },
    mutations: {
        setDiagnosticoPesquisa(state, diagnosticoPesquisa = null) {
            if (diagnosticoPesquisa) {
                state.diagnosticoPesquisa = JSON.parse(JSON.stringify(diagnosticoPesquisa))
            } else {
                state.diagnosticoPesquisa = {
                    DIAGNOSTICO_ID: null,
                    DIAGNOSTICO_DESCRICAO: null,
                    DIAGNOSTICO_ATIVO: null,
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
        setDiagnosticos(state, diagnosticos) {
            state.diagnosticos = JSON.parse(JSON.stringify(diagnosticos))
        },
    },
    actions: {
        setDiagnosticoPesquisa({ commit }, diagnosticoPesquisa) {
            commit('setDiagnosticoPesquisa', diagnosticoPesquisa)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setDiagnosticos({ commit }, diagnosticos) {
            commit('setDiagnosticos', diagnosticos)
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/diagnostico/search`,
                params: {
                    page: page,
                    ...context.state.diagnosticoPesquisa,
                }
            }).then(r => {
                context.dispatch('setDiagnosticos', r.data['data']).then()
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
