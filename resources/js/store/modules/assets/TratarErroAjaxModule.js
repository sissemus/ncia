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
            payload = payload || {};
            const response = payload.response || {};
            let mensagem = 'Ocorreu um erro inesperado.';

            if (response.status) {
                if (response.status === 422) {
                    const errors = response.data && response.data.errors
                        ? response.data.errors
                        : {};
                    let erros = [];
                    for (let key in errors) {
                        if (Object.prototype.hasOwnProperty.call(errors, key)) {
                            const mensagens = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
                            mensagens.forEach(r => erros.push(r));
                        }
                    }
                    if (erros.length) {
                        let errosUl = '<ul>';
                        erros.forEach((e)=>{
                            errosUl += `<li>${e}</li>`;
                        });
                        mensagem = errosUl + '</ul>';
                    } else {
                        mensagem = (response.data && response.data.message) || 'Existem dados inválidos.';
                    }
                } else if (response.status === 419) {
                    mensagem = (response.data && response.data.message) || 'A sessão expirou.';
                } else {
                    mensagem = response.status + ' - ' + (response.statusText || 'Erro na requisição');
                }
            } else if (response.stack || response.message) {
                mensagem = response.stack || response.message;
            }

            let i = state.alert.findIndex(r => r.id === payload.id);
            if (i < 0) return;
            state.alert[i].message = mensagem;
            state.alert[i].show = true;

        },
        setAlert(state, payload) {
            state.alert.push(payload);
        },
        fecharAlert(state, id) {
            let i = state.alert.findIndex(r => r.id === id);
            if (i < 0) return;
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
