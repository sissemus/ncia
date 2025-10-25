export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        coluna: {
            TABELA_GENERICA_ID: null,
            TABELA_ID: null,
            COLUNA_ID: null,
            DESCRICAO: null,
            ATIVO: null,
            tabela: {
                TABELA_GENERICA_ID: null,
                TABELA_ID: null,
                COLUNA_ID: null,
                DESCRICAO: null,
                ATIVO: null,
            }
        }
    },
    getters: {
        getColuna(state) {
            return state.coluna
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setTabela(state, tabela) {
            state.coluna.tabela = JSON.parse(JSON.stringify(tabela))
            state.coluna.TABELA_ID = JSON.parse(JSON.stringify(tabela['TABELA_ID']))
        },
        setColuna(state, coluna) {
            if (coluna) {
                state.coluna = JSON.parse(JSON.stringify(coluna))
            } else {
                state.coluna = {
                    TABELA_GENERICA_ID: null,
                    TABELA_ID: null,
                    COLUNA_ID: null,
                    DESCRICAO: null,
                    ATIVO: null,
                    tabela: {
                        TABELA_GENERICA_ID: null,
                        TABELA_ID: null,
                        COLUNA_ID: null,
                        DESCRICAO: null,
                        ATIVO: null,
                    }
                }
            }
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        },
    },
    actions: {
        setTabela({commit}, tabela) {
            commit('setTabela', tabela)
        },
        setColuna({commit}, coluna) {
            commit('setColuna', coluna)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
