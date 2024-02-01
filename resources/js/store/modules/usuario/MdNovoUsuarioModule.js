import Usuario from "../payloads/Usuario.json"
export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        usuario: JSON.parse(JSON.stringify(Usuario)),
        modulo: null
    },
    getters: {
        getModulo(state) {
            return state.modulo
        },
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
        setModulo(state, modulo) {
            state.modulo = modulo
        },
        spliceUsuarioLocal(state, i) {
            state.usuario['usuarioLocais'].splice(i, 1)
        },
        addUsuarioLocal(state, local) {
            let i = state.usuario['usuarioLocais'].findIndex(r => {
                return r['LOCAL_ID'] === local['LOCAL_ID']
            })
            if (i === -1) {
                state.usuario['usuarioLocais'].push({
                    USUARIO_LOCAL_ID: null,
                    USUARIO_ID: state.usuario['USUARIO_ID'],
                    USUARIO_LOCAL_ATIVO: 1,
                    LOCAL_ID: local['LOCAL_ID'],
                    local: JSON.parse(JSON.stringify(local))
                })
            } else {
                throw "Local já adicionado"
            }
        },
        setUsuario(state, usuario = null) {
            if (usuario) {
                state.usuario = JSON.parse(JSON.stringify(usuario))
            } else {
                state.usuario = JSON.parse(JSON.stringify(Usuario))
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
        spliceUsuarioLocal({commit}, i) {
            commit('spliceUsuarioLocal', i)
        },
        setModulo({commit}, modulo) {
            commit('setModulo', modulo)
        },
        addUsuarioLocal({commit}, local) {
            commit("addUsuarioLocal", local)
        },
        setUsuario({commit}, usuario) {
            commit('setUsuario', usuario)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
