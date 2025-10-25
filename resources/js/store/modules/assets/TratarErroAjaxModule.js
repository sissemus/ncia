export default {
    namespaced: true,
    state: {
        alert: [],
    },
    getters: {
        getAlert(state) {
            return state.alert;
        },
    },

    mutations: {
        tratarErro(state, payload) {
            let mensagem = '';

            if (payload.response.status) {
                if (payload.response.status === 422) {
                    let erros = [];
                    for (let key in payload.response.data.errors) {
                        if (payload.response.data.errors.hasOwnProperty(key)) {
                            payload.response.data.errors[key].forEach(r => {
                                erros.push(r);
                            });
                        }
                    }
                    let errosUl = '<ul>';
                    erros.forEach((e)=>{
                        errosUl += `<li>${e}</li>`;
                    });
                    errosUl+='</ul>';
                    mensagem = errosUl;
                }else if(payload.response.status === 419){
                    mensagem = payload.response.data.message;
                }
                else {
                    mensagem = payload.response.status + ' - ' + payload.response.statusText
                }
                let i = state.alert.findIndex(r => r.id === payload.id);
                state.alert[i].message = mensagem;
                state.alert[i].show = true;
            }
            else {
                let i = state.alert.findIndex(r => r.id === payload.id);
                state.alert[i].message = payload.response.stack;
                state.alert[i].show = true;
            }

        },
        setAlert(state, payload) {
            state.alert.push(payload);
        },
        fecharAlert(state, id) {
            let i = state.alert.findIndex(r => r.id === id);
            state.alert[i].show = false;
        }
    },

    actions: {
        tratarErro({commit}, payload) {
            commit('tratarErro', payload);
        },
        setAlert({commit}, payload) {
            commit('setAlert', payload);
        },
        fecharAlert({commit}, id) {
            commit('fecharAlert', id);
        }
    }
}
