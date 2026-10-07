import { createRouter, createWebHistory } from 'vue-router';
import store from '../store/index.js';

// ============================================
// LAYOUTS
// ============================================
import GuestLayout from '../layouts/GuestLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';

// ============================================
// VISTAS PÚBLICAS
// ============================================
import Portal from '../views/public/Portal.vue';

// ============================================
// AUTH VIEWS
// ============================================
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';

// ============================================
// CLIENT VIEWS
// ============================================
import ClientDashboard from '../views/client/Dashboard.vue';
import ServiceCategories from '../views/client/ServiceCategories.vue';
import CreateServiceRequest from '../views/client/CreateServiceRequest.vue';
import MyRequests from '../views/client/MyRequests.vue';
import RequestDetail from '../views/client/RequestDetail.vue';
import ClientServiceRequests from '../views/client/ClientServiceRequests.vue';

// ============================================
// HANDYMAN VIEWS
// ============================================
import HandymanDashboard from '../views/handyman/Dashboard.vue';
import AvailableJobs from '../views/handyman/AvailableJobs.vue';
import MyJobs from '../views/handyman/MyJobs.vue';
import JobDetail from '../views/handyman/JobDetail.vue';
import HandymanProfile from '../views/handyman/Profile.vue';

// ============================================
// ADMIN VIEWS
// ============================================
import AdminDashboard from '../views/admin/Dashboard.vue';
import UserManagement from '../views/admin/UserManagement.vue';
import HandymanManagement from '../views/admin/HandymanManagement.vue';
import ServiceCategoryManagement from '../views/admin/ServiceCategoryManagement.vue';
import ServiceRequestManagement from '../views/admin/ServiceRequestManagement.vue';
import PaymentManagement from '../views/admin/PaymentManagement.vue';
import Reports from '../views/admin/Reports.vue';
import ClientRegistrationManagement from '../views/admin/ClientRegistrationManagement.vue';

// ============================================
// SUPERADMIN VIEWS
// ============================================
import RoleManagement from '../views/superadmin/RoleManagement.vue';

// ============================================
// SHARED VIEWS
// ============================================
import Chat from '../views/shared/Chat.vue';
import Notifications from '../views/shared/Notifications.vue';
import Profile from '../views/shared/Profile.vue';

const routes = [
    // ============================================
    // RUTA PRINCIPAL - PORTAL PÚBLICO
    // ============================================
    {
        path: '/',
        name: 'Portal',
        component: Portal,
        meta: { requiresAuth: false }
    },

    // ============================================
    // REDIRECCIONES
    // ============================================
    {
        path: '/handyman/public',
        redirect: '/',
    },
    {
        path: '/handyman/public/',
        redirect: '/',
    },
    {
        path: '/public',
        redirect: '/',
    },
    {
        path: '/public/',
        redirect: '/',
    },

    // ============================================
    // RUTAS DE AUTENTICACIÓN
    // ============================================
    {
        path: '/login',
        component: GuestLayout,
        children: [
            {
                path: '',
                name: 'Login',
                component: Login,
            },
        ],
    },
    {
        path: '/register',
        component: GuestLayout,
        children: [
            {
                path: '',
                name: 'Register',
                component: Register,
            },
        ],
    },

    // ============================================
    // RUTAS PROTEGIDAS (DASHBOARD)
    // ============================================
    {
        path: '/dashboard',
        component: AuthLayout,
        meta: { requiresAuth: true },
        children: [
            // ------------------------
            // CLIENT ROUTES
            // ------------------------
            {
                path: 'client',
                name: 'ClientDashboard',
                component: ClientDashboard,
                meta: { role: 'client' },
            },
            {
                path: 'client/services',
                name: 'ServiceCategories',
                component: ServiceCategories,
                meta: { role: 'client' },
            },
            {
                path: 'client/request/create',
                name: 'CreateServiceRequest',
                component: CreateServiceRequest,
                meta: { role: 'client' },
            },
            {
                path: 'client/requests',
                name: 'MyRequests',
                component: MyRequests,
                meta: { role: 'client' },
            },
            {
                path: 'client/my-requests',
                name: 'ClientServiceRequests',
                component: ClientServiceRequests,
                meta: { role: 'client' },
            },
            {
                path: 'client/requests/:id',
                name: 'RequestDetail',
                component: RequestDetail,
                meta: { role: 'client' },
            },

            // ------------------------
            // HANDYMAN ROUTES
            // ------------------------
            {
                path: 'handyman',
                name: 'HandymanDashboard',
                component: HandymanDashboard,
                meta: { role: 'handyman' },
            },
            {
                path: 'handyman/available-jobs',
                name: 'AvailableJobs',
                component: AvailableJobs,
                meta: { role: 'handyman' },
            },
            {
                path: 'handyman/my-jobs',
                name: 'MyJobs',
                component: MyJobs,
                meta: { role: 'handyman' },
            },
            {
                path: 'handyman/jobs/:id',
                name: 'JobDetail',
                component: JobDetail,
                meta: { role: 'handyman' },
            },
            {
                path: 'handyman/profile',
                name: 'HandymanProfile',
                component: HandymanProfile,
                meta: { role: 'handyman' },
            },

            // ------------------------
            // ADMIN ROUTES
            // ------------------------
            {
                path: 'admin',
                name: 'AdminDashboard',
                component: AdminDashboard,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/users',
                name: 'UserManagement',
                component: UserManagement,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/handymen',
                name: 'HandymanManagement',
                component: HandymanManagement,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/service-categories',
                name: 'ServiceCategoryManagement',
                component: ServiceCategoryManagement,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/service-requests',
                name: 'ServiceRequestManagement',
                component: ServiceRequestManagement,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/payments',
                name: 'PaymentManagement',
                component: PaymentManagement,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/reports',
                name: 'Reports',
                component: Reports,
                meta: { role: ['admin', 'superadmin'] },
            },
            {
                path: 'admin/client-registrations',
                name: 'ClientRegistrationManagement',
                component: ClientRegistrationManagement,
                meta: { role: ['admin', 'superadmin'] },
            },

            // ------------------------
            // SUPERADMIN ROUTES
            // ------------------------
            {
                path: 'superadmin/roles',
                name: 'RoleManagement',
                component: RoleManagement,
                meta: { role: 'superadmin' },
            },

            // ------------------------
            // SHARED ROUTES
            // ------------------------
            {
                path: 'chat',
                name: 'Chat',
                component: Chat,
            },
            {
                path: 'notifications',
                name: 'Notifications',
                component: Notifications,
            },
            {
                path: 'profile',
                name: 'Profile',
                component: Profile,
            },
        ],
    },

    // ============================================
    // RUTA CREATE SERVICE REQUEST (FUERA DEL DASHBOARD)
    // ============================================
    {
        path: '/create-service-request',
        name: 'CreateServiceRequestStandalone',
        component: CreateServiceRequest,
        meta: { requiresAuth: true, role: 'client' }
    },

    // ============================================
    // CATCH ALL - REDIRECT TO PORTAL
    // ============================================
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory('/'),
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0 };
        }
    }
});

// ============================================
// NAVIGATION GUARDS
// ============================================
router.beforeEach((to, from, next) => {
    console.log('Navegando a:', to.path);
    
    const isAuthenticated = !!localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const userRole = user.roles?.[0]?.name;

    // ✅ SI EL USUARIO YA ESTÁ AUTENTICADO Y VA AL PORTAL, REDIRIGIR A SU DASHBOARD
    if (to.path === '/' && isAuthenticated) {
        switch (userRole) {
            case 'client':
                next('/dashboard/client');
                break;
            case 'handyman':
                next('/dashboard/handyman');
                break;
            case 'admin':
            case 'superadmin':
                next('/dashboard/admin');
                break;
            default:
                next();
        }
        return;
    }

    // ✅ RUTAS QUE REQUIEREN AUTENTICACIÓN
    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!isAuthenticated) {
            // ✅ SI NO ESTÁ AUTENTICADO, REDIRIGIR AL PORTAL (NO AL LOGIN)
            console.log('No autenticado, redirigiendo al portal');
            next('/');
        } else if (to.meta.role) {
            const allowedRoles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role];
            if (allowedRoles.includes(userRole)) {
                next();
            } else {
                console.log('Rol no permitido, redirigiendo a dashboard apropiado');
                switch (userRole) {
                    case 'client':
                        next('/dashboard/client');
                        break;
                    case 'handyman':
                        next('/dashboard/handyman');
                        break;
                    case 'admin':
                    case 'superadmin':
                        next('/dashboard/admin');
                        break;
                    default:
                        next('/');
                }
            }
        } else {
            next();
        }
    } else {
        // ✅ PARA RUTAS QUE NO REQUIEREN AUTENTICACIÓN
        if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
            console.log('Ya autenticado, redirigiendo a dashboard');
            switch (userRole) {
                case 'client':
                    next('/dashboard/client');
                    break;
                case 'handyman':
                    next('/dashboard/handyman');
                    break;
                case 'admin':
                case 'superadmin':
                    next('/dashboard/admin');
                    break;
                default:
                    next();
            }
        } else {
            next();
        }
    }
});

export default router;