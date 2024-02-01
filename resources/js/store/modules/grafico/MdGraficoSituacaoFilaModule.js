import Local from "../payloads/Local.json"
export default {
    namespaced: true,
    state: {
        showModal: false,
        fullScreen: false,
        modulo: null,
        local: JSON.parse(JSON.stringify(Local)),
        chartdata: {
            labels: [],
            datasets: [
                {
                    label: "Data One",
                    data: [40, 20],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                }
            ]
        },
    },
    getters: {
        getChartdata(state) {
            return state.chartdata
        },
        getLocal(state) {
            return state.local
        },
        getModulo(state) {
            return state.modulo
        },
        getOptions(state) {
            return state.options
        },
        getShowModal(state) {
            return state.showModal
        },
        getFullScreen(state) {
            return state.fullScreen
        },
    },
    mutations: {
        setChartdata(state, chartdata) {
            state.chartdata = JSON.parse(JSON.stringify(chartdata))
        },
        setLocal(state, local = null) {
            if (local) {
                state.local = JSON.parse(JSON.stringify(local))
            } else {
                state.local = JSON.parse(JSON.stringify(Local))
            }
        },
        setModulo(state, modulo) {
            state.modulo = modulo
        },
        setOptions(state, options) {
            state.options = JSON.parse(JSON.stringify(options))
        },
        setShowModal(state, showModal) {
            state.showModal = showModal
        },
        setFullScreen(state, fullScreen) {
            state.fullScreen = fullScreen
        },
    },
    actions: {
        setChartdata({commit}, chartdata) {
            commit('setChartdata', chartdata)
        },
        setLocal({commit}, local) {
            commit('setLocal', local)
        },
        setModulo({commit}, modulo) {
            commit('setModulo', modulo)
        },
        setOptions({commit}, options) {
            commit('setOptions', options)
        },
        setShowModal({commit}, showModal) {
            commit('setShowModal', showModal)
        },
        setFullScreen({commit}, fullScreen) {
            commit('setFullScreen', fullScreen)
        },
    }
}
