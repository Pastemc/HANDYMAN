<template>
    <div class="admin-dashboard">
        <div class="content-header">
            <div class="container-fluid">
                <div class="header-wrapper">
                    <div class="header-left">
                        <h1 class="dashboard-title">Administrative Dashboard</h1>
                    </div>
                    <div class="header-right">
                        <span class="badge badge-success live-badge" v-if="isConnected">
                            <i class="fas fa-circle pulse-animation"></i> Live
                        </span>
                        <button @click="fetchDashboardData" class="btn btn-refresh" :disabled="loading">
                            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Loading State -->
                <div v-if="loading && !stats.total_users" class="loading-state">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="loading-text">Loading dashboard data...</p>
                </div>

                <!-- Dashboard Content -->
                <div v-else class="dashboard-content">
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card stat-card-info">
                            <div class="stat-inner">
                                <h3 class="stat-number">{{ stats.total_users || 0 }}</h3>
                                <p class="stat-label">Total Users</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <router-link to="/dashboard/admin/users" class="stat-footer">
                                View More <i class="fas fa-arrow-circle-right"></i>
                            </router-link>
                        </div>

                        <div class="stat-card stat-card-success">
                            <div class="stat-inner">
                                <h3 class="stat-number">{{ stats.registered_handymen || 0 }}</h3>
                                <p class="stat-label">Registered Handymen</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-hard-hat"></i>
                            </div>
                            <router-link to="/dashboard/admin/handymen" class="stat-footer">
                                View More <i class="fas fa-arrow-circle-right"></i>
                            </router-link>
                        </div>

                        <div class="stat-card stat-card-warning">
                            <div class="stat-inner">
                                <h3 class="stat-number">{{ stats.pending_handymen || 0 }}</h3>
                                <p class="stat-label">Pending Approval</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-user-clock"></i>
                            </div>
                            <a href="#" @click.prevent="goToApprove" class="stat-footer">
                                Approve <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>

                        <div class="stat-card stat-card-danger">
                            <div class="stat-inner">
                                <h3 class="stat-number">${{ formatNumber(stats.platform_revenue) }}</h3>
                                <p class="stat-label">Platform Revenue</p>
                            </div>
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <router-link to="/dashboard/admin/payments" class="stat-footer">
                                View More <i class="fas fa-arrow-circle-right"></i>
                            </router-link>
                        </div>
                    </div>

                    <!-- Secondary Stats -->
                    <div class="info-boxes-grid">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-clipboard-list"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Requests</span>
                                <span class="info-box-number">{{ stats.total_requests || 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-warning">
                                <i class="fas fa-hourglass-half"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Requests</span>
                                <span class="info-box-number">{{ stats.pending_requests || 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Completed Requests</span>
                                <span class="info-box-number">{{ stats.completed_requests || 0 }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-box-icon bg-primary">
                                <i class="fas fa-money-bill-wave"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Transactions</span>
                                <span class="info-box-number">${{ formatNumber(stats.total_transactions) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tables Row -->
                    <div class="tables-grid">
                        <!-- Top Handymen -->
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3 class="card-title">Top Handymen by Rating</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Handyman</th>
                                                <th>Total Jobs</th>
                                                <th>Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="handyman in topHandymen" :key="handyman.id">
                                                <td>{{ handyman.user?.name || 'N/A' }}</td>
                                                <td>{{ handyman.total_jobs }}</td>
                                                <td>
                                                    <span class="badge badge-warning">
                                                        {{ handyman.rating }} <i class="fas fa-star"></i>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr v-if="topHandymen.length === 0">
                                                <td colspan="3" class="text-center text-muted">No data available</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Requests -->
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Requests</h3>
                                <div class="card-tools">
                                    <router-link to="/dashboard/admin/service-requests" class="btn btn-sm btn-primary">
                                        View All
                                    </router-link>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Request #</th>
                                                <th>Client</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="request in recentRequests" :key="request.id">
                                                <td>{{ request.request_number }}</td>
                                                <td>{{ request.client?.name || 'N/A' }}</td>
                                                <td>
                                                    <span class="badge" :class="getStatusBadge(request.status)">
                                                        {{ getStatusText(request.status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr v-if="recentRequests.length === 0">
                                                <td colspan="3" class="text-center text-muted">No data available</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
    name: 'AdminDashboard',
    setup() {
        const router = useRouter();
        const loading = ref(false);
        const isConnected = ref(false);
        const stats = ref({});
        const topHandymen = ref([]);
        const recentRequests = ref([]);

        const fetchDashboardData = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/dashboard');
                stats.value = response.data.stats || {};
                topHandymen.value = response.data.top_handymen || [];
                recentRequests.value = response.data.recent_requests || [];
                
                console.log('Dashboard data loaded:', response.data);
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                loading.value = false;
            }
        };

        const setupRealtimeListeners = () => {
            if (!window.Echo) {
                console.warn('Laravel Echo not initialized');
                return;
            }

            window.Echo.channel('users')
                .listen('.user.created', (e) => {
                    console.log('New user created:', e.user);
                    stats.value.total_users = (stats.value.total_users || 0) + 1;
                    
                    if (e.user.roles && e.user.roles.some(r => r.name === 'handyman')) {
                        stats.value.registered_handymen = (stats.value.registered_handymen || 0) + 1;
                        stats.value.pending_handymen = (stats.value.pending_handymen || 0) + 1;
                    }
                });

            window.Echo.channel('service-requests')
                .listen('.service-request.created', (e) => {
                    console.log('New request created:', e.serviceRequest);
                    stats.value.total_requests = (stats.value.total_requests || 0) + 1;
                    stats.value.pending_requests = (stats.value.pending_requests || 0) + 1;
                    
                    recentRequests.value.unshift(e.serviceRequest);
                    if (recentRequests.value.length > 5) {
                        recentRequests.value.pop();
                    }
                })
                .listen('.service-request.updated', (e) => {
                    console.log('Request updated:', e.serviceRequest);
                    
                    const index = recentRequests.value.findIndex(r => r.id === e.serviceRequest.id);
                    if (index !== -1) {
                        recentRequests.value[index] = e.serviceRequest;
                    }
                    
                    fetchDashboardData();
                });

            window.Echo.channel('payments')
                .listen('.payment.created', (e) => {
                    console.log('New payment registered:', e.payment);
                    
                    const amount = parseFloat(e.payment.amount || 0);
                    stats.value.total_transactions = parseFloat(stats.value.total_transactions || 0) + amount;
                    stats.value.platform_revenue = parseFloat(stats.value.platform_revenue || 0) + (amount * 0.15);
                });

            window.Echo.channel('reviews')
                .listen('.review.created', (e) => {
                    console.log('New review:', e.review);
                    
                    const handymanIndex = topHandymen.value.findIndex(h => h.user_id === e.review.handyman_id);
                    if (handymanIndex !== -1) {
                        fetchDashboardData();
                    }
                });

            isConnected.value = true;
            console.log('Dashboard listening to real-time events...');
        };

        const goToApprove = () => {
            router.push('/dashboard/admin/handymen');
        };

        const getStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                accepted: 'badge-info',
                in_progress: 'badge-primary',
                completed: 'badge-success',
                cancelled: 'badge-danger',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                pending: 'Pending',
                accepted: 'Accepted',
                in_progress: 'In Progress',
                completed: 'Completed',
                cancelled: 'Cancelled',
            };
            return texts[status] || status;
        };

        const formatNumber = (value) => {
            if (!value) return '0.00';
            return parseFloat(value).toFixed(2);
        };

        onMounted(() => {
            fetchDashboardData();
            setupRealtimeListeners();
        });

        onUnmounted(() => {
            if (window.Echo) {
                window.Echo.leave('users');
                window.Echo.leave('service-requests');
                window.Echo.leave('payments');
                window.Echo.leave('reviews');
            }
        });

        return {
            loading,
            isConnected,
            stats,
            topHandymen,
            recentRequests,
            fetchDashboardData,
            goToApprove,
            getStatusBadge,
            getStatusText,
            formatNumber,
        };
    },
};
</script>

<style scoped>
/* ============ BASE ============ */
.admin-dashboard {
    min-height: 100vh;
    background: #f4f6f9;
}

* {
    box-sizing: border-box;
}

/* ============ HEADER ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    flex: 1;
    min-width: 0;
}

.dashboard-title {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 700;
    color: #343a40;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.live-badge {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-refresh {
    padding: 0.5rem 1rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-refresh:hover:not(:disabled) {
    background: #0056b3;
}

.btn-refresh:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pulse-animation {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

/* ============ CONTENT ============ */
.content {
    padding: 1.5rem 0;
}

.container-fluid {
    padding: 0 1rem;
    max-width: 100%;
}

.loading-state {
    text-align: center;
    padding: 4rem 1rem;
}

.loading-text {
    margin-top: 1rem;
    color: #6c757d;
    font-size: 1rem;
}

.dashboard-content {
    width: 100%;
}

/* ============ STATS GRID ============ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-card-info {
    border-left: 5px solid #17a2b8;
}

.stat-card-success {
    border-left: 5px solid #28a745;
}

.stat-card-warning {
    border-left: 5px solid #ffc107;
}

.stat-card-danger {
    border-left: 5px solid #dc3545;
}

.stat-inner {
    position: relative;
    z-index: 2;
}

.stat-number {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    color: #343a40;
    margin: 0 0 0.5rem 0;
}

.stat-label {
    font-size: clamp(0.875rem, 1.5vw, 1rem);
    color: #6c757d;
    margin: 0;
}

.stat-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 4rem;
    opacity: 0.15;
}

.stat-footer {
    display: block;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.3s;
}

.stat-footer:hover {
    color: #007bff;
}

/* ============ INFO BOXES ============ */
.info-boxes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-box {
    background: white;
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.info-box-icon {
    width: 70px;
    height: 70px;
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
}

.info-box-icon.bg-info {
    background: #17a2b8;
}

.info-box-icon.bg-warning {
    background: #ffc107;
}

.info-box-icon.bg-success {
    background: #28a745;
}

.info-box-icon.bg-primary {
    background: #007bff;
}

.info-box-content {
    margin-left: 1rem;
    flex: 1;
    min-width: 0;
}

.info-box-text {
    display: block;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
    color: #6c757d;
    margin-bottom: 0.25rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.info-box-number {
    display: block;
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 700;
    color: #343a40;
}

/* ============ TABLES ============ */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 450px), 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.dashboard-card {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card-title {
    font-size: clamp(1rem, 2vw, 1.25rem);
    font-weight: 600;
    color: #343a40;
    margin: 0;
}

.card-tools {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.card-body {
    padding: 0;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.table thead th {
    background: #f8f9fa;
    padding: 1rem;
    font-weight: 600;
    color: #343a40;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-warning {
    background: #ffc107;
    color: #212529;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d;
}

/* ============ BUTTONS ============ */
.btn {
    display: inline-block;
    font-weight: 400;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    border-radius: 0.25rem;
    transition: all 0.3s;
    cursor: pointer;
    text-decoration: none;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

/* ============ SPINNER ============ */
.spinner-border {
    width: 3rem;
    height: 3rem;
    border: 0.25rem solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .info-boxes-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* ============ RESPONSIVE - MOBILE (< 768px) ============ */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0;
    }

    .header-wrapper {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        width: 100%;
        justify-content: space-between;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .info-boxes-grid {
        grid-template-columns: 1fr;
    }

    .tables-grid {
        grid-template-columns: 1fr;
    }

    .info-box {
        flex-direction: row;
        text-align: left;
    }

    .info-box-icon {
        margin-bottom: 0;
        margin-right: 1rem;
    }

    .info-box-content {
        margin-left: 0;
    }

    .info-box-text {
        white-space: normal;
    }

    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .card-tools {
        width: 100%;
    }

    .btn-sm {
        width: 100%;
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        font-size: 3rem;
    }

    .table thead th,
    .table tbody td {
        padding: 0.75rem 0.5rem;
    }

    .card-header {
        padding: 0.75rem 1rem;
    }

    .live-badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }

    .btn-refresh {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .info-box {
        flex-direction: column;
        text-align: center;
    }

    .info-box-icon {
        margin-right: 0;
        margin-bottom: 0.75rem;
    }

    .info-box-text {
        white-space: normal;
    }

    .stat-number {
        font-size: 1.75rem;
    }

    .info-box-number {
        font-size: 1.5rem;
    }
}

/* ============ SCROLL FIX ============ */
@media (max-width: 991px) {
    .admin-dashboard {
        overflow-x: hidden;
    }

    .table-responsive {
        margin: 0;
    }

    .table {
        min-width: 500px;
    }
}

/* ============ CUSTOM SCROLLBAR ============ */
.table-responsive::-webkit-scrollbar {
    height: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>