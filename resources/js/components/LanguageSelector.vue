<template>
  <div class="language-selector">
    <button 
      @click="toggleDropdown" 
      class="language-button"
      title="Cambiar idioma"
    >
      <svg class="language-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
      </svg>
      <span class="language-text">{{ currentLanguage }}</span>
      <svg class="arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <div v-if="showDropdown" class="language-dropdown">
      <button 
        v-for="lang in languages" 
        :key="lang.code"
        @click="changeLanguage(lang.code)"
        class="language-option"
        :class="{ active: currentLocale === lang.code }"
      >
        <span class="flag">{{ lang.flag }}</span>
        <span class="name">{{ lang.name }}</span>
        <svg v-if="currentLocale === lang.code" class="check-icon" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import i18n from '../plugins/i18n'; // ✅ RUTA CORREGIDA

const showDropdown = ref(false);

const languages = [
  { code: 'es', name: 'Español', flag: '🇪🇸' },
  { code: 'en', name: 'English', flag: '🇺🇸' }
];

const currentLocale = computed(() => i18n.locale);

const currentLanguage = computed(() => {
  const lang = languages.find(l => l.code === currentLocale.value);
  return lang ? `${lang.flag} ${lang.name}` : 'Español';
});

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
};

const changeLanguage = (code) => {
  i18n.setLocale(code);
  showDropdown.value = false;
};

// Cerrar dropdown al hacer click fuera
const handleClickOutside = (e) => {
  if (!e.target.closest('.language-selector')) {
    showDropdown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.language-selector {
  position: relative;
}

.language-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.5rem;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.language-button:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.language-icon,
.arrow-icon,
.check-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.language-text {
  font-size: 0.875rem;
  font-weight: 500;
}

.language-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  z-index: 1000;
  min-width: 180px;
}

.language-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: none;
  background: white;
  color: #1f2937;
  cursor: pointer;
  transition: background 0.2s ease;
}

.language-option:hover {
  background: #f3f4f6;
}

.language-option.active {
  background: #eff6ff;
  color: #3b82f6;
}

.flag {
  font-size: 1.25rem;
}

.name {
  flex: 1;
  text-align: left;
  font-weight: 500;
}
</style>