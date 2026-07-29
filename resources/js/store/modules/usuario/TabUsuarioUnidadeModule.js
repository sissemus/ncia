export default {
    namespaced: true,
    state: {
        usuarioUnidade: {
            USUARIO_UNIDADE_ID: null,
            USUARIO_ID: null,
            UNIDADE_ID: null
        },
        unidade: {
            UNIDADE_ID: null,
            UNIDADE_NOME: null
        }
    },
    getters: {
        getUsuarioUnidade(state) {
            return state.usuarioUnidade
        },
        getUnidade(state) {
            return state.unidade
        }
    },
    mutations: {
        setUsuarioUnidade(state, usuarioUnidade = null) {
            if (usuarioUnidade) {
                state.usuarioUnidade = JSON.parse(JSON.stringify(usuarioUnidade))
            } else {
                state.usuarioUnidade = {
                    USUARIO_UNIDADE_ID: null,
                    USUARIO_ID: null,
                    UNIDADE_ID: null
                }
            }
        },
        setUnidade(state, unidade = null) {
            if (unidade) {
                state.unidade = JSON.parse(JSON.stringify(unidade))
            } else {
                state.unidade = {
                    UNIDADE_ID: null,
                    UNIDADE_NOME: null
                }
            }
        }
    },
    actions: {
        setUsuarioUnidade({ commit }, usuarioUnidade) {
            commit('setUsuarioUnidade', usuarioUnidade)
        },
        setUnidade({ commit }, unidade) {
            commit('setUnidade', unidade)
        }
    }
}