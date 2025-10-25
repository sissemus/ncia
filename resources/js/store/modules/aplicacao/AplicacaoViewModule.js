export default {
    namespaced: true,
    state:{
        aplicacoes:[]
    },
    getters:{
        getAplicacoes(state){
            return state.aplicacoes;
        },
    },
    mutations:{
        setAplicacoes(state,value){
            state.aplicacoes = value;
        },
    },
    actions:{
        setAplicacoes({commit},value){
            commit('setAplicacoes',value);
        },
    },
}