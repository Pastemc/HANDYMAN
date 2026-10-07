import { reactive } from 'vue';
import es from '../lang/es';
import en from '../lang/en';

const translations = {
    es,
    en,
};

const i18n = reactive({
    locale: localStorage.getItem('locale') || 'es',
    
    t(key) {
        const keys = key.split('.');
        let value = translations[this.locale];
        
        for (const k of keys) {
            if (value && typeof value === 'object') {
                value = value[k];
            } else {
                return key;
            }
        }
        
        return value || key;
    },
    
    setLocale(locale) {
        if (translations[locale]) {
            this.locale = locale;
            localStorage.setItem('locale', locale);
            
            // Forzar actualización de la página
            window.location.reload();
        }
    },
    
    availableLocales() {
        return [
            { code: 'es', name: 'Español', flag: '🇪🇸' },
            { code: 'en', name: 'English', flag: '🇺🇸' },
        ];
    }
});

export default i18n;