import { createStore } from 'vuex';
import auth from './modules/auth';
import notifications from './modules/notifications';
import chat from './modules/chat';

export default createStore({
    modules: {
        auth,
        notifications,
        chat,
    },
    state: {
        loading: false,
    },
    mutations: {
        SET_LOADING(state, value) {
            state.loading = value;
        },
    },
    actions: {
        setLoading({ commit }, value) {
            commit('SET_LOADING', value);
        },
    },
});