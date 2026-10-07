<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Job Details</h1>
                </div>
                <div class="col-sm-6">
                    <router-link to="/dashboard/handyman/my-jobs" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> <span class="d-none d-sm-inline">Back</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Loading State -->
            <div v-if="!job" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="text-muted mt-3">Loading job details...</p>
            </div>

            <!-- Job Details Card -->
            <div class="card" v-else>
                <div class="card-header">
                    <div class="header-content">
                        <h3 class="card-title">{{ job.request_number }} - {{ job.title }}</h3>
                        <span class="badge" :class="getStatusBadge(job.status)">
                            {{ getStatusText(job.status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Service and Cost Info -->
                    <div class="row">
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h5 class="section-title">Service Information</h5>
                            <dl class="info-list">
                                <div class="info-item">
                                    <dt>Category:</dt>
                                    <dd>{{ job.service_category?.name }}</dd>
                                </div>
                                <div class="info-item">
                                    <dt>Client:</dt>
                                    <dd>{{ job.client?.name }}</dd>
                                </div>
                                <div class="info-item">
                                    <dt>Date:</dt>
                                    <dd>{{ formatDate(job.preferred_date) }}</dd>
                                </div>
                                <div class="info-item">
                                    <dt>Time:</dt>
                                    <dd>{{ job.preferred_time }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="col-lg-6">
                            <h5 class="section-title">Cost Information</h5>
                            <dl class="info-list">
                                <div class="info-item">
                                    <dt>Estimated Cost:</dt>
                                    <dd>${{ job.estimated_cost || '0.00' }}</dd>
                                </div>
                                <div class="info-item">
                                    <dt>Final Cost:</dt>
                                    <dd>
                                        <strong v-if="job.final_cost">${{ job.final_cost }}</strong>
                                        <span v-else class="text-muted">Pending</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Description -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="section-title">Description</h5>
                            <p class="description-text">{{ job.description }}</p>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="section-title">Location</h5>
                            <p class="location-text">
                                {{ job.address }}<br>
                                {{ job.city }}, {{ job.state }} {{ job.zip_code }}
                            </p>
                        </div>
                    </div>

                    <!-- Client Notes -->
                    <div class="row" v-if="job.client_notes">
                        <div class="col-12">
                            <h5 class="section-title">Client Notes</h5>
                            <p class="notes-text">{{ job.client_notes }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions Footer -->
                <div class="card-footer">
                    <div class="actions-container">
                        <button 
                            v-if="job.status === 'pending'"
                            @click="acceptJob"
                            class="btn btn-success"
                        >
                            <i class="fas fa-check"></i> Accept Job
                        </button>
                        <button 
                            v-if="job.status === 'accepted'"
                            @click="startJob"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-play"></i> Start Job
                        </button>
                        <button 
                            v-if="job.status === 'in_progress'"
                            @click="completeJob"
                            class="btn btn-success"
                        >
                            <i class="fas fa-check-circle"></i> Complete Job
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'JobDetail',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const job = ref(null);

        const fetchJob = async () => {
            try {
                const response = await axios.get(`/service-requests/${route.params.id}`);
                job.value = response.data;
            } catch (error) {
                console.error('Error fetching job:', error);
                Swal.fire('Error', 'Failed to load job details', 'error');
                router.push('/dashboard/handyman/my-jobs');
            }
        };

        const acceptJob = async () => {
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
                        return 'Please enter a valid cost!';
                    }
                }
            });

            if (estimatedCost) {
                try {
                    await axios.post(`/service-requests/${job.value.id}/accept`, {
                        estimated_cost: estimatedCost
                    });
                    Swal.fire('Success!', 'Job accepted successfully', 'success');
                    fetchJob();
                } catch (error) {
                    Swal.fire('Error', 'Failed to accept job', 'error');
                }
            }
        };

        const startJob = async () => {
            const result = await Swal.fire({
                title: 'Start Job?',
                text: 'Are you ready to start this job?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, start',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post(`/service-requests/${job.value.id}/start`);
                    Swal.fire('Success!', 'Job started successfully', 'success');
                    fetchJob();
                } catch (error) {
                    Swal.fire('Error', 'Failed to start job', 'error');
                }
            }
        };

        const completeJob = async () => {
            const { value: formValues } = await Swal.fire({
                title: 'Complete Job',
                html:
                    '<div class="form-group text-left mb-3">' +
                    '<label class="font-weight-bold">Final Cost ($)</label>' +
                    '<input id="swal-input1" class="swal2-input" type="number" step="0.01" placeholder="0.00" style="margin-top: 0.5rem;">' +
                    '</div>' +
                    '<div class="form-group text-left">' +
                    '<label class="font-weight-bold">Notes</label>' +
                    '<textarea id="swal-input2" class="swal2-textarea" placeholder="Add completion notes..." style="margin-top: 0.5rem;"></textarea>' +
                    '</div>',
                showCancelButton: true,
                confirmButtonText: 'Complete',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const finalCost = document.getElementById('swal-input1').value;
                    if (!finalCost || finalCost <= 0) {
                        Swal.showValidationMessage('Please enter a valid final cost');
                        return false;
                    }
                    return {
                        final_cost: finalCost,
                        handyman_notes: document.getElementById('swal-input2').value
                    };
                }
            });

            if (formValues) {
                try {
                    await axios.post(`/service-requests/${job.value.id}/complete`, formValues);
                    Swal.fire('Success!', 'Job completed successfully', 'success');
                    fetchJob();
                } catch (error) {
                    Swal.fire('Error', 'Failed to complete job', 'error');
                }
            }
        };

        const getStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                accepted: 'badge-info',
                in_progress: 'badge-primary',
                completed: 'badge-success',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                pending: 'Pending',
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
                month: 'long',
                day: 'numeric',
            });
        };

        onMounted(() => {
            fetchJob();
        });

        return {
            job,
            acceptJob,
            startJob,
            completeJob,
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
    padding: 1.25rem 1.5rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 0;
    flex: 1;
    min-width: 0;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

/* ============ SECTION TITLES ============ */
.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #007bff;
}

/* ============ INFO LIST ============ */
.info-list {
    margin: 0;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item dt {
    font-weight: 600;
    color: #495057;
    flex: 0 0 45%;
    margin: 0;
}

.info-item dd {
    color: #343a40;
    flex: 1;
    margin: 0;
    text-align: right;
}

/* ============ TEXT CONTENT ============ */
.description-text,
.location-text,
.notes-text {
    font-size: 1rem;
    color: #495057;
    line-height: 1.6;
    margin: 0;
}

.location-text {
    white-space: pre-line;
}

/* ============ BADGES ============ */
.badge {
    padding: 0.5rem 1rem;
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

.badge-secondary {
    background: #6c757d;
    color: white;
}

/* ============ BUTTONS ============ */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.5rem;
    font-size: 1rem;
    font-weight: 500;
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

/* ============ ACTIONS CONTAINER ============ */
.actions-container {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* ============ DIVIDER ============ */
hr {
    border: 0;
    border-top: 1px solid #dee2e6;
}

.my-4 {
    margin-top: 1.5rem;
    margin-bottom: 1.5rem;
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d !important;
}

.text-primary {
    color: #007bff !important;
}

.float-right {
    float: right;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.mb-lg-0 {
    margin-bottom: 0 !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
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

/* ============ RESPONSIVE - LAPTOP (992px - 1199px) ============ */
@media (max-width: 1199px) {
    .card-title {
        font-size: 1.125rem;
    }

    .section-title {
        font-size: 1rem;
    }
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .info-item {
        flex-direction: column;
        gap: 0.25rem;
    }

    .info-item dt,
    .info-item dd {
        text-align: left;
    }

    .info-item dd {
        font-weight: 600;
        color: #007bff;
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

    .card-body {
        padding: 1rem;
    }

    .card-footer {
        padding: 1rem;
    }

    .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .card-title {
        font-size: 1rem;
        word-break: break-word;
    }

    .badge {
        align-self: flex-start;
    }

    .my-4 {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }

    .mb-4 {
        margin-bottom: 1rem !important;
    }

    .actions-container {
        flex-direction: column;
    }

    .actions-container .btn {
        width: 100%;
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0 0.75rem;
    }

    .content-header h1 {
        font-size: 1.25rem;
    }

    .card-header {
        padding: 0.75rem;
    }

    .card-body {
        padding: 0.75rem;
    }

    .card-footer {
        padding: 0.75rem;
    }

    .card-title {
        font-size: 0.9375rem;
    }

    .section-title {
        font-size: 0.9375rem;
    }

    .badge {
        font-size: 0.8125rem;
        padding: 0.375rem 0.75rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9375rem;
    }

    .info-item {
        padding: 0.5rem 0;
        font-size: 0.9375rem;
    }

    .description-text,
    .location-text,
    .notes-text {
        font-size: 0.9375rem;
    }

    .float-right {
        float: none !important;
        width: 100%;
    }
}

/* ============ PRINT STYLES ============ */
@media print {
    .card-footer,
    .btn {
        display: none !important;
    }

    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>