<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('requests.my_requests') }}</h1>
                </div>
                <div class="col-sm-6">
                    <router-link to="/dashboard/client/request/create" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> {{ $t('requests.new_request') }}
                    </router-link>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchRequests"
                                type="text" 
                                class="form-control" 
                                :placeholder="$t('buttons.search') + '...'"
                            >
                        </div>
                        <div class="col-md-2">
                            <select v-model="filters.status" @change="fetchRequests" class="form-control">
                                <option value="">{{ $t('requests.all_status') }}</option>
                                <option value="pending">{{ $t('status.pending') }}</option>
                                <option value="accepted">{{ $t('status.accepted') }}</option>
                                <option value="in_progress">{{ $t('status.in_progress') }}</option>
                                <option value="completed">{{ $t('status.completed') }}</option>
                                <option value="cancelled">{{ $t('status.cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button @click="fetchRequests" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ $t('buttons.search') }}
                            </button>
                        </div>
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
                                <th>{{ $t('requests.cost_info') }}</th>
                                <th>{{ $t('users.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in requests" :key="request.id">
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
                                    <span v-if="request.final_cost">${{ request.final_cost }}</span>
                                    <span v-else-if="request.estimated_cost">~${{ request.estimated_cost }}</span>
                                    <span v-else>{{ $t('status.pending') }}</span>
                                </td>
                                <td>
                                    <router-link 
                                        :to="`/dashboard/client/requests/${request.id}`" 
                                        class="btn btn-sm btn-info"
                                        :title="$t('buttons.view')"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </router-link>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0 && !loading">
                                <td colspan="7" class="text-center">{{ $t('messages.no_data') }}</td>
                            </tr>
                        </tbody>
                    </table>
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
    name: 'MyRequests',
    setup() {
        const requests = ref([]);
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

        const fetchRequests = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/service-requests', { params: filters });
                requests.value = response.data.data;
                pagination.value = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                };
            } catch (error) {
                console.error('Error fetching requests:', error);
            } finally {
                loading.value = false;
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchRequests();
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
            fetchRequests();
        });

        return {
            requests,
            loading,
            filters,
            pagination,
            fetchRequests,
            changePage,
            getStatusBadge,
            getStatusText,
            formatDate,
        };
    },
};
</script>