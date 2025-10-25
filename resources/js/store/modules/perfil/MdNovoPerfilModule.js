import Perfil from "../payloads/Perfil.json"
import Aplicacao from "../payloads/Aplicacao.json"
export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        perfil: JSON.parse(JSON.stringify(Perfil)),
        aplicacao: JSON.parse(JSON.stringify(Aplicacao)),
        modulo: null,
        tree: [],
    },
    getters: {
        getModulo(state) {
            return state.modulo
        },
        getTree(state) {
            return state.tree
        },
        getAplicacao(state) {
            return state.aplicacao
        },
        getPerfil(state) {
            return state.perfil
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setModulo(state, modulo) {
            state.modulo = modulo
        },
        setTree(state, tree) {
            state.tree = JSON.parse(JSON.stringify(tree))
        },
        setAplicacao(state, aplicacao = null) {
            if (aplicacao) {
                state.aplicacao = JSON.parse(JSON.stringify(aplicacao))
            } else {
                state.aplicacao = JSON.parse(JSON.stringify(Aplicacao))
            }
        },
        setPerfil(state, perfil = null) {
            if (perfil) {
                state.perfil = JSON.parse(JSON.stringify(perfil))
                state.tree = perfil['acessos'].map(r => {
                    return r['aplicacao']
                })
            } else {
                state.perfil = JSON.parse(JSON.stringify(Perfil))
                state.tree = []
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
        setModulo({commit}, modulo) {
            commit('setModulo', modulo)
        },
        setTree({commit}, tree) {
            commit('setTree', tree)
        },
        setAplicacao({commit}, aplicacao) {
            commit('setAplicacao', aplicacao)
        },
        setPerfil({commit}, perfil) {
            commit('setPerfil', perfil)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
