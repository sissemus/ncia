export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        orientacoes: [],
    },
    getters: {
        getOrientacoes(state) {
            return state.orientacoes
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setOrientacoes(state, orientacoes) {
            state.orientacoes = JSON.parse(JSON.stringify(orientacoes))
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        },
    },
    actions: {
        setOrientacoes({commit}, orientacoes) {
            commit('setOrientacoes', orientacoes)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
