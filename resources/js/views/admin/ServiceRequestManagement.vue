<template> 
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('requests.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="showCreateModal = true" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> {{ $t('requests.new_request') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchRequests"
                                type="text" 
                                class="form-control" 
                                :placeholder="$t('buttons.search') + '...'"
                            >
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                            <select v-model="filters.status" @change="fetchRequests" class="form-control">
                                <option value="">{{ $t('requests.all_status') }}</option>
                                <option value="pending">{{ $t('status.pending') }}</option>
                                <option value="accepted">{{ $t('status.accepted') }}</option>
                                <option value="in_progress">{{ $t('status.in_progress') }}</option>
                                <option value="completed">{{ $t('status.completed') }}</option>
                                <option value="cancelled">{{ $t('status.cancelled') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <button @click="fetchRequests" class="btn btn-primary btn-block">
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
                                <th class="d-none d-md-table-cell">{{ $t('requests.client') }}</th>
                                <th class="d-none d-lg-table-cell">{{ $t('requests.handyman') }}</th>
                                <th class="d-none d-lg-table-cell">{{ $t('requests.category') }}</th>
                                <th>{{ $t('status.status') }}</th>
                                <th class="d-none d-md-table-cell">{{ $t('forms.date') }}</th>
                                <th class="d-none d-lg-table-cell">{{ $t('requests.photos') }}</th>
                                <th class="d-none d-lg-table-cell">{{ $t('requests.cost_info') }}</th>
                                <th>{{ $t('users.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="request in requests" :key="request.id">
                                <td>{{ request.request_number }}</td>
                                <td class="d-none d-md-table-cell">{{ request.client?.name || $t('messages.no_data') }}</td>
                                <td class="d-none d-lg-table-cell">{{ request.handyman?.name || $t('requests.no_handyman_assigned') }}</td>
                                <td class="d-none d-lg-table-cell">{{ request.service_category?.name || $t('messages.no_data') }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(request.status)">
                                        {{ getStatusText(request.status) }}
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell">{{ formatDate(request.created_at) }}</td>
                                <td class="d-none d-lg-table-cell">
                                    <button 
                                        v-if="request.photos_urls && request.photos_urls.length > 0"
                                        @click="viewPhotos(request)"
                                        class="btn btn-sm btn-info"
                                        :title="$t('requests.view_photos')"
                                    >
                                        <i class="fas fa-images"></i> {{ request.photos_urls.length }}
                                    </button>
                                    <span v-else class="text-muted">-</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div v-if="request.final_cost">
                                        <strong class="text-success">Final: ${{ request.final_cost }}</strong>
                                    </div>
                                    <div v-else-if="request.estimated_cost">
                                        <span class="text-info">Estimado: ~${{ request.estimated_cost }}</span>
                                    </div>
                                    <span v-else class="text-muted">Sin costo</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button @click="viewDetail(request)" class="btn btn-sm btn-info" :title="$t('buttons.view')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button @click="editRequest(request)" class="btn btn-sm btn-warning" :title="$t('buttons.edit')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            v-if="request.status === 'accepted' || request.status === 'in_progress'"
                                            @click="showCompleteModal(request)" 
                                            class="btn btn-sm btn-success"
                                            :title="$t('buttons.complete')"
                                        >
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0 && !loading">
                                <td colspan="9" class="text-center">{{ $t('messages.no_data') }}</td>
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

    <!-- Modal de Fotos -->
    <div v-if="showPhotosModal" class="modal fade show modal-backdrop-custom" @click.self="closePhotosModal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-custom">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-images"></i> Fotos - {{ selectedRequestForPhotos?.request_number }}
                    </h5>
                    <button type="button" class="close text-white" @click="closePhotosModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-2">
                    <div v-if="selectedRequestForPhotos?.photos_urls && selectedRequestForPhotos.photos_urls.length > 0">
                        <div id="photosCarousel" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li 
                                    v-for="(photo, index) in selectedRequestForPhotos.photos_urls" 
                                    :key="index"
                                    data-target="#photosCarousel" 
                                    :data-slide-to="index"
                                    :class="{ active: index === 0 }"
                                ></li>
                            </ol>
                            <div class="carousel-inner">
                                <div 
                                    v-for="(photo, index) in selectedRequestForPhotos.photos_urls" 
                                    :key="index"
                                    class="carousel-item"
                                    :class="{ active: index === 0 }"
                                >
                                    <img 
                                        :src="'http://127.0.0.1:8000' + photo" 
                                        class="d-block w-100 carousel-photo"
                                        :alt="`Foto ${index + 1}`"
                                    >
                                    <div class="carousel-caption">
                                        <p class="mb-0">Foto {{ index + 1 }} de {{ selectedRequestForPhotos.photos_urls.length }}</p>
                                    </div>
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#photosCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Anterior</span>
                            </a>
                            <a class="carousel-control-next" href="#photosCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Siguiente</span>
                            </a>
                        </div>

                        <!-- Miniaturas -->
                        <div class="row mt-3 px-2">
                            <div 
                                v-for="(photo, index) in selectedRequestForPhotos.photos_urls" 
                                :key="index"
                                class="col-4 col-md-2 mb-2"
                            >
                                <img 
                                    :src="'http://127.0.0.1:8000' + photo" 
                                    class="img-thumbnail thumbnail-photo"
                                    :alt="`Miniatura ${index + 1}`"
                                    @click="goToSlide(index)"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary btn-sm" @click="closePhotosModal">
                        {{ $t('buttons.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="modal fade show modal-backdrop-custom" @click.self="closeModals">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('requests.new_request') }}</h5>
                    <button type="button" class="close" @click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveRequest">
                        <div class="form-group">
                            <label>{{ $t('requests.client') }} *</label>
                            <select v-model="requestForm.client_id" class="form-control form-control-sm" required>
                                <option value="">{{ $t('forms.select_option') }}</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.name }} - {{ client.email }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('requests.category') }} *</label>
                            <select v-model="requestForm.service_category_id" class="form-control form-control-sm" required>
                                <option value="">{{ $t('forms.select_option') }}</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('forms.title') }} *</label>
                            <input v-model="requestForm.title" type="text" class="form-control form-control-sm" required>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('forms.description') }} *</label>
                            <textarea v-model="requestForm.description" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $t('forms.preferred_date') }} *</label>
                                    <input v-model="requestForm.preferred_date" type="date" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $t('forms.preferred_time') }} *</label>
                                    <input v-model="requestForm.preferred_time" type="time" class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('forms.address') }} *</label>
                            <input v-model="requestForm.address" type="text" class="form-control form-control-sm" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ $t('forms.city') }} *</label>
                                    <input v-model="requestForm.city" type="text" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ $t('forms.state') }} *</label>
                                    <input v-model="requestForm.state" type="text" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ $t('forms.zip_code') }} *</label>
                                    <input v-model="requestForm.zip_code" type="text" class="form-control form-control-sm" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('priority.priority') || 'Prioridad' }}</label>
                            <select v-model="requestForm.priority" class="form-control form-control-sm">
                                <option value="low">{{ $t('priority.low') }}</option>
                                <option value="normal">{{ $t('priority.normal') }}</option>
                                <option value="high">{{ $t('priority.high') }}</option>
                                <option value="urgent">{{ $t('priority.urgent') }}</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="closeModals">{{ $t('buttons.cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="saveRequest" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        {{ $t('buttons.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="modal fade show modal-backdrop-custom" @click.self="closeModals">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('requests.edit_request') }} - {{ editForm.request_number }}</h5>
                    <button type="button" class="close" @click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="updateRequest">
                        <div class="form-group">
                            <label>{{ $t('requests.assign_handyman') }}</label>
                            <select v-model="editForm.handyman_id" class="form-control form-control-sm">
                                <option value="">{{ $t('requests.no_handyman_assigned') }}</option>
                                <option v-for="handyman in handymen" :key="handyman.id" :value="handyman.user_id">
                                    {{ handyman.user?.name }} - {{ handyman.user?.email }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('status.status') }}</label>
                            <select v-model="editForm.status" class="form-control form-control-sm">
                                <option value="pending">{{ $t('status.pending') }}</option>
                                <option value="accepted">{{ $t('status.accepted') }}</option>
                                <option value="in_progress">{{ $t('status.in_progress') }}</option>
                                <option value="completed">{{ $t('status.completed') }}</option>
                                <option value="cancelled">{{ $t('status.cancelled') }}</option>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> <strong>Flujo de Costos:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Costo Estimado:</strong> Se asigna al inicio cuando el admin asigna el handyman (referencia para el cliente)</li>
                                <li><strong>Costo Final:</strong> Lo establece el handyman al completar el trabajo (se crea el pago automáticamente)</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $t('requests.estimated_cost') }} ($)</label>
                                    <input v-model="editForm.estimated_cost" type="number" step="0.01" class="form-control form-control-sm">
                                    <small class="text-muted">Este costo se envía al cliente como referencia inicial</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ $t('requests.final_cost') }} ($)</label>
                                    <input v-model="editForm.final_cost" type="number" step="0.01" class="form-control form-control-sm" :disabled="editForm.status !== 'completed'">
                                    <small class="text-muted">Solo editable cuando el estado es 'Completado'</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('requests.handyman_notes') }}</label>
                            <textarea v-model="editForm.handyman_notes" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="closeModals">{{ $t('buttons.cancel') }}</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="updateRequest" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        {{ $t('buttons.update') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Request Modal -->
    <div v-if="showCompleteRequestModal" class="modal fade show modal-backdrop-custom" @click.self="closeCompleteModal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle"></i> {{ $t('requests.complete_request') }}
                    </h5>
                    <button type="button" class="close text-white" @click="closeCompleteModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>{{ $t('requests.request') }}:</strong> {{ selectedRequestToComplete?.request_number }}<br>
                        <strong>{{ $t('requests.client') }}:</strong> {{ selectedRequestToComplete?.client?.name }}<br>
                        <strong>{{ $t('requests.handyman') }}:</strong> {{ selectedRequestToComplete?.handyman?.name }}
                    </div>

                    <div class="form-group">
                        <label>{{ $t('requests.final_cost') }} *</label>
                        <input 
                            v-model.number="completeForm.final_cost" 
                            type="number" 
                            step="0.01"
                            min="0"
                            class="form-control form-control-sm" 
                            placeholder="0.00"
                            required
                        >
                        <small class="text-muted">
                            Costo estimado: ${{ selectedRequestToComplete?.estimated_cost || '0.00' }}
                        </small>
                    </div>

                    <div class="form-group">
                        <label>{{ $t('payments.payment_method') }} *</label>
                        <select v-model="completeForm.payment_method" class="form-control form-control-sm" required>
                            <option value="">{{ $t('forms.select') }}...</option>
                            <option value="cash">{{ $t('payments.cash') }}</option>
                            <option value="credit_card">{{ $t('payments.credit_card') }}</option>
                            <option value="debit_card">{{ $t('payments.debit_card') }}</option>
                            <option value="transfer">{{ $t('payments.transfer') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ $t('forms.notes') }}</label>
                        <textarea 
                            v-model="completeForm.handyman_notes" 
                            class="form-control form-control-sm" 
                            rows="3"
                            :placeholder="$t('forms.notes') + '...'"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="closeCompleteModal">
                        {{ $t('buttons.cancel') }}
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-success btn-sm" 
                        @click="completeRequest" 
                        :disabled="submitting || !completeForm.final_cost || !completeForm.payment_method"
                    >
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="fas fa-check"></i>
                        {{ $t('buttons.complete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedRequest" class="modal fade show modal-backdrop-custom" @click.self="selectedRequest = null">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('requests.request_details') }} - {{ selectedRequest.request_number }}</h5>
                    <button type="button" class="close" @click="selectedRequest = null">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="border-bottom pb-2">{{ $t('requests.service_info') }}</h6>
                            <dl class="row mb-0">
                                <dt class="col-5">{{ $t('forms.title') }}:</dt>
                                <dd class="col-7">{{ selectedRequest.title }}</dd>

                                <dt class="col-5">{{ $t('requests.category') }}:</dt>
                                <dd class="col-7">{{ selectedRequest.service_category?.name }}</dd>

                                <dt class="col-5">{{ $t('status.status') }}:</dt>
                                <dd class="col-7">
                                    <span class="badge" :class="getStatusBadge(selectedRequest.status)">
                                        {{ getStatusText(selectedRequest.status) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="border-bottom pb-2">{{ $t('requests.people_involved') }}</h6>
                            <dl class="row mb-0">
                                <dt class="col-5">{{ $t('requests.client') }}:</dt>
                                <dd class="col-7">{{ selectedRequest.client?.name }}</dd>

                                <dt class="col-5">{{ $t('requests.handyman') }}:</dt>
                                <dd class="col-7">{{ selectedRequest.handyman?.name || $t('requests.no_handyman_assigned') }}</dd>
                            </dl>
                        </div>
                    </div>

                    <hr>

                    <p><strong>{{ $t('forms.description') }}:</strong><br>{{ selectedRequest.description }}</p>
                    <p><strong>{{ $t('forms.address') }}:</strong><br>{{ selectedRequest.address }}, {{ selectedRequest.city }}</p>
                    <p><strong>{{ $t('forms.date') }}:</strong> {{ formatDate(selectedRequest.preferred_date) }} - {{ selectedRequest.preferred_time }}</p>
                    
                    <hr>

                    <div class="row">
                        <div class="col-6">
                            <p><strong>{{ $t('requests.estimated_cost') }}:</strong> 
                                <span class="text-info">${{ selectedRequest.estimated_cost || '0.00' }}</span>
                            </p>
                        </div>
                        <div class="col-6">
                            <p><strong>{{ $t('requests.final_cost') }}:</strong> 
                                <span v-if="selectedRequest.final_cost" class="text-success font-weight-bold">${{ selectedRequest.final_cost }}</span>
                                <span v-else class="text-muted">{{ $t('status.pending') }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Fotos en el detalle -->
                    <div v-if="selectedRequest.photos_urls && selectedRequest.photos_urls.length > 0">
                        <hr>
                        <h6>{{ $t('requests.photos') }}</h6>
                        <div class="row">
                            <div v-for="(photo, index) in selectedRequest.photos_urls.slice(0, 4)" :key="index" class="col-3 mb-2">
                                <img 
                                    :src="'http://127.0.0.1:8000' + photo" 
                                    class="img-thumbnail" 
                                    style="cursor: pointer; width: 100%; height: 80px; object-fit: cover;"
                                    @click="viewPhotos(selectedRequest)"
                                >
                            </div>
                        </div>
                        <button 
                            v-if="selectedRequest.photos_urls.length > 4"
                            @click="viewPhotos(selectedRequest)" 
                            class="btn btn-sm btn-info mt-2"
                        >
                            <i class="fas fa-images"></i> Ver todas ({{ selectedRequest.photos_urls.length }})
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="selectedRequest = null">{{ $t('buttons.close') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'ServiceRequestManagement',
    setup() {
        const requests = ref([]);
        const clients = ref([]);
        const categories = ref([]);
        const handymen = ref([]);
        const loading = ref(false);
        const submitting = ref(false);
        const showCreateModal = ref(false);
        const showEditModal = ref(false);
        const showCompleteRequestModal = ref(false);
        const showPhotosModal = ref(false);
        const selectedRequest = ref(null);
        const selectedRequestToComplete = ref(null);
        const selectedRequestForPhotos = ref(null);

        const filters = reactive({
            search: '',
            status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const requestForm = reactive({
            client_id: '',
            service_category_id: '',
            title: '',
            description: '',
            address: '',
            city: '',
            state: '',
            zip_code: '',
            preferred_date: '',
            preferred_time: '',
            priority: 'normal',
        });

        const editForm = reactive({
            id: null,
            request_number: '',
            handyman_id: '',
            status: '',
            estimated_cost: '',
            final_cost: '',
            handyman_notes: '',
        });

        const completeForm = reactive({
            final_cost: '',
            payment_method: '',
            handyman_notes: '',
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

        const fetchClients = async () => {
            try {
                const response = await axios.get('/users');
                clients.value = response.data.data.filter(u => 
                    u.roles && u.roles.some(r => r.name === 'client')
                );
            } catch (error) {
                console.error('Error fetching clients:', error);
            }
        };

        const fetchCategories = async () => {
            try {
                const response = await axios.get('/service-categories');
                categories.value = response.data;
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };

        const fetchHandymen = async () => {
            try {
                const response = await axios.get('/handymen');
                handymen.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching handymen:', error);
            }
        };

        const viewPhotos = (request) => {
            selectedRequestForPhotos.value = request;
            showPhotosModal.value = true;
            setTimeout(() => {
                if (window.$) {
                    window.$('#photosCarousel').carousel({ interval: false });
                }
            }, 100);
        };

        const closePhotosModal = () => {
            showPhotosModal.value = false;
            selectedRequestForPhotos.value = null;
        };

        const goToSlide = (index) => {
            if (window.$) {
                window.$('#photosCarousel').carousel(index);
            }
        };

        const viewDetail = (request) => {
            selectedRequest.value = request;
        };

        const editRequest = (request) => {
            editForm.id = request.id;
            editForm.request_number = request.request_number;
            editForm.handyman_id = request.handyman_id || '';
            editForm.status = request.status;
            editForm.estimated_cost = request.estimated_cost || '';
            editForm.final_cost = request.final_cost || '';
            editForm.handyman_notes = request.handyman_notes || '';
            showEditModal.value = true;
        };

        const showCompleteModal = (request) => {
            selectedRequestToComplete.value = request;
            completeForm.final_cost = request.estimated_cost || '';
            completeForm.payment_method = '';
            completeForm.handyman_notes = '';
            showCompleteRequestModal.value = true;
        };

        const closeCompleteModal = () => {
            showCompleteRequestModal.value = false;
            selectedRequestToComplete.value = null;
            completeForm.final_cost = '';
            completeForm.payment_method = '';
            completeForm.handyman_notes = '';
        };

        const saveRequest = async () => {
            submitting.value = true;
            try {
                await axios.post('/service-requests', requestForm);
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Solicitud creada exitosamente',
                    timer: 2000,
                });

                closeModals();
                fetchRequests();
            } catch (error) {
                console.error('Error creating request:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al crear la solicitud',
                });
            } finally {
                submitting.value = false;
            }
        };

        const updateRequest = async () => {
            submitting.value = true;
            try {
                await axios.put(`/service-requests/${editForm.id}`, {
                    handyman_id: editForm.handyman_id,
                    status: editForm.status,
                    estimated_cost: editForm.estimated_cost,
                    final_cost: editForm.final_cost,
                    handyman_notes: editForm.handyman_notes,
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: 'Solicitud actualizada exitosamente',
                    timer: 2000,
                });

                closeModals();
                fetchRequests();
            } catch (error) {
                console.error('Error updating request:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al actualizar',
                });
            } finally {
                submitting.value = false;
            }
        };

        const completeRequest = async () => {
            if (!completeForm.final_cost || !completeForm.payment_method) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Debes ingresar el costo final y método de pago',
                });
                return;
            }

            submitting.value = true;
            try {
                await axios.post(`/service-requests/${selectedRequestToComplete.value.id}/complete`, {
                    final_cost: completeForm.final_cost,
                    payment_method: completeForm.payment_method,
                    handyman_notes: completeForm.handyman_notes,
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Completado!',
                    text: 'Solicitud completada y pago registrado',
                    timer: 2000,
                });

                closeCompleteModal();
                fetchRequests();
            } catch (error) {
                console.error('Error completing request:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al completar',
                });
            } finally {
                submitting.value = false;
            }
        };

        const closeModals = () => {
            showCreateModal.value = false;
            showEditModal.value = false;
            Object.keys(requestForm).forEach(key => {
                requestForm[key] = key === 'priority' ? 'normal' : '';
            });
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

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-ES');
        };

        onMounted(() => {
            fetchRequests();
            fetchClients();
            fetchCategories();
            fetchHandymen();
        });

        return {
            requests,
            clients,
            categories,
            handymen,
            loading,
            submitting,
            filters,
            pagination,
            showCreateModal,
            showEditModal,
            showCompleteRequestModal,
            showPhotosModal,
            selectedRequest,
            selectedRequestToComplete,
            selectedRequestForPhotos,
            requestForm,
            editForm,
            completeForm,
            fetchRequests,
            viewPhotos,
            closePhotosModal,
            goToSlide,
            viewDetail,
            editRequest,
            showCompleteModal,
            closeCompleteModal,
            saveRequest,
            updateRequest,
            completeRequest,
            closeModals,
            changePage,
            getStatusBadge,
            getStatusText,
            formatDate,
        };
    },
};
</script>

<style scoped>
.modal-backdrop-custom {
    display: block;
    background: rgba(0, 0, 0, 0.5);
}

.modal-dialog-custom {
    max-width: 800px;
}

.carousel-photo {
    max-height: 500px;
    object-fit: contain;
}

.thumbnail-photo {
    cursor: pointer;
    height: 60px;
    object-fit: cover;
}

.thumbnail-photo:hover {
    opacity: 0.7;
    transform: scale(1.05);
    transition: all 0.3s;
}

@media (max-width: 768px) {
    .modal-dialog-custom {
        margin: 10px;
    }
}
</style>