import Vacina from "../payloads/Vacina.json"
export default {
    namespaced: true,
    state: {
        vacina: JSON.parse(JSON.stringify(Vacina)),
        vacinas: []
    },
    getters: {
        getVacinas(state) {
            return state.vacinas
        },
        getVacina(state) {
            return state.vacina
        },
    },
    mutations: {
        setVacinas(state, vacinas) {
            state.vacinas = JSON.parse(JSON.stringify(vacinas))
        },
        setVacina(state, vacina = null) {
            if (vacina) {
                state.vacina = JSON.parse(JSON.stringify(vacina))
            } else {
                state.vacina = JSON.parse(JSON.stringify(Vacina))
            }
        },
    },
    actions: {
        setVacinas({commit}, vacinas) {
            commit('setVacinas', vacinas)
        },
        setVacina({commit}, vacina) {
            commit('setVacina', vacina)
        },
    }
}
