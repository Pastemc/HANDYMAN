<template>
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <router-link to="/" class="brand-link">
            <i class="brand-icon fas fa-tools"></i>
            <span class="brand-text font-weight-light">Heaven's Home</span>
        </router-link>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <div class="user-avatar-sidebar">
                        {{ getUserInitials }}
                    </div>
                </div>
                <div class="info">
                    <router-link to="/dashboard/profile" class="d-block">
                        {{ user?.name }}
                    </router-link>
                    <small class="text-muted">{{ getUserRole }}</small>
                </div>
            </div>

            <!-- Language Selector -->
            <div class="px-3 pb-3">
                <LanguageSelector />
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <router-link :to="getDashboardRoute" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>{{ currentLang === 'es' ? 'Panel de Control' : 'Dashboard' }}</p>
                        </router-link>
                    </li>

                    <!-- ADMINISTRATION - DROPDOWN MENU (Admin/SuperAdmin Only) -->
                    <template v-if="isAdmin || isSuperAdmin">
                        <li class="nav-header">{{ currentLang === 'es' ? 'ADMINISTRACIÓN' : 'ADMINISTRATION' }}</li>
                        <li class="nav-item" :class="{ 'menu-open': configMenuOpen }">
                            <a href="#" class="nav-link" @click.prevent="toggleConfigMenu">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    {{ currentLang === 'es' ? 'Configuración' : 'Configuration' }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <router-link to="/dashboard/admin/users" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ currentLang === 'es' ? 'Usuarios' : 'Users' }}</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/dashboard/admin/handymen" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Handymen</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/dashboard/admin/service-categories" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ currentLang === 'es' ? 'Categorías' : 'Categories' }}</p>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/dashboard/admin/service-requests" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ currentLang === 'es' ? 'Solicitudes' : 'Requests' }}</p>
                                    </router-link>
                                </li>
                            </ul>
                        </li>

                        <!-- Registration Requests -->
                        <li class="nav-item">
                            <router-link to="/dashboard/admin/client-registrations" class="nav-link">
                                <i class="nav-icon fas fa-user-plus"></i>
                                <p>
                                    {{ currentLang === 'es' ? 'Solicitudes de Registro' : 'Registration Requests' }}
                                    <span v-if="pendingRegistrations > 0" class="badge badge-warning right">
                                        {{ pendingRegistrations }}
                                    </span>
                                </p>
                            </router-link>
                        </li>

                        <!-- Other Admin Menus -->
                        <li class="nav-item">
                            <router-link to="/dashboard/admin/payments" class="nav-link">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>{{ currentLang === 'es' ? 'Pagos' : 'Payments' }}</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dashboard/admin/reports" class="nav-link">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>{{ currentLang === 'es' ? 'Reportes' : 'Reports' }}</p>
                            </router-link>
                        </li>
                    </template>

                    <!-- SUPER ADMIN (SuperAdmin Only) -->
                    <template v-if="isSuperAdmin">
                        <li class="nav-header">SUPER ADMIN</li>
                        <li class="nav-item">
                            <router-link to="/dashboard/superadmin/roles" class="nav-link">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>{{ currentLang === 'es' ? 'Roles' : 'Roles' }}</p>
                            </router-link>
                        </li>
                    </template>

                    <!-- CLIENT MENU -->
                    <template v-if="isClient">
                        <li class="nav-header">{{ currentLang === 'es' ? 'SERVICIOS' : 'SERVICES' }}</li>
                        <li class="nav-item">
                            <router-link to="/dashboard/client/services" class="nav-link">
                                <i class="nav-icon fas fa-list"></i>
                                <p>{{ currentLang === 'es' ? 'Categorías de Servicio' : 'Service Categories' }}</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dashboard/client/request/create" class="nav-link">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>{{ currentLang === 'es' ? 'Nueva Solicitud' : 'New Request' }}</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dashboard/client/requests" class="nav-link">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>{{ currentLang === 'es' ? 'Mis Solicitudes' : 'My Requests' }}</p>
                            </router-link>
                        </li>
                    </template>

                    <!-- HANDYMAN MENU -->
                    <template v-if="isHandyman">
                        <li class="nav-header">{{ currentLang === 'es' ? 'TRABAJOS' : 'JOBS' }}</li>
                        <li class="nav-item">
                            <router-link to="/dashboard/handyman/available-jobs" class="nav-link">
                                <i class="nav-icon fas fa-briefcase"></i>
                                <p>{{ currentLang === 'es' ? 'Trabajos Disponibles' : 'Available Jobs' }}</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dashboard/handyman/my-jobs" class="nav-link">
                                <i class="nav-icon fas fa-tasks"></i>
                                <p>{{ currentLang === 'es' ? 'Mis Trabajos' : 'My Jobs' }}</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/dashboard/handyman/profile" class="nav-link">
                                <i class="nav-icon fas fa-user-tie"></i>
                                <p>{{ currentLang === 'es' ? 'Mi Perfil Profesional' : 'My Professional Profile' }}</p>
                            </router-link>
                        </li>
                    </template>

                    <!-- COMMUNICATION -->
                    <li class="nav-header">{{ currentLang === 'es' ? 'COMUNICACIÓN' : 'COMMUNICATION' }}</li>
                    <li class="nav-item">
                        <router-link to="/dashboard/chat" class="nav-link">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>
                                {{ currentLang === 'es' ? 'Mensajes' : 'Messages' }}
                                <span v-if="unreadMessagesLocal > 0" class="badge badge-danger right">
                                    {{ unreadMessagesLocal }}
                                </span>
                            </p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/dashboard/notifications" class="nav-link">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>
                                {{ currentLang === 'es' ? 'Notificaciones' : 'Notifications' }}
                                <span v-if="unreadNotificationsLocal > 0" class="badge badge-warning right">
                                    {{ unreadNotificationsLocal }}
                                </span>
                            </p>
                        </router-link>
                    </li>

                    <!-- ACCOUNT -->
                    <li class="nav-header">{{ currentLang === 'es' ? 'CUENTA' : 'ACCOUNT' }}</li>
                    <li class="nav-item">
                        <router-link to="/dashboard/profile" class="nav-link">
                            <i class="nav-icon fas fa-user-cog"></i>
                            <p>{{ currentLang === 'es' ? 'Perfil' : 'Profile' }}</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <a href="#" @click.prevent="handleLogout" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>{{ currentLang === 'es' ? 'Cerrar Sesión' : 'Logout' }}</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<script>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import LanguageSelector from './LanguageSelector.vue';
import i18n from '../plugins/i18n';

export default {
    name: 'Sidebar',
    components: {
        LanguageSelector
    },
    setup() {
        const store = useStore();
        const router = useRouter();
        const unreadNotificationsLocal = ref(0);
        const unreadMessagesLocal = ref(0);
        const pendingRegistrations = ref(0);
        const configMenuOpen = ref(false);
        let refreshInterval = null;

        const user = computed(() => store.getters['auth/user']);
        const isClient = computed(() => store.getters['auth/isClient']);
        const isHandyman = computed(() => store.getters['auth/isHandyman']);
        const isAdmin = computed(() => store.getters['auth/isAdmin']);
        const isSuperAdmin = computed(() => store.getters['auth/isSuperAdmin']);
        const currentLang = computed(() => i18n.locale);

        const getUserInitials = computed(() => {
            if (!user.value?.name) return '?';
            return user.value.name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
        });

        const getUserRole = computed(() => {
            if (!user.value?.roles || user.value.roles.length === 0) return '';
            return user.value.roles[0].display_name;
        });

        const getDashboardRoute = computed(() => {
            if (isClient.value) return '/dashboard/client';
            if (isHandyman.value) return '/dashboard/handyman';
            if (isAdmin.value || isSuperAdmin.value) return '/dashboard/admin';
            return '/';
        });

        const toggleConfigMenu = () => {
            configMenuOpen.value = !configMenuOpen.value;
        };

        const fetchUnreadCounts = async () => {
            try {
                const notifResponse = await window.axios.get('/notifications/unread/count');
                unreadNotificationsLocal.value = notifResponse.data.count || 0;

                const convResponse = await window.axios.get('/conversations/unread/count');
                unreadMessagesLocal.value = convResponse.data.count || 0;

                // Get pending registrations (admin only)
                if (isAdmin.value || isSuperAdmin.value) {
                    const regResponse = await window.axios.get('/client-registrations', { 
                        params: { status: 'pending' } 
                    });
                    pendingRegistrations.value = regResponse.data.total || 0;
                }
            } catch (error) {
                console.error('Error fetching counts:', error);
            }
        };

        const handleLogout = async () => {
            try {
                // Call backend to invalidate token
                await store.dispatch('auth/logout');
                
                // ✅ REDIRECT TO PORTAL (NOT LOGIN)
                router.push('/');
            } catch (error) {
                console.error('Logout error:', error);
                
                // Even with error, redirect to portal
                router.push('/');
            }
        };

        onMounted(() => {
            fetchUnreadCounts();
            refreshInterval = setInterval(fetchUnreadCounts, 30000);
        });

        onUnmounted(() => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        return {
            user,
            isClient,
            isHandyman,
            isAdmin,
            isSuperAdmin,
            unreadNotificationsLocal,
            unreadMessagesLocal,
            pendingRegistrations,
            configMenuOpen,
            currentLang,
            getUserInitials,
            getUserRole,
            getDashboardRoute,
            toggleConfigMenu,
            handleLogout,
        };
    },
};
</script>

<style scoped>
.brand-icon {
    font-size: 1.5rem;
    margin-right: 0.5rem;
    color: #ffd700;
}

.user-avatar-sidebar {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.nav-link.router-link-active {
    background-color: rgba(255, 255, 255, 0.1);
}

.badge {
    font-size: 0.65rem;
    padding: 2px 6px;
    border-radius: 10px;
}

.badge-danger {
    background-color: #dc3545 !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Dropdown menu styles */
.nav-treeview {
    padding-left: 1rem;
}

.nav-treeview .nav-link {
    padding-left: 2rem;
}

.nav-item.menu-open > .nav-link i.right {
    transform: rotate(-90deg);
}

.nav-link i.right {
    transition: transform 0.3s ease;
}
</style>