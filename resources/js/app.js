import './bootstrap';
import { createApp } from 'vue';
import router from './router/index.js';
import store from './store/index.js';
import App from './components/App.vue';
import i18n from './plugins/i18n';

const app = createApp(App);

// Registrar plugins
app.use(router);
app.use(store);

// Registrar i18n globalmente
app.config.globalProperties.$t = (key) => {
    // Retornar la key tal cual porque la traducción la hace el backend
    return key;
};
app.config.globalProperties.$i18n = i18n;

// Hacer i18n disponible globalmente en window
window.i18n = i18n;

// Montar la aplicación
app.mount('#app');

console.log('Vue app mounted successfully with i18n support!');
console.log('Current locale:', i18n.locale);