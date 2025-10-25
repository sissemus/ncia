export default {
    namespaced: true,
    state:{
        aplicacao:{
            APLICACAO_ID: null,
            APLICACAO_NOME: null,
            APLICACAO_URL: null,
            APLICACAO_GESTAO: null,
            APLICACAO_ATIVA: null,
            APLICACAO_ORDEM: null,
            APLICACAO_PAI_ID: null,
            APLICACAO_ICONE: null,
            children:[],
        },
        showModal: false,
        fullScreen: false,
    },
    getters:{
        getAplicacao(state){
            return state.aplicacao;
        },
        getShowModal(state){
            return state.showModal;
        },
        getFullScreen(state){
            return state.fullScreen;
        },
    },
    mutations:{
        setAplicacao(state,value){
            if(value){
                state.aplicacao = value;
            }else{
                state.aplicacao = {
                    APLICACAO_ID: null,
                    APLICACAO_NOME: null,
                    APLICACAO_URL: null,
                    APLICACAO_GESTAO: null,
                    APLICACAO_ATIVA: null,
                    APLICACAO_ORDEM: null,
                    APLICACAO_PAI_ID: null,
                    APLICACAO_ICONE: null,
                };
            }
        },
        setShowModal(state,value){
            state.showModal = value;
        },
        setFullScreen(state,value){
            state.fullScreen = value;
        },
    },
    actions:{
        setAplicacao({commit},value){
            commit('setAplicacao',value);
        },
        setShowModal({commit},value){
            commit('setShowModal',value);
        },
        setFullScreen({commit},value){
            commit('setFullScreen',value);
        },
    },
}