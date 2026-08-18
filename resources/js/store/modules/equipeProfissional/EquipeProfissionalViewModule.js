export default {
    namespaced: true,
    state: {
        equipeProfissionais: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        equipeProfissionalPesquisa: {
            EQUIPE_PROFISSIONAL_ID: null,
            EQUIPE_ID: null,
            PROFISSIONAL_ID: null,
            EQUIPE_PROFISSIONAL_ATIVO: null,
        }
    },
    getters: {
        getEquipeProfissionalPesquisa(state) {
            return state.equipeProfissionalPesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getEquipeProfissionais(state) {
            return state.equipeProfissionais
        },
    },
    mutations: {
        setEquipeProfissionalPesquisa(state, equipeProfissionalPesquisa = null) {
            if (equipeProfissionalPesquisa) {
                state.equipeProfissionalPesquisa = JSON.parse(JSON.stringify(equipeProfissionalPesquisa))
            } else {
                state.equipeProfissionalPesquisa = {
                    EQUIPE_PROFISSIONAL_ID: null,
                    EQUIPE_ID: null,
                    PROFISSIONAL_ID: null,
                    EQUIPE_PROFISSIONAL_ATIVO: null
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
        setEquipeProfissionais(state, equipeProfissionais) {
            state.equipeProfissionais = JSON.parse(JSON.stringify(equipeProfissionais))
        },
    },
    actions: {
        setEquipeProfissionalPesquisa({ commit }, equipeProfissionalPesquisa) {
            commit('setEquipeProfissionalPesquisa', equipeProfissionalPesquisa)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setEquipeProfissionais({ commit }, equipeProfissionais) {
            commit('setEquipeProfissionais', equipeProfissionais)
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            axios({
                method: 'GET',
                url: `${baseUrl}/equipeProfissional/search`,
                params: {
                    page: page,
                    ...context.state.equipeProfissionalPesquisa,
                }
            }).then(r => {
                context.dispatch('setEquipeProfissionais', r.data['data']).then()
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
