<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">My Jobs</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchJobs"
                                type="text" 
                                class="form-control" 
                                placeholder="Search..."
                            >
                        </div>
                        <div class="col-12 col-md-2 mb-2 mb-md-0">
                            <select v-model="filters.status" @change="fetchJobs" class="form-control">
                                <option value="">All Status</option>
                                <option value="accepted">Accepted</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button @click="fetchJobs" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="card-body table-responsive p-0 d-none d-lg-block">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Category</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Estimated Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="job in jobs" :key="job.id">
                                <td>{{ job.request_number }}</td>
                                <td>{{ job.service_category?.name }}</td>
                                <td>{{ job.client?.name }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(job.status)">
                                        {{ getStatusText(job.status) }}
                                    </span>
                                </td>
                                <td>{{ formatDate(job.preferred_date) }}</td>
                                <td>${{ job.estimated_cost || '0.00' }}</td>
                                <td>
                                    <router-link 
                                        :to="`/dashboard/handyman/jobs/${job.id}`"
                                        class="btn btn-sm btn-info"
                                        title="View"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </router-link>
                                </td>
                            </tr>
                            <tr v-if="jobs.length === 0 && !loading">
                                <td colspan="7" class="text-center">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tablet Table View -->
                <div class="card-body table-responsive p-0 d-none d-md-block d-lg-none">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Request #</th>
                                <th>Client</th>
                                <th>Status</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="job in jobs" :key="job.id">
                                <td>{{ job.request_number }}</td>
                                <td>{{ job.client?.name }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(job.status)">
                                        {{ getStatusText(job.status) }}
                                    </span>
                                </td>
                                <td>${{ job.estimated_cost || '0.00' }}</td>
                                <td>
                                    <router-link 
                                        :to="`/dashboard/handyman/jobs/${job.id}`"
                                        class="btn btn-sm btn-info"
                                        title="View"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </router-link>
                                </td>
                            </tr>
                            <tr v-if="jobs.length === 0 && !loading">
                                <td colspan="5" class="text-center">No data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="card-body d-md-none">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="jobs.length === 0" class="text-center py-4">
                        <p class="text-muted">No data</p>
                    </div>
                    <div v-else class="jobs-cards">
                        <div v-for="job in jobs" :key="job.id" class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0 font-weight-bold">{{ job.request_number }}</h6>
                                    <span class="badge" :class="getStatusBadge(job.status)">
                                        {{ getStatusText(job.status) }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Category:</small><br>
                                    <strong>{{ job.service_category?.name || 'N/A' }}</strong>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Client:</small><br>
                                    <strong>{{ job.client?.name }}</strong>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <small class="text-muted">Date:</small><br>
                                        <strong>{{ formatDate(job.preferred_date) }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Cost:</small><br>
                                        <strong>${{ job.estimated_cost || '0.00' }}</strong>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <router-link 
                                        :to="`/dashboard/handyman/jobs/${job.id}`"
                                        class="btn btn-sm btn-info"
                                    >
                                        <i class="fas fa-eye"></i> View Details
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right" v-if="pagination.last_page > 1">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">«</a>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">{{ pagination.current_page }}</span>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">»</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

export default {
    name: 'MyJobs',
    setup() {
        const jobs = ref([]);
        const loading = ref(false);

        const filters = reactive({
            search: '',
            status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const fetchJobs = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/service-requests', { params: filters });
                jobs.value = response.data.data;
                pagination.value = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                };
            } catch (error) {
                console.error('Error fetching jobs:', error);
            } finally {
                loading.value = false;
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchJobs();
            }
        };

        const getStatusBadge = (status) => {
            const badges = {
                accepted: 'badge-info',
                in_progress: 'badge-primary',
                completed: 'badge-success',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                accepted: 'Accepted',
                in_progress: 'In Progress',
                completed: 'Completed',
            };
            return texts[status] || status;
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
            fetchJobs();
        });

        return {
            jobs,
            loading,
            filters,
            pagination,
            fetchJobs,
            changePage,
            getStatusBadge,
            getStatusText,
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

/* ============ CARD ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 0;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 0.75rem 1.5rem;
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

/* ============ BADGES ============ */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
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

.badge-secondary {
    background: #6c757d;
    color: white;
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

/* ============ FORM CONTROLS ============ */
.form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* ============ MOBILE CARDS ============ */
.jobs-cards {
    padding: 1rem;
    max-height: calc(100vh - 350px);
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
    color: #007bff;
}

/* ============ PAGINATION ============ */
.pagination {
    margin: 0;
}

.page-item {
    display: inline-block;
}

.page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    color: #007bff;
    background: white;
    border: 1px solid #dee2e6;
    text-decoration: none;
}

.page-item.active .page-link {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-link:hover {
    background: #f8f9fa;
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d;
}

.float-right {
    float: right;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.font-weight-bold {
    font-weight: 700;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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
    .table thead th,
    .table tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.9375rem;
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

    .card-header {
        padding: 1rem;
    }

    .card-footer {
        padding: 0.75rem 1rem;
    }

    .jobs-cards {
        padding: 0.75rem;
        max-height: calc(100vh - 300px);
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

    .card-header {
        padding: 0.75rem;
    }

    .btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .form-control {
        font-size: 0.9375rem;
    }

    .jobs-cards {
        padding: 0.5rem;
    }

    .jobs-cards .card-body {
        padding: 0.75rem;
    }

    .jobs-cards .card-title {
        font-size: 0.9375rem;
    }

    .badge {
        font-size: 0.8125rem;
        padding: 0.25rem 0.5rem;
    }

    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.8125rem;
    }
}
</style>