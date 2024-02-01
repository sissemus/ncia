export default {
    namespaced: true,
    state: {
        usuarios: [],
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
        getUsuarios(state) {
            return state.usuarios
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
        setUsuarios(state, usuarios) {
            state.usuarios = JSON.parse(JSON.stringify(usuarios))
        },
    },
    actions: {
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setUsuarios({commit}, usuarios) {
            commit('setUsuarios', usuarios)
        },
        listar(context, msgId) {
            console.log(context)
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/usuario/list-all`,
                params: {
                    page: page
                }
            }).then(r => {
                console.log(r.data)
                context.dispatch('setUsuarios', r.data['data']).then()
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
