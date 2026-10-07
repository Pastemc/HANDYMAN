<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Handyman Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Welcome Message -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Welcome back, {{ userName }}!</h5>
                        Here's an overview of your work and earnings
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ stats.available_jobs || 0 }}</h3>
                            <p>Available Jobs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <router-link to="/dashboard/handyman/available-jobs" class="small-box-footer">
                            View <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ stats.active_jobs || 0 }}</h3>
                            <p>Active Jobs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <router-link to="/dashboard/handyman/my-jobs" class="small-box-footer">
                            View <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ stats.completed_jobs || 0 }}</h3>
                            <p>Completed Jobs</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <router-link to="/dashboard/handyman/my-jobs" class="small-box-footer">
                            View <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>${{ stats.total_earnings || 0 }}</h3>
                            <p>Total Earnings</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            More Info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile and Jobs Section -->
            <div class="row">
                <!-- Profile Info -->
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="user-avatar-large mx-auto">
                                    {{ getInitials }}
                                </div>
                            </div>

                            <h3 class="profile-username text-center">{{ userName }}</h3>
                            <p class="text-muted text-center">Handyman</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Rating</b> 
                                    <span class="float-right">
                                        {{ stats.rating || 0 }} <i class="fas fa-star text-warning"></i>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Total Jobs</b> 
                                    <span class="float-right">{{ stats.total_jobs || 0 }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Years of Experience</b> 
                                    <span class="float-right">{{ stats.years_experience || 0 }}</span>
                                </li>
                            </ul>

                            <router-link to="/dashboard/handyman/profile" class="btn btn-primary btn-block">
                                <b>Professional Profile</b>
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Available Jobs -->
                <div class="col-lg-8 col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Available Jobs</h3>
                            <div class="card-tools">
                                <router-link to="/dashboard/handyman/available-jobs" class="btn btn-sm btn-primary">
                                    View All
                                </router-link>
                            </div>
                        </div>

                        <!-- Desktop/Tablet Table View -->
                        <div class="card-body table-responsive p-0 d-none d-md-block">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request #</th>
                                        <th>Category</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="job in availableJobs" :key="job.id">
                                        <td>{{ job.request_number }}</td>
                                        <td>{{ job.service_category?.name }}</td>
                                        <td>{{ job.client?.name }}</td>
                                        <td>{{ formatDate(job.preferred_date) }}</td>
                                        <td>
                                            <router-link 
                                                :to="`/dashboard/handyman/jobs/${job.id}`" 
                                                class="btn btn-sm btn-info"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr v-if="availableJobs.length === 0">
                                        <td colspan="5" class="text-center text-muted">No data available</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="card-body d-md-none">
                            <div v-if="availableJobs.length === 0" class="text-center py-4">
                                <p class="text-muted">No data available</p>
                            </div>
                            <div v-else class="jobs-cards">
                                <div v-for="job in availableJobs" :key="job.id" class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title font-weight-bold text-primary">{{ job.request_number }}</h6>
                                        <div class="mb-2">
                                            <small class="text-muted">Category:</small><br>
                                            <strong>{{ job.service_category?.name }}</strong>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted">Client:</small><br>
                                            <strong>{{ job.client?.name }}</strong>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted">Date:</small><br>
                                            <strong>{{ formatDate(job.preferred_date) }}</strong>
                                        </div>
                                        <router-link 
                                            :to="`/dashboard/handyman/jobs/${job.id}`" 
                                            class="btn btn-sm btn-info btn-block"
                                        >
                                            <i class="fas fa-eye"></i> View Details
                                        </router-link>
                                    </div>
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
import { ref, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';

export default {
    name: 'HandymanDashboard',
    setup() {
        const store = useStore();
        const stats = ref({});
        const availableJobs = ref([]);

        const userName = computed(() => store.getters['auth/user']?.name || 'User');
        
        const getInitials = computed(() => {
            const name = userName.value;
            return name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);
        });

        const fetchDashboardData = async () => {
            try {
                const response = await axios.get('/dashboard');
                stats.value = response.data.stats || {};
                availableJobs.value = response.data.available_jobs || [];
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };

        onMounted(() => {
            fetchDashboardData();
        });

        return {
            userName,
            getInitials,
            stats,
            availableJobs,
            formatDate,
        };
    },
};
</script>

<style scoped>
/* ============ BASE ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.content {
    padding: 1.5rem 0;
}

.container-fluid {
    padding: 0 1rem;
}

/* ============ BREADCRUMB ============ */
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
    list-style: none;
    display: flex;
}

.breadcrumb-item {
    display: inline-flex;
    align-items: center;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    padding: 0 0.5rem;
    color: #6c757d;
}

.breadcrumb-item a {
    color: #007bff;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #0056b3;
}

.breadcrumb-item.active {
    color: #6c757d;
}

.float-sm-right {
    float: right;
}

/* ============ ALERT ============ */
.alert {
    padding: 1rem 1.25rem;
    margin-bottom: 1.5rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
}

.alert-info {
    background: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert h5 {
    margin: 0 0 0.5rem 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert .icon {
    font-size: 1.25rem;
}

/* ============ SMALL BOXES ============ */
.small-box {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.small-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.small-box .inner {
    padding: 1.5rem;
    position: relative;
    z-index: 2;
}

.small-box .inner h3 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: white;
}

.small-box .inner p {
    font-size: 1rem;
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
}

.small-box .icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 5rem;
    color: rgba(0, 0, 0, 0.15);
    z-index: 1;
}

.small-box-footer {
    display: block;
    padding: 0.75rem;
    text-align: center;
    background: rgba(0, 0, 0, 0.1);
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: background 0.3s;
    font-size: 0.875rem;
}

.small-box-footer:hover {
    background: rgba(0, 0, 0, 0.2);
    color: white;
}

.bg-info {
    background: #17a2b8 !important;
}

.bg-warning {
    background: #ffc107 !important;
}

.bg-success {
    background: #28a745 !important;
}

.bg-danger {
    background: #dc3545 !important;
}

/* ============ CARD ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 1.5rem;
}

.card-primary.card-outline {
    border-top: 3px solid #007bff;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 0;
}

.card-tools {
    display: flex;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* ============ PROFILE BOX ============ */
.box-profile {
    text-align: center;
}

.user-avatar-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 40px;
    margin-bottom: 1rem;
}

.profile-username {
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
    margin: 1rem 0 0.5rem 0;
}

.text-muted {
    color: #6c757d;
}

.text-center {
    text-align: center;
}

.text-warning {
    color: #ffc107 !important;
}

.text-primary {
    color: #007bff !important;
}

/* ============ LIST GROUP ============ */
.list-group-unbordered {
    border-radius: 0;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
    padding: 0.75rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.list-group-item:first-child {
    border-top: 0;
}

.list-group-item b {
    font-weight: 600;
    color: #343a40;
}

.float-right {
    margin-left: auto;
}

/* ============ TABLE ============ */
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
}

.table tbody td {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    vertical-align: middle;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

/* ============ MOBILE CARDS ============ */
.jobs-cards {
    max-height: 500px;
    overflow-y: auto;
}

.jobs-cards::-webkit-scrollbar {
    width: 6px;
}

.jobs-cards::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.jobs-cards::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.jobs-cards .card {
    border: 1px solid #dee2e6;
    transition: transform 0.3s, box-shadow 0.3s;
}

.jobs-cards .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.jobs-cards .card-body {
    padding: 1rem;
}

.jobs-cards .card-title {
    font-size: 1rem;
    margin-bottom: 0.75rem;
}

/* ============ BUTTONS ============ */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-primary {
    background: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    border-color: #0056b3;
}

.btn-info {
    background: #17a2b8;
    border-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #138496;
    border-color: #138496;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-block {
    display: flex;
    width: 100%;
}

/* ============ UTILITIES ============ */
.mb-0 {
    margin-bottom: 0 !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.font-weight-bold {
    font-weight: 700;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .small-box .inner h3 {
        font-size: 2rem;
    }

    .small-box .icon {
        font-size: 4rem;
    }

    .user-avatar-large {
        width: 90px;
        height: 90px;
        font-size: 36px;
    }

    .profile-username {
        font-size: 1.375rem;
    }
}

/* ============ RESPONSIVE - MOBILE (< 768px) ============ */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0;
    }

    .content {
        padding: 1rem 0;
    }

    .float-sm-right {
        float: none !important;
        margin-top: 0.5rem;
    }

    .breadcrumb {
        justify-content: flex-start;
    }

    .alert h5 {
        font-size: 1rem;
    }

    .small-box .inner {
        padding: 1rem;
    }

    .small-box .inner h3 {
        font-size: 1.75rem;
    }

    .small-box .inner p {
        font-size: 0.875rem;
    }

    .small-box .icon {
        font-size: 3rem;
        top: 0.75rem;
        right: 0.75rem;
    }

    .card-header {
        padding: 1rem;
        flex-direction: column;
        align-items: flex-start;
    }

    .card-tools {
        width: 100%;
        margin-top: 0.5rem;
    }

    .card-tools .btn {
        width: 100%;
    }

    .card-body {
        padding: 1rem;
    }

    .user-avatar-large {
        width: 80px;
        height: 80px;
        font-size: 32px;
    }

    .profile-username {
        font-size: 1.25rem;
    }

    .list-group-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }

    .float-right {
        margin-left: 0;
    }

    .jobs-cards {
        max-height: 400px;
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0 0.75rem;
    }

    .content-header h1 {
        font-size: 1.5rem;
    }

    .alert {
        padding: 0.75rem 1rem;
    }

    .alert h5 {
        font-size: 0.9375rem;
    }

    .small-box .inner h3 {
        font-size: 1.5rem;
    }

    .small-box .icon {
        font-size: 2.5rem;
    }

    .card-header {
        padding: 0.75rem;
    }

    .card-title {
        font-size: 1.125rem;
    }

    .card-body {
        padding: 0.75rem;
    }

    .user-avatar-large {
        width: 70px;
        height: 70px;
        font-size: 28px;
    }

    .profile-username {
        font-size: 1.125rem;
    }

    .list-group-item {
        padding: 0.5rem 0;
        font-size: 0.9375rem;
    }

    .jobs-cards .card-body {
        padding: 0.75rem;
    }

    .jobs-cards .card-title {
        font-size: 0.9375rem;
    }

    .btn-sm {
        font-size: 0.8125rem;
    }
}

/* ============ PRINT STYLES ============ */
@media print {
    .small-box-footer,
    .btn,
    .card-tools {
        display: none !important;
    }

    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>