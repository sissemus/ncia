export default {
    namespaced: true,
    state: {
        departamentos: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        departamentoPesquisa: {
            DEPARTAMENTO_ID: null,
            HIERARQUIA_ID: null,
            DEPARTAMENTO_NOME: null,
            DEPARTAMENTO_SIGLA: null,
            DEPARTAMENTO_DESCRICAO: null,
            DEPARTAMENTO_ATIVO: null,
        }
    },
    getters: {
        getDepartamentoPesquisa(state) {
            return state.departamentoPesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getDepartamentos(state) {
            return state.departamentos
        },
    },
    mutations: {
        setDepartamentoPesquisa(state, departamentoPesquisa = null) {
            if (departamentoPesquisa) {
                state.departamentoPesquisa = JSON.parse(JSON.stringify(departamentoPesquisa))
            } else {
                state.departamentoPesquisa = {
                    DEPARTAMENTO_ID: null,
                    HIERARQUIA_ID: null,
                    DEPARTAMENTO_NOME: null,
                    DEPARTAMENTO_SIGLA: null,
                    DEPARTAMENTO_DESCRICAO: null,
                    DEPARTAMENTO_ATIVO: null,
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
        setDepartamentos(state, departamentos) {
            state.departamentos = JSON.parse(JSON.stringify(departamentos))
        },
    },
    actions: {
        setDepartamentoPesquisa({ commit }, departamentoPesquisa) {
            commit('setDepartamentoPesquisa', departamentoPesquisa)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setDepartamentos({ commit }, departamentos) {
            commit('setDepartamentos', departamentos)
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/departamento/search`,
                params: {
                    page: page,
                    ...context.state.departamentoPesquisa,
                }
            }).then(r => {
                context.dispatch('setDepartamentos', r.data['data']).then()
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
