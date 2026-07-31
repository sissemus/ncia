export default {
    namespaced: true,
    state: {
        profissionais: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        profissionalPesquisa: {
            PROFISSIONAL_NOME: null,
            PROFISSIONAL_CPF: null,
            TG_TIPO_PROFISSIONAL_ID: null,
            PROFISSIONAL_ATIVO: null
        }
    },
    getters: {
        getProfissionais(state) {
            return state.profissionais
        },
        getPagination(state) {
            return state.pagination
        },
        getProfissionalPesquisa(state) {
            return state.profissionalPesquisa
        }
    },
    mutations: {
        setProfissionais(state, profissionais) {
            state.profissionais = JSON.parse(JSON.stringify(profissionais))
        },
        setPagination(state, pagination = null) {
            if (pagination) {
                pagination = pagination.retorno

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
        setProfissionalPesquisa(state, profissionalPesquisa = null) {
            if (profissionalPesquisa) {
                state.profissionalPesquisa = JSON.parse(JSON.stringify(profissionalPesquisa))
            } else {
                state.profissionalPesquisa = {
                    PROFISSIONAL_NOME: null,
                    PROFISSIONAL_CPF: null,
                    TG_TIPO_PROFISSIONAL_ID: null,
                    PROFISSIONAL_ATIVO: null
                }
            }
        }
    },
    actions: {
        setProfissionais({ commit }, profissionais) {
            commit('setProfissionais', profissionais)
        },
        setPagination({ commit }, pagination) {
            commit('setPagination', pagination)
        },
        setProfissionalPesquisa({ commit }, profissionalPesquisa) {
            commit('setProfissionalPesquisa', profissionalPesquisa)
        },
        search({ state, commit, rootGetters }, msgId) {
            let profissionalPesquisa = JSON.parse(JSON.stringify(state.profissionalPesquisa))
            profissionalPesquisa.page = state.pagination.current_page

            if (profissionalPesquisa.PROFISSIONAL_CPF)
                profissionalPesquisa.PROFISSIONAL_CPF = profissionalPesquisa.PROFISSIONAL_CPF.replace(/\D/g, '')

            axios.post(`${rootGetters.getBaseUrl}/profissional/pesquisar`, profissionalPesquisa)
                .then(r => {
                    commit('setProfissionais', r.data.retorno.data)
                    commit('setPagination', r.data)
                })
                .catch(e => {
                    console.error('ERRO: ', e)
                    this.dispatch('TratarErroAjaxModule/tratarErro', {
                        id: msgId,
                        response: e.response
                    })
                })
        }
    }
}