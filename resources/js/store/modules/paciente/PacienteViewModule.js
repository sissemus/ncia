export default {
    namespaced: true,

    state: {
        pacientes: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        },
        pacientePesquisa: {
            PACIENTE_ID: null,
            PACIENTE_NOME: null,
            PACIENTE_CPF: null,
            PACIENTE_CODIGO_TEMPORARIO: null,
            TG_SEXO_ID: null,
            PACIENTE_VULNERABILIDADE_SOCIAL: null
        }
    },

    getters: {
        getPacientes(state) {
            return state.pacientes;
        },

        getPagination(state) {
            return state.pagination;
        },

        getPacientePesquisa(state) {
            return state.pacientePesquisa;
        }
    },

    mutations: {
        setPacientes(state, pacientes) {
            state.pacientes = JSON.parse(JSON.stringify(pacientes));
        },

        setPagination(state, pagination = null) {
            if (pagination) {
                state.pagination = {
                    current_page: pagination.current_page,
                    total: pagination.total,
                    last_page: pagination.last_page
                };
            } else {
                state.pagination = {
                    current_page: 1,
                    total: 0,
                    last_page: 0
                };
            }
        },

        setPacientePesquisa(state, pacientePesquisa = null) {
            if (pacientePesquisa) {
                state.pacientePesquisa = JSON.parse(JSON.stringify(pacientePesquisa));
            } else {
                state.pacientePesquisa = {
                    PACIENTE_ID: null,
                    PACIENTE_NOME: null,
                    PACIENTE_CPF: null,
                    PACIENTE_CODIGO_TEMPORARIO: null,
                    TG_SEXO_ID: null,
                    PACIENTE_VULNERABILIDADE_SOCIAL: null
                };
            }
        }
    },

    actions: {
        setPacientes({ commit }, pacientes) {
            commit("setPacientes", pacientes);
        },

        setPagination({ commit }, pagination) {
            commit("setPagination", pagination);
        },

        setPacientePesquisa({ commit }, pacientePesquisa) {
            commit("setPacientePesquisa", pacientePesquisa);
        },

        search(context, msgId) {
            let baseUrl = context.rootGetters["getBaseUrl"];
            let pesquisa = JSON.parse(JSON.stringify(context.state.pacientePesquisa));
            pesquisa.page = context.state.pagination.current_page;

            if (pesquisa.PACIENTE_CPF)
                pesquisa.PACIENTE_CPF = pesquisa.PACIENTE_CPF.replace(/\D/g, "");

            axios.post(`${baseUrl}/paciente/pesquisar`, pesquisa)
                .then(r => {
                    context.dispatch("setPacientes", r.data.retorno.data);
                    context.dispatch("setPagination", r.data.retorno);
                })
                .catch(e => {
                    console.error("ERRO: ", e);
                    this.dispatch("TratarErroAjaxModule/tratarErro", {
                        id: msgId,
                        response: e.response
                    });
                });
        }
    }
}