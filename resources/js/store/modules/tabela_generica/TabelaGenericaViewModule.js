export default {
    namespaced: true,
    state: {
        tabelas: [],
        colunas: [],
        tabelaSelecionada: {
            TABELA_GENERICA_ID: null,
            TABELA_ID: null,
            COLUNA_ID: null,
            DESCRICAO: null,
            ATIVO: null,
        },
    },
    getters: {
        getTabelaSelecionada(state) {
            return state.tabelaSelecionada
        },
        getColunas(state) {
            return state.colunas
        },
        getTabelas(state) {
            return state.tabelas
        },
    },
    mutations: {
        setTabelaSelecionada(state, tabelaSelecionada = null) {
            if (tabelaSelecionada) {
                state.tabelaSelecionada = JSON.parse(JSON.stringify(tabelaSelecionada))
            } else {
                state.tabelaSelecionada = {
                    TABELA_GENERICA_ID: null,
                    TABELA_ID: null,
                    COLUNA_ID: null,
                    DESCRICAO: null,
                    ATIVO: null,
                }
            }
        },
        setColunas(state, colunas = null) {
            if (colunas) {
                state.colunas = JSON.parse(JSON.stringify(colunas))
            } else {
                state.colunas = []
            }
        },
        setTabelas(state, tabelas) {
            state.tabelas = JSON.parse(JSON.stringify(tabelas))
        },
    },
    actions: {
        setTabelaSelecionada({commit}, tabelaSelecionada) {
            commit('setTabelaSelecionada', tabelaSelecionada)
        },
        setColunas({commit}, colunas) {
            commit('setColunas', colunas)
        },
        setTabelas({commit}, tabelas) {
            commit('setTabelas', tabelas)
        },
    }
}
