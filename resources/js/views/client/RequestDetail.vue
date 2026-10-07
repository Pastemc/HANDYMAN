<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('requests.request_details') }}</h1>
                </div>
                <div class="col-sm-6">
                    <router-link to="/dashboard/client/requests" class="btn btn-secondary float-right">
                        <i class="fas fa-arrow-left"></i> {{ $t('buttons.back') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card" v-if="request">
                <div class="card-header">
                    <h3 class="card-title">{{ request.request_number }} - {{ request.title }}</h3>
                    <span class="float-right badge" :class="getStatusBadge(request.status)">
                        {{ getStatusText(request.status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>{{ $t('requests.service_info') }}</h5>
                            <dl class="row">
                                <dt class="col-sm-5">{{ $t('requests.category') }}:</dt>
                                <dd class="col-sm-7">{{ request.service_category?.name }}</dd>

                                <dt class="col-sm-5">{{ $t('forms.date') }}:</dt>
                                <dd class="col-sm-7">{{ formatDate(request.preferred_date) }}</dd>

                                <dt class="col-sm-5">{{ $t('forms.time') }}:</dt>
                                <dd class="col-sm-7">{{ request.preferred_time }}</dd>

                                <dt class="col-sm-5">{{ $t('priority.priority') }}:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge" :class="getPriorityBadge(request.priority)">
                                        {{ getPriorityText(request.priority) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h5>{{ $t('requests.cost_info') }}</h5>
                            <dl class="row">
                                <dt class="col-sm-6">{{ $t('requests.estimated_cost') }}:</dt>
                                <dd class="col-sm-6">${{ request.estimated_cost || '0.00' }}</dd>

                                <dt class="col-sm-6">{{ $t('requests.final_cost') }}:</dt>
                                <dd class="col-sm-6">
                                    <strong v-if="request.final_cost">${{ request.final_cost }}</strong>
                                    <span v-else class="text-muted">{{ $t('status.pending') }}</span>
                                </dd>

                                <dt class="col-sm-6">{{ $t('requests.handyman') }}:</dt>
                                <dd class="col-sm-7">{{ request.handyman?.name || $t('requests.no_handyman_assigned') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h5>{{ $t('forms.description') }}</h5>
                            <p>{{ request.description }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h5>{{ $t('requests.location') }}</h5>
                            <p>
                                {{ request.address }}<br>
                                {{ request.city }}, {{ request.state }} {{ request.zip_code }}
                            </p>
                        </div>
                    </div>

                    <div class="row" v-if="request.client_notes">
                        <div class="col-12">
                            <h5>{{ $t('requests.client_notes') }}</h5>
                            <p>{{ request.client_notes }}</p>
                        </div>
                    </div>

                    <div class="row" v-if="request.handyman_notes">
                        <div class="col-12">
                            <h5>{{ $t('requests.handyman_notes') }}</h5>
                            <p>{{ request.handyman_notes }}</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button 
                        v-if="request.status === 'pending'"
                        @click="cancelRequest"
                        class="btn btn-danger"
                    >
                        <i class="fas fa-times"></i> {{ $t('buttons.cancel') }} {{ $t('requests.title') }}
                    </button>
                </div>
            </div>

            <div v-else class="text-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">{{ $t('messages.loading') }}</span>
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
    name: 'RequestDetail',
    setup() {
        const route = useRoute();
        const router = useRouter();
        const request = ref(null);

        const fetchRequest = async () => {
            try {
                const response = await axios.get(`/service-requests/${route.params.id}`);
                request.value = response.data;
            } catch (error) {
                console.error('Error fetching request:', error);
                Swal.fire('Error', 'Failed to load request details', 'error');
                router.push('/dashboard/client/requests');
            }
        };

        const cancelRequest = async () => {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to cancel this request?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'No'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post(`/service-requests/${request.value.id}/cancel`);
                    Swal.fire('Cancelled', 'Request has been cancelled', 'success');
                    fetchRequest();
                } catch (error) {
                    Swal.fire('Error', 'Failed to cancel request', 'error');
                }
            }
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
            const i18n = window.i18n;
            if (i18n) {
                return i18n.t(`status.${status}`);
            }
            return status;
        };

        const getPriorityBadge = (priority) => {
            const badges = {
                low: 'badge-secondary',
                normal: 'badge-primary',
                high: 'badge-warning',
                urgent: 'badge-danger',
            };
            return badges[priority] || 'badge-secondary';
        };

        const getPriorityText = (priority) => {
            const i18n = window.i18n;
            if (i18n) {
                return i18n.t(`priority.${priority}`);
            }
            return priority;
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        };

        onMounted(() => {
            fetchRequest();
        });

        return {
            request,
            cancelRequest,
            getStatusBadge,
            getStatusText,
            getPriorityBadge,
            getPriorityText,
            formatDate,
        };
    },
};
</script>