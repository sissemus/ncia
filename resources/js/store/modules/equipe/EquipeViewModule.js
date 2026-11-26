// Filtros do Cadastro de Equipes (lista paginada por equipe)
const pesquisaPadrao = () => ({
    EQUIPE_ID: null,
    VEICULO_ID: null,
    VEICULO_IDENTIFICACAO: null,
    EQUIPE_TURNO: null,
    PROFISSIONAL_NOME: null,
    SITUACAO: 'ATIVAS',          // ATIVAS | DESATIVADAS | TODAS
    SITUACAO_PLANTAO: null,      // VIGENTE | ENCERRADO
})

export default {
    namespaced: true,
    state: {
        equipes: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        equipePesquisa: pesquisaPadrao(),
        // Filtros da última pesquisa (o formulário só vale após clicar em Pesquisar)
        filtrosAplicados: pesquisaPadrao(),
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
        getFiltrosAplicados(state) {
            return state.filtrosAplicados
        }
    },
    mutations: {
        setEquipePesquisa(state, equipePesquisa = null) {
            state.equipePesquisa = equipePesquisa
                ? JSON.parse(JSON.stringify(equipePesquisa))
                : pesquisaPadrao()
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
        aplicarFiltros(state) {
            state.filtrosAplicados = JSON.parse(JSON.stringify(state.equipePesquisa))
        }
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
        aplicarFiltros({ commit }) {
            commit('aplicarFiltros')
        },
        search(context, msgId) {
            let baseUrl = context.rootGetters['getBaseUrl']
            let page = context.state.pagination.current_page
            const params = { page }

            // Envia só os filtros preenchidos
            Object.entries(context.state.filtrosAplicados).forEach(([chave, valor]) => {
                const texto = typeof valor === 'string' ? valor.trim() : valor
                if (texto !== null && texto !== undefined && texto !== '') params[chave] = texto
            })

            return axios({
                method: 'GET',
                url: `${baseUrl}/equipe/search`,
                params
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
