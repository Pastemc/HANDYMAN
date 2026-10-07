<template>
    <div class="client-registrations-page">
        <div class="content-header">
            <div class="container-fluid">
                <div class="header-wrapper">
                    <h1 class="page-title">
                        <i class="fas fa-clipboard-list"></i>
                        Client Registration Requests
                    </h1>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <div class="filters-grid">
                            <div class="filter-item">
                                <input 
                                    v-model="filters.search"
                                    @keyup.enter="fetchRegistrations"
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Search..."
                                >
                            </div>
                            <div class="filter-item">
                                <select v-model="filters.status" @change="fetchRegistrations" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <button @click="fetchRegistrations" class="btn btn-primary btn-search">
                                    <i class="fas fa-search"></i>
                                    <span class="btn-text">Search</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Loading registrations...</p>
                    </div>

                    <!-- Table Desktop -->
                    <div v-else class="card-body table-responsive p-0 desktop-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Document</th>
                                    <th>Service</th>
                                    <th>ZIP Code</th>
                                    <th>Photos</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="reg in registrations" :key="reg.id">
                                    <td>{{ reg.name }}</td>
                                    <td>{{ reg.email }}</td>
                                    <td>{{ reg.phone }}</td>
                                    <td>{{ reg.document_type?.toUpperCase() }} - {{ reg.document_number }}</td>
                                    <td>{{ reg.service_category?.name || '-' }}</td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-map-pin"></i> {{ reg.zip_code }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="reg.document_photos && reg.document_photos.length > 0" class="badge badge-info">
                                            <i class="fas fa-images"></i> {{ reg.document_photos.length }}
                                        </span>
                                        <span v-else class="text-muted">-</span>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getStatusBadge(reg.status)">
                                            {{ getStatusText(reg.status) }}
                                        </span>
                                    </td>
                                    <td>{{ formatDate(reg.created_at) }}</td>
                                    <td>
                                        <div class="action-buttons">
                                            <button @click="viewDetail(reg)" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button 
                                                v-if="reg.status === 'pending'"
                                                @click="approveRegistration(reg)" 
                                                class="btn btn-sm btn-success"
                                                title="Approve"
                                            >
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button 
                                                v-if="reg.status === 'pending'"
                                                @click="rejectRegistration(reg)" 
                                                class="btn btn-sm btn-danger"
                                                title="Reject"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="registrations.length === 0">
                                    <td colspan="10" class="text-center empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>No registration requests found</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cards Mobile -->
                    <div v-if="!loading" class="mobile-cards">
                        <div v-for="reg in registrations" :key="reg.id" class="registration-card">
                            <div class="card-header-mobile">
                                <h5>{{ reg.name }}</h5>
                                <span class="badge" :class="getStatusBadge(reg.status)">
                                    {{ getStatusText(reg.status) }}
                                </span>
                            </div>
                            <div class="card-body-mobile">
                                <div class="info-row">
                                    <i class="fas fa-envelope"></i>
                                    <span>{{ reg.email }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ reg.phone }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-id-card"></i>
                                    <span>{{ reg.document_type?.toUpperCase() }} - {{ reg.document_number }}</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-map-pin"></i>
                                    <span>ZIP: {{ reg.zip_code }} - {{ reg.city }}, {{ reg.state }}</span>
                                </div>
                                <div class="info-row" v-if="reg.service_category">
                                    <i class="fas fa-tools"></i>
                                    <span>{{ reg.service_category.name }}</span>
                                </div>
                                <div class="info-row" v-if="reg.document_photos && reg.document_photos.length > 0">
                                    <i class="fas fa-images"></i>
                                    <span>{{ reg.document_photos.length }} photos uploaded</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ formatDate(reg.created_at) }}</span>
                                </div>
                            </div>
                            <div class="card-footer-mobile">
                                <button @click="viewDetail(reg)" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button 
                                    v-if="reg.status === 'pending'"
                                    @click="approveRegistration(reg)" 
                                    class="btn btn-sm btn-success"
                                >
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button 
                                    v-if="reg.status === 'pending'"
                                    @click="rejectRegistration(reg)" 
                                    class="btn btn-sm btn-danger"
                                >
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>

                        <div v-if="registrations.length === 0" class="empty-state-mobile">
                            <i class="fas fa-inbox"></i>
                            <p>No registration requests found</p>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer clearfix" v-if="pagination.last_page > 1">
                        <ul class="pagination pagination-sm m-0 float-right">
                            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">«</a>
                            </li>
                            <li class="page-item active">
                                <span class="page-link">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
                            </li>
                            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">»</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Detalle CON MAPA USANDO ZIP CODE -->
        <div v-if="selectedRegistration" class="modal-overlay" @click.self="selectedRegistration = null">
            <div class="modal-container">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle"></i>
                        Registration Details - {{ selectedRegistration.name }}
                    </h5>
                    <button type="button" class="btn-close-modal" @click="selectedRegistration = null">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- MAPA DE UBICACIÓN USANDO ZIP CODE -->
                    <div class="detail-section map-section">
                        <h6>
                            <i class="fas fa-map-marked-alt"></i> 
                            Client Location
                            <span class="badge badge-primary ml-2">
                                <i class="fas fa-map-pin"></i> ZIP: {{ selectedRegistration.zip_code }}
                            </span>
                        </h6>
                        
                        <!-- Loading del mapa -->
                        <div v-if="loadingMapCoordinates" class="map-loading">
                            <div class="spinner-border text-light" role="status"></div>
                            <p>Loading map location from ZIP Code...</p>
                        </div>

                        <!-- Mapa -->
                        <div v-else-if="mapCoordinates" class="map-container">
                            <iframe
                                :src="getGoogleMapsEmbedUrlByCoordinates()"
                                width="100%"
                                height="400"
                                style="border:0; border-radius: 0.5rem;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                            
                            <div class="map-info">
                                <small>
                                    <i class="fas fa-crosshairs"></i>
                                    Coordinates: {{ mapCoordinates.latitude }}, {{ mapCoordinates.longitude }}
                                    | {{ selectedRegistration.city }}, {{ selectedRegistration.state }}
                                </small>
                            </div>
                        </div>

                        <!-- Error si no se encuentra el ZIP -->
                        <div v-else class="map-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Unable to load map for ZIP Code {{ selectedRegistration.zip_code }}</p>
                            <small>Using address fallback</small>
                        </div>
                        
                        <div class="map-actions">
                            <a 
                                :href="getGoogleMapsDirectionsUrl(selectedRegistration)"
                                target="_blank"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                            <a 
                                :href="getGoogleMapsViewUrl(selectedRegistration)"
                                target="_blank"
                                class="btn btn-secondary btn-sm"
                            >
                                <i class="fas fa-external-link-alt"></i> Open in Google Maps
                            </a>
                        </div>
                    </div>

                    <div class="details-grid">
                        <div class="detail-section">
                            <h6><i class="fas fa-user"></i> Personal Information</h6>
                            <div class="detail-item">
                                <strong>Name:</strong>
                                <span>{{ selectedRegistration.name }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Email:</strong>
                                <span>{{ selectedRegistration.email }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Phone:</strong>
                                <span>{{ selectedRegistration.phone }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Document:</strong>
                                <span>{{ selectedRegistration.document_type?.toUpperCase() }} - {{ selectedRegistration.document_number }}</span>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h6><i class="fas fa-map-marker-alt"></i> Location Details</h6>
                            <div class="detail-item">
                                <strong>Address:</strong>
                                <span>{{ selectedRegistration.address }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>City:</strong>
                                <span>{{ selectedRegistration.city }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>State:</strong>
                                <span>{{ selectedRegistration.state }}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Zip Code:</strong>
                                <span class="badge badge-primary">{{ selectedRegistration.zip_code }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedRegistration.service_category" class="detail-section">
                        <h6><i class="fas fa-tools"></i> Service of Interest</h6>
                        <p>{{ selectedRegistration.service_category.name }}</p>
                    </div>

                    <div v-if="selectedRegistration.message" class="detail-section">
                        <h6><i class="fas fa-comment"></i> Message</h6>
                        <p class="message-text">{{ selectedRegistration.message }}</p>
                    </div>

                    <!-- VISUALIZACIÓN DE FOTOS SUBIDAS -->
                    <div v-if="selectedRegistration.document_photos && selectedRegistration.document_photos.length > 0" class="detail-section">
                        <h6><i class="fas fa-images"></i> Uploaded Photos ({{ selectedRegistration.document_photos.length }})</h6>
                        
                        <div class="photos-grid">
                            <div 
                                v-for="(photo, index) in selectedRegistration.document_photos" 
                                :key="index"
                                class="photo-item"
                                @click="openPhotoModal(photo, index)"
                            >
                                <img 
                                    :src="getPhotoUrl(photo)" 
                                    :alt="`Document Photo ${index + 1}`"
                                    class="photo-thumbnail"
                                >
                                <div class="photo-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <span>Photo {{ index + 1 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h6><i class="fas fa-info-circle"></i> Status Information</h6>
                        <div class="detail-item">
                            <strong>Status:</strong>
                            <span class="badge" :class="getStatusBadge(selectedRegistration.status)">
                                {{ getStatusText(selectedRegistration.status) }}
                            </span>
                        </div>
                        <div class="detail-item">
                            <strong>Request Date:</strong>
                            <span>{{ formatDate(selectedRegistration.created_at) }}</span>
                        </div>
                        <div v-if="selectedRegistration.approved_at" class="detail-item">
                            <strong>Approval Date:</strong>
                            <span>{{ formatDate(selectedRegistration.approved_at) }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button 
                        v-if="selectedRegistration.status === 'pending'"
                        @click="approveRegistration(selectedRegistration)" 
                        class="btn btn-success"
                    >
                        <i class="fas fa-check"></i> Approve & Create User
                    </button>
                    <button 
                        v-if="selectedRegistration.status === 'pending'"
                        @click="rejectRegistration(selectedRegistration)" 
                        class="btn btn-danger"
                    >
                        <i class="fas fa-times"></i> Reject
                    </button>
                    <button type="button" class="btn btn-secondary" @click="closeDetailModal">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal para ver foto en grande -->
        <div v-if="selectedPhoto" class="photo-modal-overlay" @click="closePhotoModal">
            <div class="photo-modal-container" @click.stop>
                <button class="btn-close-photo" @click="closePhotoModal">
                    <i class="fas fa-times"></i>
                </button>
                <div class="photo-modal-header">
                    <h5>Photo {{ selectedPhotoIndex + 1 }} of {{ selectedRegistration.document_photos.length }}</h5>
                    <div class="photo-nav">
                        <button 
                            @click="previousPhoto" 
                            class="btn-nav"
                            :disabled="selectedPhotoIndex === 0"
                        >
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button 
                            @click="nextPhoto" 
                            class="btn-nav"
                            :disabled="selectedPhotoIndex === selectedRegistration.document_photos.length - 1"
                        >
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="photo-modal-body">
                    <img :src="getPhotoUrl(selectedPhoto)" alt="Full size photo" class="photo-full">
                </div>
                <div class="photo-modal-footer">
                    <a 
                        :href="getPhotoUrl(selectedPhoto)" 
                        :download="`photo-${selectedPhotoIndex + 1}.jpg`"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'ClientRegistrationManagement',
    setup() {
        const registrations = ref([]);
        const loading = ref(false);
        const selectedRegistration = ref(null);
        const selectedPhoto = ref(null);
        const selectedPhotoIndex = ref(0);
        
        // Nuevas variables para el mapa con ZIP Code
        const loadingMapCoordinates = ref(false);
        const mapCoordinates = ref(null);

        const filters = reactive({
            search: '',
            status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const fetchRegistrations = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/client-registrations', { params: filters });
                
                if (response.data.data) {
                    registrations.value = response.data.data;
                    pagination.value = {
                        current_page: response.data.current_page || 1,
                        last_page: response.data.last_page || 1,
                    };
                } else if (Array.isArray(response.data)) {
                    registrations.value = response.data;
                } else {
                    registrations.value = [];
                }
                
                console.log('Registrations loaded:', registrations.value);
            } catch (error) {
                console.error('Error:', error);
                
                let errorMessage = 'Error loading registration requests';
                if (error.response) {
                    errorMessage = error.response.data?.message || `Server Error ${error.response.status}`;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                });
                
                registrations.value = [];
            } finally {
                loading.value = false;
            }
        };

        // ========== FUNCIÓN PARA OBTENER COORDENADAS DESDE ZIP CODE ==========
        const fetchCoordinatesFromZipCode = async (zipCode) => {
            loadingMapCoordinates.value = true;
            mapCoordinates.value = null;

            try {
                const response = await axios.get(`/portal/lookup-zipcode/${zipCode}`);
                
                if (response.data.success && response.data.data) {
                    mapCoordinates.value = {
                        latitude: response.data.data.latitude,
                        longitude: response.data.data.longitude,
                        city: response.data.data.city,
                        state: response.data.data.state_abbreviation,
                    };
                    console.log('Coordinates loaded from ZIP:', mapCoordinates.value);
                } else {
                    console.warn('No coordinates found for ZIP:', zipCode);
                    mapCoordinates.value = null;
                }
            } catch (error) {
                console.error('Error fetching coordinates:', error);
                mapCoordinates.value = null;
            } finally {
                loadingMapCoordinates.value = false;
            }
        };

        const viewDetail = async (registration) => {
            selectedRegistration.value = registration;
            // Obtener coordenadas del ZIP Code
            if (registration.zip_code) {
                await fetchCoordinatesFromZipCode(registration.zip_code);
            }
        };

        const closeDetailModal = () => {
            selectedRegistration.value = null;
            mapCoordinates.value = null;
            loadingMapCoordinates.value = false;
        };

        const getPhotoUrl = (photoPath) => {
            if (!photoPath) return '';
            
            if (photoPath.startsWith('http://') || photoPath.startsWith('https://')) {
                return photoPath;
            }
            
            if (photoPath.startsWith('/storage')) {
                return `${window.location.origin}${photoPath}`;
            }
            
            if (photoPath.startsWith('storage/')) {
                return `${window.location.origin}/${photoPath}`;
            }
            
            return `${window.location.origin}/storage/${photoPath}`;
        };

        // ========== FUNCIONES PARA GOOGLE MAPS USANDO COORDENADAS DEL ZIP CODE ==========
        
        const getGoogleMapsEmbedUrlByCoordinates = () => {
            if (!mapCoordinates.value) return '';
            
            const { latitude, longitude } = mapCoordinates.value;
            
            // Usar coordenadas exactas del ZIP Code
            return `https://maps.google.com/maps?q=${latitude},${longitude}&t=&z=13&ie=UTF8&iwloc=&output=embed`;
        };

        const getGoogleMapsDirectionsUrl = (registration) => {
            // Si tenemos coordenadas, usarlas; si no, usar dirección completa
            if (mapCoordinates.value) {
                const { latitude, longitude } = mapCoordinates.value;
                return `https://www.google.com/maps/dir/?api=1&destination=${latitude},${longitude}`;
            }
            
            const destination = encodeURIComponent(
                `${registration.address}, ${registration.city}, ${registration.state} ${registration.zip_code}`
            );
            return `https://www.google.com/maps/dir/?api=1&destination=${destination}`;
        };

        const getGoogleMapsViewUrl = (registration) => {
            // Si tenemos coordenadas, usarlas; si no, usar ZIP Code
            if (mapCoordinates.value) {
                const { latitude, longitude } = mapCoordinates.value;
                return `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
            }
            
            return `https://www.google.com/maps/search/?api=1&query=${registration.zip_code}`;
        };

        // ========== FIN FUNCIONES GOOGLE MAPS ==========

        const openPhotoModal = (photo, index) => {
            selectedPhoto.value = photo;
            selectedPhotoIndex.value = index;
        };

        const closePhotoModal = () => {
            selectedPhoto.value = null;
            selectedPhotoIndex.value = 0;
        };

        const previousPhoto = () => {
            if (selectedPhotoIndex.value > 0) {
                selectedPhotoIndex.value--;
                selectedPhoto.value = selectedRegistration.value.document_photos[selectedPhotoIndex.value];
            }
        };

        const nextPhoto = () => {
            if (selectedPhotoIndex.value < selectedRegistration.value.document_photos.length - 1) {
                selectedPhotoIndex.value++;
                selectedPhoto.value = selectedRegistration.value.document_photos[selectedPhotoIndex.value];
            }
        };

        const approveRegistration = async (registration) => {
            const result = await Swal.fire({
                title: 'Approve Request?',
                html: `
                    <p>An account will be created for <strong>${registration.name}</strong></p>
                    <p>Email: ${registration.email}</p>
                    <p>Initial Password: ${registration.document_number}</p>
                    <p class="text-warning mt-3"><i class="fas fa-exclamation-triangle"></i> A welcome email will be sent with login credentials</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '✓ Approve & Create User',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#28a745',
            });

            if (result.isConfirmed) {
                try {
                    const response = await axios.post(`/client-registrations/${registration.id}/approve`);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Approved!',
                        html: `
                            <p>Client registered successfully</p>
                            <p class="text-success"><i class="fas fa-envelope"></i> Welcome email sent</p>
                        `,
                        timer: 3000,
                    });

                    closeDetailModal();
                    fetchRegistrations();

                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.message || 'Error approving request',
                    });
                }
            }
        };

        const rejectRegistration = async (registration) => {
            const result = await Swal.fire({
                title: 'Reject Request?',
                text: `The request from ${registration.name} will be rejected`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
            });

            if (result.isConfirmed) {
                try {
                    await axios.post(`/client-registrations/${registration.id}/reject`);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Rejected',
                        timer: 2000,
                    });

                    closeDetailModal();
                    fetchRegistrations();

                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error rejecting request',
                    });
                }
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchRegistrations();
            }
        };

        const getStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                approved: 'badge-success',
                rejected: 'badge-danger',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                pending: '⏳ Pending',
                approved: '✓ Approved',
                rejected: '✗ Rejected',
            };
            return texts[status] || status;
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        onMounted(() => {
            fetchRegistrations();
        });

        return {
            registrations,
            loading,
            filters,
            pagination,
            selectedRegistration,
            selectedPhoto,
            selectedPhotoIndex,
            loadingMapCoordinates,
            mapCoordinates,
            fetchRegistrations,
            viewDetail,
            closeDetailModal,
            getPhotoUrl,
            getGoogleMapsEmbedUrlByCoordinates,
            getGoogleMapsDirectionsUrl,
            getGoogleMapsViewUrl,
            openPhotoModal,
            closePhotoModal,
            previousPhoto,
            nextPhoto,
            approveRegistration,
            rejectRegistration,
            changePage,
            getStatusBadge,
            getStatusText,
            formatDate,
        };
    },
};
</script>

<style scoped>
.client-registrations-page {
    min-height: 100vh;
    background: #f4f6f9;
}

/* ============ HEADER ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    margin-bottom: 1.5rem;
}

.header-wrapper {
    padding: 0 1rem;
}

.page-title {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 700;
    color: #343a40;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* ============ FILTERS ============ */
.card-header {
    padding: 1.5rem;
    background: white;
    border-bottom: 1px solid #dee2e6;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 1rem;
}

.filter-item {
    width: 100%;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    font-size: 1rem;
}

.btn-search {
    width: 100%;
    white-space: nowrap;
}

/* ============ LOADING ============ */
.loading-state {
    text-align: center;
    padding: 4rem 2rem;
}

.loading-state p {
    margin-top: 1rem;
    color: #6c757d;
}

/* ============ TABLE DESKTOP ============ */
.desktop-table {
    display: block;
}

.mobile-cards {
    display: none;
}

.table {
    margin: 0;
    width: 100%;
}

.table thead th {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    padding: 1rem;
    white-space: nowrap;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.empty-state {
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    display: block;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0;
    font-size: 1.125rem;
}

/* ============ CARDS MOBILE ============ */
.registration-card {
    background: white;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header-mobile {
    padding: 1rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.card-header-mobile h5 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #343a40;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.card-body-mobile {
    padding: 1rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f3f5;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row i {
    width: 20px;
    color: #6c757d;
    flex-shrink: 0;
}

.info-row span {
    flex: 1;
    font-size: 0.9375rem;
    color: #495057;
    word-break: break-word;
}

.card-footer-mobile {
    padding: 1rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.card-footer-mobile .btn {
    flex: 1;
    min-width: 100px;
}

.empty-state-mobile {
    text-align: center;
    padding: 4rem 2rem;
    color: #6c757d;
}

.empty-state-mobile i {
    font-size: 3rem;
    display: block;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state-mobile p {
    margin: 0;
    font-size: 1.125rem;
}

/* ============ BADGES ============ */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-info {
    background: #17a2b8;
    color: white;
}

.badge-warning {
    background: #ffc107;
    color: #212529;
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

.badge-primary {
    background: #007bff;
    color: white;
}

.ml-2 {
    margin-left: 0.5rem;
}

/* ============ MAPA SECTION ============ */
.map-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.map-section h6 {
    color: white !important;
    border-bottom-color: rgba(255, 255, 255, 0.3) !important;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.map-container {
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    margin-bottom: 0.75rem;
}

.map-loading {
    background: rgba(0, 0, 0, 0.3);
    padding: 4rem 2rem;
    border-radius: 0.5rem;
    text-align: center;
    color: white;
    margin-bottom: 0.75rem;
}

.map-loading p {
    margin-top: 1rem;
    font-size: 0.9375rem;
}

.map-error {
    background: rgba(220, 53, 69, 0.2);
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
    color: white;
    margin-bottom: 0.75rem;
}

.map-error i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}

.map-error p {
    margin: 0.5rem 0;
    font-weight: 600;
}

.map-error small {
    opacity: 0.9;
}

.map-info {
    background: rgba(255, 255, 255, 0.15);
    padding: 0.75rem;
    border-radius: 0.375rem;
    margin-bottom: 0.75rem;
}

.map-info small {
    color: white;
    font-size: 0.8125rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.map-info i {
    font-size: 0.875rem;
}

.map-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.map-actions .btn {
    flex: 1;
    min-width: 150px;
}

/* ============ MODAL ============ */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    padding: 1rem;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container {
    background: white;
    border-radius: 0.5rem;
    max-width: 1000px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: clamp(1.125rem, 2vw, 1.5rem);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.btn-close-modal {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close-modal:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 1.5rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section h6 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item strong {
    color: #6c757d;
    font-weight: 600;
}

.detail-item span {
    color: #495057;
    text-align: right;
    word-break: break-word;
}

.message-text {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    color: #495057;
    line-height: 1.6;
    margin: 0;
}

.modal-footer {
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.modal-footer .btn {
    min-width: 120px;
}

/* ============ GALERÍA DE FOTOS ============ */
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 140px), 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.photo-item {
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.photo-item:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.photo-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    color: white;
    gap: 0.5rem;
}

.photo-item:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay i {
    font-size: 1.5rem;
}

.photo-overlay span {
    font-size: 0.875rem;
    font-weight: 600;
}

/* ============ MODAL DE FOTO ============ */
.photo-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 3000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.3s;
}

.photo-modal-container {
    max-width: 1200px;
    width: 100%;
    max-height: 90vh;
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
}

.btn-close-photo {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    font-size: 1.25rem;
}

.btn-close-photo:hover {
    background: white;
    transform: scale(1.1);
}

.photo-modal-header {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.photo-modal-header h5 {
    margin: 0;
    font-size: 1.125rem;
    color: #343a40;
}

.photo-nav {
    display: flex;
    gap: 0.5rem;
}

.btn-nav {
    width: 36px;
    height: 36px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
    background: white;
    color: #495057;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-nav:hover:not(:disabled) {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-nav:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.photo-modal-body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    background: #000;
    overflow: auto;
}

.photo-full {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 0.5rem;
}

.photo-modal-footer {
    padding: 1rem 1.5rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: flex-end;
}

/* ============ BUTTONS ============ */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.25rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover {
    background: #117a8b;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

/* ============ PAGINATION ============ */
.card-footer {
    padding: 1rem 1.5rem;
    background: white;
    border-top: 1px solid #dee2e6;
}

.pagination {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 0.25rem;
}

.page-item {
    display: inline-block;
}

.page-item.disabled .page-link {
    opacity: 0.5;
    pointer-events: none;
}

.page-item.active .page-link {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.page-link {
    padding: 0.5rem 0.75rem;
    color: #007bff;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
}

.page-link:hover:not(.disabled) {
    background: #e9ecef;
}

/* ============ RESPONSIVE ============ */
@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }

    .desktop-table {
        display: none;
    }

    .mobile-cards {
        display: block;
        padding: 1rem;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .detail-item {
        flex-direction: column;
        gap: 0.25rem;
    }

    .detail-item span {
        text-align: left;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer .btn {
        width: 100%;
    }

    .card-footer-mobile .btn {
        font-size: 0.875rem;
    }
    
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 100px), 1fr));
        gap: 0.75rem;
    }
    
    .photo-modal-body {
        padding: 1rem;
    }
    
    .photo-modal-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .map-actions {
        flex-direction: column;
    }

    .map-actions .btn {
        width: 100%;
    }

    .map-section h6 {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 576px) {
    .content-header {
        padding: 1rem 0;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .card-header {
        padding: 1rem;
    }

    .modal-header {
        padding: 1rem;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 1rem;
    }

    .btn-text {
        display: none;
    }

    .card-footer-mobile {
        flex-direction: column;
    }

    .card-footer-mobile .btn {
        width: 100%;
    }

    .map-section {
        padding: 1rem;
    }

    .map-container iframe {
        height: 300px !important;
    }
}
</style>