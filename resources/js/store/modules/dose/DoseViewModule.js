import Dose from "../payloads/Dose.json"
export default {
    namespaced: true,
    state: {
        dose: JSON.parse(JSON.stringify(Dose)),
        doses: [],
        pagination: {
            current_page: 1,
            total: 0,
            last_page: 0
        }
    },
    getters: {
        getPagination(state) {
            return state.pagination
        },
        getDoses(state) {
            return state.doses
        },
        getDose(state) {
            return state.dose
        },
    },
    mutations: {
        setPagination(state, pagination = null) {
            if (pagination) {
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
        setDoses(state, doses = null) {
            if (doses) {
                state.doses = JSON.parse(JSON.stringify(doses))
            } else {
                state.doses = []
            }
        },
        setDose(state, dose = null) {
            if (dose) {
                state.dose = JSON.parse(JSON.stringify(dose))
            } else {
                state.dose = JSON.parse(JSON.stringify(Dose))
            }
        },
    },
    actions: {
        setPagination({commit}, pagination) {
            commit('setPagination', pagination)
        },
        setDoses({commit}, doses) {
            commit('setDoses', doses)
        },
        setDose({commit}, dose) {
            commit('setDose', dose)
        },
    }
}
