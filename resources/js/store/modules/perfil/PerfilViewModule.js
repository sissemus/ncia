export default {
    namespaced: true,
    state: {
        perfis: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        }
    },
    getters: {
        getPagination(state) {
            return state.pagination
        },
        getPerfis(state) {
            return state.perfis
        },
    },
    mutations: {
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
        setPerfis(state, perfis) {
            state.perfis = JSON.parse(JSON.stringify(perfis))
        },
    },
    actions: {
        setPerfis({commit}, perfis) {
            commit('setPerfis', perfis)
        },
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        listar(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/perfil/list`,
                params: {
                    page: page
                }
            }).then(r => {
                context.dispatch('setPerfis', r.data['data']).then()
                context.dispatch('setPagination',r.data).then()
            }).catch(e => {
                console.error('ERRO: ', e)
                this.rootState.dispatch('TratarErroAjaxModule/tratarErro', {
                    id: msgId,
                    response: e.response
                })
            })
        }
    }
}
