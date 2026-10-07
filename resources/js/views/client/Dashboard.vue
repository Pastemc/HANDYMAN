<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('dashboard.client_dashboard') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">{{ $t('nav.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ $t('nav.dashboard') }}</li>
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
                        <h5><i class="icon fas fa-info"></i> {{ $t('dashboard.welcome_back') }}, {{ userName }}!</h5>
                        {{ $t('dashboard.overview') }}
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ stats.total_requests || 0 }}</h3>
                            <p>{{ $t('requests.total_requests') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <router-link to="/dashboard/client/requests" class="small-box-footer">
                            {{ $t('buttons.view_more') }} <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ stats.pending_requests || 0 }}</h3>
                            <p>{{ $t('requests.pending_requests') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <router-link to="/dashboard/client/requests" class="small-box-footer">
                            {{ $t('buttons.view') }} <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ stats.completed_requests || 0 }}</h3>
                            <p>{{ $t('requests.completed_requests') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <router-link to="/dashboard/client/requests" class="small-box-footer">
                            {{ $t('buttons.view') }} <i class="fas fa-arrow-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>${{ stats.total_spent || 0 }}</h3>
                            <p>{{ $t('common.total') }} {{ $t('payments.amount') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            {{ $t('common.information') }} <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('common.quick_actions') || 'Acciones Rápidas' }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <router-link to="/dashboard/client/request/create" class="btn btn-primary btn-block btn-lg">
                                        <i class="fas fa-plus-circle"></i><br>
                                        {{ $t('requests.new_request') }}
                                    </router-link>
                                </div>
                                <div class="col-md-4">
                                    <router-link to="/dashboard/client/services" class="btn btn-info btn-block btn-lg">
                                        <i class="fas fa-list"></i><br>
                                        {{ $t('categories.service_categories') }}
                                    </router-link>
                                </div>
                                <div class="col-md-4">
                                    <router-link to="/dashboard/client/requests" class="btn btn-success btn-block btn-lg">
                                        <i class="fas fa-clipboard-list"></i><br>
                                        {{ $t('requests.my_requests') }}
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('dashboard.recent_requests') }}</h3>
                            <div class="card-tools">
                                <router-link to="/dashboard/client/requests" class="btn btn-sm btn-primary">
                                    {{ $t('dashboard.view_all') }}
                                </router-link>
                            </div>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ $t('requests.request_number') }}</th>
                                        <th>{{ $t('requests.category') }}</th>
                                        <th>{{ $t('requests.handyman') }}</th>
                                        <th>{{ $t('status.status') }}</th>
                                        <th>{{ $t('forms.date') }}</th>
                                        <th>{{ $t('users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="request in recentRequests" :key="request.id">
                                        <td>{{ request.request_number }}</td>
                                        <td>{{ request.service_category?.name }}</td>
                                        <td>{{ request.handyman?.name || $t('requests.no_handyman_assigned') }}</td>
                                        <td>
                                            <span class="badge" :class="getStatusBadge(request.status)">
                                                {{ getStatusText(request.status) }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(request.created_at) }}</td>
                                        <td>
                                            <router-link 
                                                :to="`/dashboard/client/requests/${request.id}`" 
                                                class="btn btn-sm btn-info"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </router-link>
                                        </td>
                                    </tr>
                                    <tr v-if="recentRequests.length === 0">
                                        <td colspan="6" class="text-center">{{ $t('dashboard.no_data_available') }}</td>
                                    </tr>
                                </tbody>
                            </table>
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
    name: 'ClientDashboard',
    setup() {
        const store = useStore();
        const stats = ref({});
        const recentRequests = ref([]);

        const userName = computed(() => store.getters['auth/user']?.name || 'Usuario');

        const fetchDashboardData = async () => {
            try {
                const response = await axios.get('/dashboard');
                stats.value = response.data.stats || {};
                recentRequests.value = response.data.recent_requests || [];
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
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
            const texts = {
                pending: 'Pending',
                accepted: 'Accepted',
                in_progress: 'In Progress',
                completed: 'Completed',
                cancelled: 'Cancelled',
            };
            return texts[status] || status;
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-ES', {
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
            stats,
            recentRequests,
            getStatusBadge,
            getStatusText,
            formatDate,
        };
    },
};
</script>