<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Mis Solicitudes</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="$router.push('/create-service-request')" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> Nueva Solicitud
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <select v-model="filters.status" @change="fetchRequests" class="form-control">
                                <option value="">Todos los Estados</option>
                                <option value="pending">Pendiente</option>
                                <option value="accepted">Aceptado</option>
                                <option value="in_progress">En Progreso</option>
                                <option value="completed">Completado</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Vista Desktop -->
                <div class="card-body table-responsive p-0 d-none d-md-block">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>N° Solicitud</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Handyman</th>
                                <th>Estado</th>
                                <th>Costo Estimado</th>
                                <th>Costo Final</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in requests" :key="request.id">
                                <td>{{ request.request_number }}</td>
                                <td>{{ request.title }}</td>
                                <td>{{ request.service_category?.name || '-' }}</td>
                                <td>{{ request.handyman?.name || 'Sin asignar' }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(request.status)">
                                        {{ getStatusText(request.status) }}
                                    </span>
                                </td>
                                <td>
                                    <span v-if="request.estimated_cost" class="text-info font-weight-bold">
                                        ~${{ request.estimated_cost }}
                                    </span>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td>
                                    <span v-if="request.final_cost" class="text-success font-weight-bold">
                                        ${{ request.final_cost }}
                                    </span>
                                    <span v-else class="text-muted">Pendiente</span>
                                </td>
                                <td>{{ formatDate(request.created_at) }}</td>
                                <td>
                                    <button @click="viewDetail(request)" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0 && !loading">
                                <td colspan="9" class="text-center">No hay solicitudes</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Vista Mobile -->
                <div class="card-body d-md-none">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>
                    <div v-else-if="requests.length === 0" class="text-center py-4">
                        <p class="text-muted">No hay solicitudes</p>
                    </div>
                    <div v-else>
                        <div v-for="request in requests" :key="request.id" class="card mb-3">
                            <div class="card-body">
                                <h6 class="mb-2">{{ request.request_number }}</h6>
                                <p class="small mb-1"><strong>Título:</strong> {{ request.title }}</p>
                                <p class="small mb-1"><strong>Handyman:</strong> {{ request.handyman?.name || 'Sin asignar' }}</p>
                                <p class="small mb-1">
                                    <span class="badge" :class="getStatusBadge(request.status)">
                                        {{ getStatusText(request.status) }}
                                    </span>
                                </p>
                                
                                <!-- Costos en Mobile -->
                                <div class="mt-2">
                                    <div v-if="request.estimated_cost" class="mb-1">
                                        <small><strong>Costo Estimado:</strong></small>
                                        <span class="text-info ml-2">~${{ request.estimated_cost }}</span>
                                    </div>
                                    <div v-if="request.final_cost">
                                        <small><strong>Costo Final:</strong></small>
                                        <span class="text-success ml-2">${{ request.final_cost }}</span>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <button @click="viewDetail(request)" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </button>
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

    <!-- Modal de Detalle -->
    <div v-if="selectedRequest" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Solicitud - {{ selectedRequest.request_number }}</h5>
                    <button type="button" class="close" @click="selectedRequest = null">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Información del Servicio</h6>
                            <p><strong>Título:</strong> {{ selectedRequest.title }}</p>
                            <p><strong>Categoría:</strong> {{ selectedRequest.service_category?.name }}</p>
                            <p><strong>Estado:</strong> 
                                <span class="badge" :class="getStatusBadge(selectedRequest.status)">
                                    {{ getStatusText(selectedRequest.status) }}
                                </span>
                            </p>
                            <p><strong>Prioridad:</strong> {{ getPriorityText(selectedRequest.priority) }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2">Handyman Asignado</h6>
                            <p v-if="selectedRequest.handyman">
                                <strong>Nombre:</strong> {{ selectedRequest.handyman.name }}<br>
                                <strong>Email:</strong> {{ selectedRequest.handyman.email }}
                            </p>
                            <p v-else class="text-muted">Sin asignar aún</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="border-bottom pb-2">Descripción</h6>
                    <p>{{ selectedRequest.description }}</p>

                    <h6 class="border-bottom pb-2">Ubicación</h6>
                    <p>
                        <strong>Dirección:</strong> {{ selectedRequest.address }}<br>
                        <strong>Ciudad:</strong> {{ selectedRequest.city }}, {{ selectedRequest.state }}<br>
                        <strong>Código Postal:</strong> {{ selectedRequest.zip_code }}
                    </p>

                    <h6 class="border-bottom pb-2">Fecha y Hora Preferida</h6>
                    <p>
                        <strong>Fecha:</strong> {{ formatDate(selectedRequest.preferred_date) }}<br>
                        <strong>Hora:</strong> {{ selectedRequest.preferred_time }}
                    </p>

                    <hr>

                    <!-- INFORMACIÓN DE COSTOS -->
                    <h6 class="border-bottom pb-2">Información de Costos</h6>
                    <div class="row">
                        <div class="col-6">
                            <div class="info-box bg-info">
                                <div class="info-box-content">
                                    <span class="info-box-text">Costo Estimado</span>
                                    <span class="info-box-number">
                                        <span v-if="selectedRequest.estimated_cost">~${{ selectedRequest.estimated_cost }}</span>
                                        <span v-else class="text-muted">Sin definir</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-success">
                                <div class="info-box-content">
                                    <span class="info-box-text">Costo Final</span>
                                    <span class="info-box-number">
                                        <span v-if="selectedRequest.final_cost">${{ selectedRequest.final_cost }}</span>
                                        <span v-else class="text-muted">Pendiente</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedRequest.estimated_cost && !selectedRequest.final_cost" class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> El costo estimado es una referencia. El costo final se confirmará cuando el handyman complete el trabajo.
                    </div>

                    <div v-if="selectedRequest.final_cost" class="alert alert-success mt-3">
                        <i class="fas fa-check-circle"></i>
                        <strong>Trabajo Completado:</strong> El costo final ha sido confirmado en ${{ selectedRequest.final_cost }}
                    </div>

                    <!-- Fotos -->
                    <div v-if="selectedRequest.photos_urls && selectedRequest.photos_urls.length > 0">
                        <hr>
                        <h6 class="border-bottom pb-2">Fotos</h6>
                        <div class="row">
                            <div v-for="(photo, index) in selectedRequest.photos_urls" :key="index" class="col-6 col-md-3 mb-2">
                                <img 
                                    :src="'http://127.0.0.1:8000' + photo" 
                                    class="img-thumbnail"
                                    style="width: 100%; height: 100px; object-fit: cover; cursor: pointer;"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Notas del Handyman -->
                    <div v-if="selectedRequest.handyman_notes">
                        <hr>
                        <h6 class="border-bottom pb-2">Notas del Handyman</h6>
                        <p>{{ selectedRequest.handyman_notes }}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="selectedRequest = null">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useStore } from 'vuex';
import axios from 'axios';

export default {
    name: 'ClientServiceRequests',
    setup() {
        const router = useRouter();
        const store = useStore();
        const requests = ref([]);
        const loading = ref(false);
        const selectedRequest = ref(null);

        const currentUser = computed(() => store.state.auth.user);

        const filters = reactive({
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
                requests.value = response.data.data || [];
                pagination.value = {
                    current_page: response.data.current_page || 1,
                    last_page: response.data.last_page || 1,
                };
            } catch (error) {
                console.error('Error fetching requests:', error);
            } finally {
                loading.value = false;
            }
        };

        const viewDetail = (request) => {
            selectedRequest.value = request;
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
            const texts = {
                pending: 'Pendiente',
                accepted: 'Aceptado',
                in_progress: 'En Progreso',
                completed: 'Completado',
                cancelled: 'Cancelado',
            };
            return texts[status] || status;
        };

        const getPriorityText = (priority) => {
            const texts = {
                low: 'Baja',
                normal: 'Normal',
                high: 'Alta',
                urgent: 'Urgente',
            };
            return texts[priority] || priority;
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-ES');
        };

        onMounted(() => {
            fetchRequests();
        });

        return {
            requests,
            loading,
            filters,
            pagination,
            selectedRequest,
            currentUser,
            fetchRequests,
            viewDetail,
            changePage,
            getStatusBadge,
            getStatusText,
            getPriorityText,
            formatDate,
        };
    },
};
</script>

<style scoped>
.info-box {
    min-height: 80px;
    padding: 10px;
    border-radius: 5px;
    color: white;
}

.info-box-content {
    padding: 5px 10px;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

@media (max-width: 768px) {
    .info-box-number {
        font-size: 16px;
    }
}
</style>