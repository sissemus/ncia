export default {

    namespaced: true,
    state: {
        usuarioPerfil: {
            "USUARIO_PERFIL_ID" : null,
            "USUARIO_ID" : null,
            "PERFIL_ID" : null,
            "USUARIO_PERFIL_ATIVO" : null
        },
        perfil: {
            PERFIL_ID: null,
            PERFIL_NOME: null
        },
        usuario: {
            USUARIO_ID: null,
            USUARIO_NOME: null
        }
    },
    getters: {
        getUsuarioPerfil(state) {
            return state.usuarioPerfil
        },
        getPerfil(state) {
            return state.perfil
        }
    },
    mutations: {
        setUsuarioPerfil(state, usuarioPerfil = null) {
            if (usuarioPerfil) {
                state.usuarioPerfil = JSON.parse(JSON.stringify(usuarioPerfil))
            } else {
                state.usuarioPerfil = {
                    "USUARIO_PERFIL_ID" : null,
                    "USUARIO_ID" : null,
                    "PERFIL_ID" : null,
                    "USUARIO_PERFIL_ATIVO" : null
                }
            }
        },
        setPerfil(state, perfil) {
            if (perfil) {
                state.perfil = JSON.parse(JSON.stringify(perfil))
            } else {
                state.perfil = {
                    PERFIL_ID: null,
                    PERFIL_NOME: null
                }
            }
        },

    },
    actions: {
        setUsuarioPerfil({ commit }, usuarioPerfil) {
            commit('setUsuarioPerfil', usuarioPerfil)
        },
        setPerfil({ commit }, perfil) {
            commit('setPerfil', perfil)
        }
    }
}
