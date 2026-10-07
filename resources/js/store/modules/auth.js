// ✅ NO IMPORTAR AXIOS - Usar window.axios configurado en bootstrap.js

export default {
    namespaced: true,
    
    state: {
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null, // ✅ Cambié 'auth_token' a 'token'
        isAuthenticated: !!localStorage.getItem('token'), // ✅ Cambié 'auth_token' a 'token'
    },
    
    mutations: {
        SET_USER(state, user) {
            state.user = user;
            state.isAuthenticated = !!user;
            if (user) {
                localStorage.setItem('user', JSON.stringify(user));
            } else {
                localStorage.removeItem('user');
            }
        },
        
        SET_TOKEN(state, token) {
            state.token = token;
            state.isAuthenticated = !!token;
            if (token) {
                localStorage.setItem('token', token);
                // ✅ Usar window.axios en lugar de axios
                window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                localStorage.removeItem('token');
                delete window.axios.defaults.headers.common['Authorization'];
            }
        },
        
        LOGOUT(state) {
            state.user = null;
            state.token = null;
            state.isAuthenticated = false;
            localStorage.removeItem('user');
            localStorage.removeItem('token');
            delete window.axios.defaults.headers.common['Authorization'];
        },
    },
    
    actions: {
        async login({ commit }, credentials) {
            try {
                // ✅ Usar window.axios
                const response = await window.axios.post('/login', credentials);
                const { user, token } = response.data;
                
                commit('SET_TOKEN', token);
                commit('SET_USER', user);
                
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async register({ commit }, userData) {
            try {
                // ✅ Usar window.axios
                const response = await window.axios.post('/register', userData);
                const { user, token } = response.data;
                
                commit('SET_TOKEN', token);
                commit('SET_USER', user);
                
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async logout({ commit }) {
            try {
                // ✅ Usar window.axios
                await window.axios.post('/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                commit('LOGOUT');
            }
        },
        
        async fetchUser({ commit, state }) {
            try {
                // Verificar que hay token antes de hacer la petición
                if (!state.token) {
                    commit('LOGOUT');
                    return null;
                }

                // ✅ Usar window.axios
                const response = await window.axios.get('/me');
                commit('SET_USER', response.data.user);
                return response.data.user;
            } catch (error) {
                // Solo hacer logout si el error es 401 (no autorizado)
                if (error.response?.status === 401) {
                    commit('LOGOUT');
                }
                throw error;
            }
        },
    },
    
    getters: {
        user: state => state.user,
        token: state => state.token,
        isAuthenticated: state => state.isAuthenticated,
        isSuperAdmin: state => state.user?.roles?.some(role => role.name === 'superadmin') || false,
        isAdmin: state => state.user?.roles?.some(role => role.name === 'admin') || false,
        isHandyman: state => state.user?.roles?.some(role => role.name === 'handyman') || false,
        isClient: state => state.user?.roles?.some(role => role.name === 'client') || false,
        userRole: state => state.user?.roles?.[0]?.name || null,
    },
};