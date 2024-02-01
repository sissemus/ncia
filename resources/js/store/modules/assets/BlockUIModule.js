export default {
    namespaced: true,
    state: {
        overlay: true,
    },
    getters: {
        getOverlay(state) {
            return state.overlay;
        }
    },
    mutations: {
        setOverlay(state, value) {
            state.overlay = value;
        }
    },
    actions: {
        setOverlay({commit}, value) {
            commit('setOverlay', value);
        }
    }
}
