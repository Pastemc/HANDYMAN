import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ============================================
// CONFIGURAR AXIOS
// ============================================
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = '/api'; // ✅ SIN http://127.0.0.1:8000
window.axios.defaults.withCredentials = true;

// ============================================
// INTERCEPTOR: AGREGAR TOKEN A CADA REQUEST
// ============================================
window.axios.interceptors.request.use(
    config => {
        // Obtener token de localStorage
        const token = localStorage.getItem('token'); // ✅ Cambié 'auth_token' a 'token'
        
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Agregar idioma a cada request (para traducción automática)
        const locale = localStorage.getItem('locale') || 'es';
        config.headers['Accept-Language'] = locale;

        return config;
    },
    error => {
        return Promise.reject(error);
    }
);

// ============================================
// INTERCEPTOR: MANEJAR ERRORES DE RESPUESTA
// ============================================
window.axios.interceptors.response.use(
    response => response,
    error => {
        // Manejar error 401 (No autorizado)
        if (error.response?.status === 401) {
            const currentPath = window.location.pathname;
            
            // NO hacer nada si ya estamos en login, register o portal
            if (!currentPath.includes('/login') && 
                !currentPath.includes('/register') && 
                currentPath !== '/') {
                
                console.error('Error 401: No autorizado');
                
                // Limpiar datos de autenticación
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                
                // Redirigir al portal
                window.location.href = '/';
            }
        }

        // Manejar error 403 (Prohibido)
        if (error.response?.status === 403) {
            console.error('Error 403: No tienes permisos para esta acción');
        }

        // Manejar error 500 (Error del servidor)
        if (error.response?.status === 500) {
            console.error('Error 500: Error del servidor');
        }

        return Promise.reject(error);
    }
);

// ============================================
// CONFIGURAR LARAVEL ECHO (WebSockets)
// ============================================
const token = localStorage.getItem('token'); // ✅ Cambié 'auth_token' a 'token'

if (token) {
    window.Pusher = Pusher;

    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
            wsHost: window.location.hostname,
            wsPort: 6001,
            wssPort: 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: 'application/json',
                },
            },
        });

        console.log('✅ Laravel Echo initialized');
    } catch (error) {
        console.error('❌ Error initializing Echo:', error);
    }
} else {
    console.log('⚠️ No auth token, Echo not initialized');
}