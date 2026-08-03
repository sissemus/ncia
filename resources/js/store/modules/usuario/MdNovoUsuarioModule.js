export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        usuario: {
            USUARIO_ID: null,
            USUARIO_LOGIN: null,
            USUARIO_SENHA: null,
            USUARIO_NOME: null,
            TG_DOCUMENTO_ID: 7,
            USUARIO_DOC: null,
            USUARIO_EMAIL: null,
            USUARIO_ATIVO: null,
            USUARIO_VIGENCIA: null,
            USUARIO_SENHA_CONFIRMATION: null,
            usuarioPerfis: [],
            usuarioUnidades: [],
        }
    },
    getters: {
        getUsuario(state) {
            return state.usuario
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setUsuario(state, usuario = null) {
            if (usuario) {
                state.usuario = JSON.parse(JSON.stringify(usuario))

                if (!state.usuario.usuarioPerfis)
                    state.usuario.usuarioPerfis = []

                if (!state.usuario.usuarioUnidades)
                    state.usuario.usuarioUnidades = []
            } else {
                state.usuario = {
                    USUARIO_ID: null,
                    USUARIO_LOGIN: null,
                    USUARIO_SENHA: null,
                    USUARIO_NOME: null,
                    TG_DOCUMENTO_ID: 7,
                    USUARIO_DOC: null,
                    USUARIO_EMAIL: null,
                    USUARIO_ATIVO: null,
                    USUARIO_VIGENCIA: null,
                    USUARIO_SENHA_CONFIRMATION: null,
                    usuarioPerfis: [],
                    usuarioUnidades: [],
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
        setUsuario({ commit }, usuario) {
            commit('setUsuario', usuario)
        },
        setShowModal({ commit }, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({ commit }, fullScreen) {
            commit('setFullScreen', fullScreen)
        }
    }
}