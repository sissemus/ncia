import UsuarioLocal from "../payloads/UsuarioLocal.json"
import Vacinacao from "../payloads/Vacinacao.json"
export default {
    namespaced: true,
    state: {
        usuarioLocal: JSON.parse(JSON.stringify(UsuarioLocal)),
        vacinacao: JSON.parse(JSON.stringify(Vacinacao)),
        vacinacoes: []
    },
    getters: {
        getVacinacoes(state) {
            return state.vacinacoes
        },
        getVacinacao(state) {
            return state.vacinacao
        },
        getUsuarioLocal(state) {
            return state.usuarioLocal
        },
    },
    mutations: {
        setVacinacoes(state, vacinacoes = []) {
            state.vacinacoes = JSON.parse(JSON.stringify(vacinacoes))
        },
        setVacinacao(state, vacinacao = null) {
            if (vacinacao) {
                state.vacinacao = JSON.parse(JSON.stringify(vacinacao))
            } else {
                state.vacinacao = JSON.parse(JSON.stringify(Vacinacao))
            }
        },
        setUsuarioLocal(state, usuarioLocal = null) {
            if (usuarioLocal) {
                state.usuarioLocal = JSON.parse(JSON.stringify(usuarioLocal))
            } else {
                state.usuarioLocal = JSON.parse(JSON.stringify(UsuarioLocal))
            }
        },
    },
    actions: {
        setVacinacoes({commit}, vacinacoes) {
            commit('setVacinacoes', vacinacoes)
        },
        setVacinacao({commit}, vacinacao) {
            commit('setVacinacao', vacinacao)
        },
        setUsuarioLocal({commit}, usuarioLocal) {
            commit('setUsuarioLocal', usuarioLocal)
        },
    }
}
