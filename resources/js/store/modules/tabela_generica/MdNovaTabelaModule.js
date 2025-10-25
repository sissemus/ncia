export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        tabela: {
            TABELA_GENERICA_ID: null,
            TABELA_ID: null,
            COLUNA_ID: null,
            DESCRICAO: null,
            ATIVO: null,
        }
    },
    getters: {
        getTabela(state) {
            return state.tabela
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setTabela(state, tabela = null) {
            if (tabela) {
                state.tabela = JSON.parse(JSON.stringify(tabela))
            } else {
                state.tabela = {
                    TABELA_GENERICA_ID: null,
                    TABELA_ID: null,
                    COLUNA_ID: null,
                    DESCRICAO: null,
                    ATIVO: null,
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
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
