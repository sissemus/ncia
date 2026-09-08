import Chamado from "../../../models/Chamado";
import PacienteTemporario from "../../../models/PacienteTemporario";

export default {
    namespaced: true,

    state: {
        chamado: new Chamado(),
        paciente: null,
        pacienteTemporario: new PacienteTemporario(),
        pacienteVulnerabilidadeSocial: false,
        cpfPesquisa: null
    },

    getters: {
        getChamado(state) {
            return state.chamado;
        },

        getPaciente(state) {
            return state.paciente;
        },

        getPacienteTemporario(state) {
            return state.pacienteTemporario;
        },

        getPacienteVulnerabilidadeSocial(state) {
            return state.pacienteVulnerabilidadeSocial;
        },

        getCpfPesquisa(state) {
            return state.cpfPesquisa;
        }
    },

    mutations: {
        setChamado(state, chamado = null) {
            state.chamado = chamado ? JSON.parse(JSON.stringify(chamado)) : new Chamado();
        },

        setPaciente(state, paciente = null) {
            state.paciente = paciente ? JSON.parse(JSON.stringify(paciente)) : null;
        },

        setPacienteTemporario(state, pacienteTemporario = null) {
            state.pacienteTemporario = pacienteTemporario ? JSON.parse(JSON.stringify(pacienteTemporario)) : new PacienteTemporario();
        },

        setPacienteVulnerabilidadeSocial(state, pacienteVulnerabilidadeSocial = false) {
            state.pacienteVulnerabilidadeSocial = pacienteVulnerabilidadeSocial;
        },

        setCpfPesquisa(state, cpfPesquisa = null) {
            state.cpfPesquisa = cpfPesquisa;
        }
    },

    actions: {
        setChamado({ commit }, chamado) {
            commit("setChamado", chamado);
        },

        setPaciente({ commit }, paciente) {
            commit("setPaciente", paciente);
        },

        setPacienteTemporario({ commit }, pacienteTemporario) {
            commit("setPacienteTemporario", pacienteTemporario);
        },

        setPacienteVulnerabilidadeSocial({ commit }, pacienteVulnerabilidadeSocial) {
            commit("setPacienteVulnerabilidadeSocial", pacienteVulnerabilidadeSocial);
        },

        setCpfPesquisa({ commit }, cpfPesquisa) {
            commit("setCpfPesquisa", cpfPesquisa);
        },

        clear({ commit }) {
            commit("setChamado", null);
            commit("setPaciente", null);
            commit("setPacienteTemporario", null);
            commit("setPacienteVulnerabilidadeSocial", false);
            commit("setCpfPesquisa", null);
        }
    }
}