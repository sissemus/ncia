export default {
    namespaced: true,
    state: {
        usuarios: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        usuarioPesquisa: {
            USUARIO_NOME: null
        },
        listar:null
    },
    getters: {
        getUsuarioPesquisa(state) {
            return state.usuarioPesquisa
        },
        getPagination(state) {
            return state.pagination
        },
        getUsuarios(state) {
            return state.usuarios
        },
        getListar(state){
            return state.listar.bind(this);
        }
    },
    mutations: {
        setUsuarioPesquisa(state, usuarioPesquisa) {
            state.usuarioPesquisa = JSON.parse(JSON.stringify(usuarioPesquisa))
        },
        setPagination(state, pagination = null) {
            if (pagination) {
                pagination = pagination['retorno'];
                state.pagination = {
                    current_page: pagination.current_page,
                    total: pagination.total,
                    last_page: pagination.last_page
                }
            } else {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                }
            }
        },
        setUsuarios(state, usuarios) {
            state.usuarios = JSON.parse(JSON.stringify(usuarios))
        },
        setListar(state,funcao){
            state.listar = funcao;
        }
    },
    actions: {
        setUsuarioPesquisa({commit}, usuarioPesquisa) {
            commit('setUsuarioPesquisa', usuarioPesquisa)
        },
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setUsuarios({commit}, usuarios) {
            commit('setUsuarios', usuarios)
        },
        setListar({commit},funcao){
            commit('setListar',funcao);
        },
    }
}
