export default {
    namespaced: true,
    state: {
        equipes: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        equipePesquisa: {
            EQUIPE_ID: null,
            VEICULO_ID: null,
            PROFISSIONAL_ID: null,
            EQUIPE_ATIVO: null,
<<<<<<< HEAD
            EQUIPE_DATA_INI: null,
            EQUIPE_DATA_FIM: null,
=======
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
        }
    },
    getters: {
        getEquipePesquisa(state) {
            return state.equipePesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getEquipes(state) {
            return state.equipes
        },
    },
    mutations: {
        setEquipePesquisa(state, equipePesquisa = null) {
            if (equipePesquisa) {
                state.equipePesquisa = JSON.parse(JSON.stringify(equipePesquisa))
            } else {
                state.equipePesquisa = {
                    EQUIPE_ID: null,
                    VEICULO_ID: null,
                    PROFISSIONAL_ID: null,
                    EQUIPE_ATIVO: null,
<<<<<<< HEAD
                    EQUIPE_DATA_INI: null,
                    EQUIPE_DATA_FIM: null,
=======
>>>>>>> b5ca06c (implementar abertura de chamado pela unidade)
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
        setEquipes(state, equipes) {
            state.equipes = JSON.parse(JSON.stringify(equipes))
        },
    },
    actions: {
        setEquipePesquisa({ commit }, equipePesquisa) {
            commit('setEquipePesquisa', equipePesquisa)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setEquipes({ commit }, equipes) {
            commit('setEquipes', equipes)
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/equipe/search`,
                params: {
                    page: page,
                    ...context.state.equipePesquisa,
                }
            }).then(r => {
                context.dispatch('setEquipes', r.data['data']).then()
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
