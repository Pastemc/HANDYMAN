<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Available Jobs</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <span class="badge badge-success mr-2" v-if="isConnected">
                            <i class="fas fa-circle pulse-animation"></i> Live
                        </span>
                        <button @click="fetchJobs" class="btn btn-sm btn-primary" :disabled="loading">
                            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchJobs"
                                type="text" 
                                class="form-control" 
                                placeholder="Search..."
                            >
                        </div>
                        <div class="col-12 col-md-2">
                            <button @click="fetchJobs" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Loading -->
                    <div v-if="loading && jobs.length === 0" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading available jobs...</p>
                    </div>

                    <!-- Jobs Grid -->
                    <div v-else class="row">
                        <div class="col-lg-6 col-md-12 mb-4" v-for="job in jobs" :key="job.id">
                            <div class="card card-outline card-primary job-card h-100">
                                <div class="card-header">
                                    <h3 class="card-title font-weight-bold">{{ job.title }}</h3>
                                    <div class="card-tools">
                                        <span class="badge badge-warning">Pending</span>
                                        <span v-if="job.priority === 'urgent'" class="badge badge-danger ml-1">
                                            <i class="fas fa-exclamation-circle"></i> Urgent
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-5">Category:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge badge-info">{{ job.service_category?.name }}</span>
                                        </dd>

                                        <dt class="col-sm-5">Client:</dt>
                                        <dd class="col-sm-7">
                                            <i class="fas fa-user"></i> {{ job.client?.name }}
                                        </dd>

                                        <dt class="col-sm-5">Location:</dt>
                                        <dd class="col-sm-7">
                                            <i class="fas fa-map-marker-alt"></i> {{ job.city }}, {{ job.state }}
                                        </dd>

                                        <dt class="col-sm-5">Date:</dt>
                                        <dd class="col-sm-7">
                                            <i class="fas fa-calendar"></i> {{ formatDate(job.preferred_date) }}
                                        </dd>

                                        <dt class="col-sm-5">Time:</dt>
                                        <dd class="col-sm-7">
                                            <i class="fas fa-clock"></i> {{ job.preferred_time }}
                                        </dd>
                                    </dl>

                                    <hr>

                                    <div class="description-box">
                                        <strong>Description:</strong>
                                        <p class="text-muted mb-0">
                                            {{ job.description.length > 150 ? job.description.substring(0, 150) + '...' : job.description }}
                                        </p>
                                    </div>

                                    <!-- Job Photos -->
                                    <div v-if="job.photos_urls && job.photos_urls.length > 0" class="mt-3">
                                        <strong>Photos:</strong>
                                        <div class="d-flex flex-wrap mt-2">
                                            <img 
                                                v-for="(photo, index) in job.photos_urls.slice(0, 3)" 
                                                :key="index"
                                                :src="'http://127.0.0.1:8000' + photo" 
                                                class="img-thumbnail mr-2 mb-2"
                                                style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                                @click="viewPhotos(job)"
                                            >
                                            <span v-if="job.photos_urls.length > 3" class="badge badge-info align-self-center">
                                                +{{ job.photos_urls.length - 3 }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <router-link 
                                        :to="`/dashboard/handyman/jobs/${job.id}`"
                                        class="btn btn-info"
                                    >
                                        <i class="fas fa-eye"></i> View
                                    </router-link>
                                    <button 
                                        @click="acceptJob(job.id)"
                                        class="btn btn-success ml-2"
                                    >
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- No Data -->
                    <div v-if="jobs.length === 0 && !loading" class="text-center py-5">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <p class="mb-0">No data available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photos Modal -->
    <div v-if="showPhotosModal" class="modal fade show modal-backdrop-custom" @click.self="closePhotosModal">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h4 class="modal-title text-white">
                        <i class="fas fa-images"></i> Job Photos
                    </h4>
                    <button type="button" class="close text-white" @click="closePhotosModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedJobPhotos && selectedJobPhotos.length > 0">
                        <div id="jobPhotosCarousel" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li 
                                    v-for="(photo, index) in selectedJobPhotos" 
                                    :key="index"
                                    data-target="#jobPhotosCarousel" 
                                    :data-slide-to="index"
                                    :class="{ active: index === 0 }"
                                ></li>
                            </ol>
                            <div class="carousel-inner">
                                <div 
                                    v-for="(photo, index) in selectedJobPhotos" 
                                    :key="index"
                                    class="carousel-item"
                                    :class="{ active: index === 0 }"
                                >
                                    <img 
                                        :src="'http://127.0.0.1:8000' + photo" 
                                        class="d-block w-100 carousel-photo"
                                        :alt="`Photo ${index + 1}`"
                                    >
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#jobPhotosCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </a>
                            <a class="carousel-control-next" href="#jobPhotosCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" @click="closePhotosModal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'AvailableJobs',
    setup() {
        const router = useRouter();
        const store = useStore();
        const jobs = ref([]);
        const loading = ref(false);
        const isConnected = ref(false);
        const showPhotosModal = ref(false);
        const selectedJobPhotos = ref([]);

        const currentUser = computed(() => store.state.auth.user);

        const filters = reactive({
            search: '',
            status: 'pending',
        });

        const fetchJobs = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/service-requests', { 
                    params: { 
                        ...filters,
                        handyman_id: 'null'
                    } 
                });
                jobs.value = response.data.data;
            } catch (error) {
                console.error('Error fetching jobs:', error);
            } finally {
                loading.value = false;
            }
        };

        const setupRealtimeListeners = () => {
            if (!window.Echo) {
                console.warn('Laravel Echo not initialized');
                return;
            }

            window.Echo.channel('service-requests')
                .listen('.service-request.created', (e) => {
                    console.log('New request created:', e.serviceRequest);
                    
                    if (e.serviceRequest && !e.serviceRequest.handyman_id) {
                        jobs.value.unshift(e.serviceRequest);
                        
                        if (Notification.permission === 'granted') {
                            new Notification('New Job Available', {
                                body: e.serviceRequest.title,
                                icon: '/favicon.ico'
                            });
                        }

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'New job available',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                        });
                    }
                })
                .listen('.service-request.updated', (e) => {
                    console.log('Request updated:', e.serviceRequest);
                    
                    if (e.serviceRequest.handyman_id) {
                        const index = jobs.value.findIndex(j => j.id === e.serviceRequest.id);
                        if (index !== -1) {
                            jobs.value.splice(index, 1);
                        }
                    } else {
                        const index = jobs.value.findIndex(j => j.id === e.serviceRequest.id);
                        if (index !== -1) {
                            jobs.value[index] = e.serviceRequest;
                        }
                    }
                })
                .listen('.service-request.deleted', (e) => {
                    console.log('Request deleted:', e.serviceRequestId);
                    
                    const index = jobs.value.findIndex(j => j.id === e.serviceRequestId);
                    if (index !== -1) {
                        jobs.value.splice(index, 1);
                    }
                });

            isConnected.value = true;
            console.log('Listening to real-time events...');
        };

        const acceptJob = async (id) => {
            const { value: estimatedCost } = await Swal.fire({
                title: 'Accept Job',
                input: 'number',
                inputLabel: 'Estimated Cost ($)',
                inputPlaceholder: 'Enter estimated cost',
                showCancelButton: true,
                confirmButtonText: 'Accept',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value || value <= 0) {
                        return 'Please enter a valid cost';
                    }
                }
            });

            if (estimatedCost) {
                try {
                    await axios.post(`/service-requests/${id}/accept`, {
                        estimated_cost: estimatedCost
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Job accepted successfully',
                        timer: 2000,
                    });
                    
                    const index = jobs.value.findIndex(j => j.id === id);
                    if (index !== -1) {
                        jobs.value.splice(index, 1);
                    }
                    
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to accept job', 'error');
                }
            }
        };

        const viewPhotos = (job) => {
            selectedJobPhotos.value = job.photos_urls || [];
            showPhotosModal.value = true;

            setTimeout(() => {
                window.$('#jobPhotosCarousel').carousel({
                    interval: false
                });
            }, 100);
        };

        const closePhotosModal = () => {
            showPhotosModal.value = false;
            selectedJobPhotos.value = [];
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };

        const requestNotificationPermission = () => {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        };

        onMounted(() => {
            fetchJobs();
            setupRealtimeListeners();
            requestNotificationPermission();
        });

        onUnmounted(() => {
            if (window.Echo) {
                window.Echo.leave('service-requests');
            }
        });

        return {
            jobs,
            loading,
            filters,
            isConnected,
            showPhotosModal,
            selectedJobPhotos,
            fetchJobs,
            acceptJob,
            viewPhotos,
            closePhotosModal,
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
    padding: 1.5rem;
}

/* ============ JOB CARDS ============ */
.job-card {
    transition: all 0.3s ease;
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.job-card .card-header {
    background: white;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.job-card .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #007bff;
    margin: 0;
    flex: 1;
}

.job-card .card-tools {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.job-card .card-body {
    padding: 1.25rem;
}

.job-card .card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* ============ DESCRIPTION BOX ============ */
.description-box {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 3px solid #007bff;
}

.description-box strong {
    display: block;
    margin-bottom: 0.5rem;
    color: #343a40;
}

.description-box p {
    margin: 0;
    line-height: 1.6;
}

/* ============ DEFINITION LIST ============ */
dl.row {
    margin-bottom: 0;
}

dt {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

dd {
    color: #343a40;
    margin-bottom: 0.5rem;
}

/* ============ BADGES ============ */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-warning {
    background: #ffc107;
    color: #212529;
}

.badge-danger {
    background: #dc3545;
    color: white;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-success {
    background: #28a745;
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

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
    border-color: #0056b3;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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

.btn-success {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
    border-color: #218838;
}

.btn-secondary {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    border-color: #5a6268;
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

/* ============ IMAGES ============ */
.img-thumbnail {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    transition: transform 0.3s, box-shadow 0.3s;
}

.img-thumbnail:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* ============ ALERT ============ */
.alert {
    padding: 1.25rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
}

.alert-info {
    background: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
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

/* ============ PULSE ANIMATION ============ */
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

/* ============ MODAL ============ */
.modal-backdrop-custom {
    display: block !important;
    background: rgba(0, 0, 0, 0.9);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    overflow-y: auto;
    padding: 1rem;
}

.modal-dialog {
    margin: 1.75rem auto;
    max-width: 90%;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-dialog-xl {
    max-width: 1140px;
}

.modal-content {
    border-radius: 0.5rem;
    border: none;
}

.modal-content.bg-dark {
    background: #343a40 !important;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header.border-0 {
    border-bottom: none;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-footer.border-0 {
    border-top: none;
}

.close {
    background: transparent;
    border: none;
    font-size: 2rem;
    font-weight: 300;
    line-height: 1;
    cursor: pointer;
    opacity: 0.8;
}

.close:hover {
    opacity: 1;
}

/* ============ CAROUSEL ============ */
.carousel-photo {
    max-height: 500px;
    object-fit: contain;
    background: #000;
}

.carousel-indicators {
    margin-bottom: 0;
}

.carousel-control-prev,
.carousel-control-next {
    width: 10%;
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d !important;
}

.text-white {
    color: white !important;
}

.float-right {
    float: right;
}

.h-100 {
    height: 100%;
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

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.mt-2 {
    margin-top: 0.5rem !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

.ml-1 {
    margin-left: 0.25rem !important;
}

.ml-2 {
    margin-left: 0.5rem !important;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.font-weight-bold {
    font-weight: 700;
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .job-card .card-title {
        font-size: 1rem;
    }

    dt, dd {
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

    .float-right {
        float: none !important;
        display: flex;
        justify-content: flex-start;
        margin-top: 0.5rem;
    }

    .card-header {
        padding: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .job-card .card-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .job-card .card-tools {
        width: 100%;
        justify-content: flex-start;
        margin-top: 0.5rem;
    }

    .job-card .card-body {
        padding: 1rem;
    }

    .job-card .card-footer {
        flex-direction: column;
        padding: 0.75rem;
    }

    .job-card .card-footer .btn {
        width: 100%;
        margin-left: 0 !important;
        margin-bottom: 0.5rem;
    }

    .job-card .card-footer .btn:last-child {
        margin-bottom: 0;
    }

    dt, dd {
        padding: 0.25rem 0;
    }

    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }

    .carousel-photo {
        max-height: 300px;
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

    .badge {
        font-size: 0.8125rem;
        padding: 0.25rem 0.5rem;
    }

    .btn {
        font-size: 0.9375rem;
        padding: 0.5rem 0.75rem;
    }

    .btn-sm {
        font-size: 0.8125rem;
        padding: 0.375rem 0.5rem;
    }

    .job-card .card-title {
        font-size: 0.9375rem;
    }

    .description-box {
        padding: 0.75rem;
    }

    dt, dd {
        font-size: 0.875rem;
    }

    .img-thumbnail {
        width: 60px !important;
        height: 60px !important;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 0.75rem 1rem;
    }
}
</style>