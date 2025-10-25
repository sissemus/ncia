export default {
    namespaced: true,
    state: {
        usuario: {
            USUARIO_ID: null,
            USUARIO_NOME: null,
        },
    },
    getters: {

        getUsuario(state) {
            return state.usuario
        }
    },
    mutations: {

        setUsuario(state, usuario = null) {
            if (usuario) {
                state.usuario = JSON.parse(JSON.stringify(usuario))
            } else {
                state.usuario = {
                    USUARIO_ID: null,
                    USUARIO_NOME: null,
                }
            }
        }
    },
    actions: {

        setUsuario({ commit }, usuario) {
            commit('setUsuario', usuario)
        }
    },
}

