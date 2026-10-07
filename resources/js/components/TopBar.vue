<template>
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <router-link to="/" class="nav-link">{{ $t('nav.home') }}</router-link>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Language Selector -->
            <li class="nav-item dropdown">
                <a 
                    class="nav-link dropdown-toggle" 
                    href="#" 
                    id="languageDropdown" 
                    role="button" 
                    data-toggle="dropdown" 
                    aria-haspopup="true" 
                    aria-expanded="false"
                >
                    <span class="flag-icon mr-1">{{ currentLocale.flag }}</span>
                    <span class="d-none d-md-inline">{{ currentLocale.code.toUpperCase() }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
                    <a 
                        v-for="locale in availableLocales" 
                        :key="locale.code"
                        @click.prevent="changeLocale(locale.code)"
                        href="#" 
                        class="dropdown-item"
                        :class="{ 'active': $i18n.locale === locale.code }"
                    >
                        <span class="flag-icon mr-2">{{ locale.flag }}</span>
                        {{ locale.name }}
                    </a>
                </div>
            </li>

            <!-- Theme Toggle -->
            <li class="nav-item">
                <a class="nav-link" @click="toggleTheme" href="#" role="button">
                    <i :class="isDarkMode ? 'fas fa-sun' : 'fas fa-moon'"></i>
                </a>
            </li>

            <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-comments"></i>
                    <span v-if="unreadMessages > 0" class="badge badge-danger navbar-badge">{{ unreadMessages }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <router-link to="/dashboard/chat" class="dropdown-item">
                        <div class="media">
                            <div class="media-body">
                                <h3 class="dropdown-item-title">{{ $t('nav.messages') }}</h3>
                                <p class="text-sm">{{ $t('messages.view_all') }}</p>
                            </div>
                        </div>
                    </router-link>
                </div>
            </li>

            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span v-if="unreadNotifications > 0" class="badge badge-warning navbar-badge">{{ unreadNotifications }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ unreadNotifications }} {{ $t('nav.notifications') }}</span>
                    <div class="dropdown-divider"></div>
                    <router-link to="/dashboard/notifications" class="dropdown-item">
                        <i class="fas fa-envelope mr-2"></i> {{ $t('buttons.view_all') }}
                    </router-link>
                </div>
            </li>

            <!-- User Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <div class="user-avatar">
                        {{ getUserInitials }}
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <div class="dropdown-item dropdown-header">
                        <div class="text-center">
                            <div class="user-avatar-large">
                                {{ getUserInitials }}
                            </div>
                            <h5 class="mt-2 mb-0">{{ user?.name }}</h5>
                            <small class="text-muted">{{ user?.email }}</small>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <router-link to="/dashboard/profile" class="dropdown-item">
                        <i class="fas fa-user mr-2"></i> {{ $t('nav.profile') }}
                    </router-link>
                    <div class="dropdown-divider"></div>
                    <a href="#" @click.prevent="handleLogout" class="dropdown-item">
                        <i class="fas fa-sign-out-alt mr-2"></i> {{ $t('nav.logout') }}
                    </a>
                </div>
            </li>
        </ul>
    </nav>
</template>

<script>
import { computed, ref, onMounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import i18n from '../plugins/i18n';

export default {
    name: 'TopBar',
    setup() {
        const store = useStore();
        const router = useRouter();
        const isDarkMode = ref(false);

        const user = computed(() => store.getters['auth/user']);
        const unreadNotifications = computed(() => store.getters['notifications/unreadCount']);
        const unreadMessages = computed(() => store.getters['chat/unreadCount']);

        const availableLocales = i18n.availableLocales();
        const currentLocale = computed(() => {
            return availableLocales.find(l => l.code === i18n.locale) || availableLocales[0];
        });

        const getUserInitials = computed(() => {
            if (!user.value?.name) return '?';
            return user.value.name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
        });

        const changeLocale = (locale) => {
            i18n.setLocale(locale);
        };

        const toggleTheme = () => {
            isDarkMode.value = !isDarkMode.value;
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light');
        };

        const handleLogout = async () => {
            try {
                await store.dispatch('auth/logout');
                router.push('/login');
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
            }
        };

        onMounted(() => {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                isDarkMode.value = true;
                document.body.classList.add('dark-mode');
            }

            store.dispatch('notifications/fetchUnreadCount');
            store.dispatch('chat/fetchUnreadCount');
        });

        return {
            user,
            unreadNotifications,
            unreadMessages,
            getUserInitials,
            isDarkMode,
            toggleTheme,
            handleLogout,
            availableLocales,
            currentLocale,
            changeLocale,
        };
    },
};
</script>

<style scoped>
.user-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.user-avatar-large {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 32px;
    margin: 0 auto;
}

.dropdown-menu {
    min-width: 280px;
}

.dropdown-header {
    padding: 15px;
}

.flag-icon {
    font-size: 18px;
}

.dropdown-item.active {
    background-color: #667eea;
    color: white;
}
</style>