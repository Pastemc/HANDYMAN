<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Handymen</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="showCreateModal = true" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> 
                        <span class="d-none d-sm-inline">New Handyman</span>
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
                        <div class="col-12 col-md-4 mb-2 mb-md-0">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchHandymen"
                                type="text" 
                                class="form-control" 
                                placeholder="Search..."
                            >
                        </div>
                        <div class="col-12 col-md-3 mb-2 mb-md-0">
                            <select v-model="filters.approval_status" @change="fetchHandymen" class="form-control">
                                <option value="">All</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <button @click="fetchHandymen" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop table view -->
                <div class="card-body table-responsive p-0 d-none d-md-block" style="max-height: 600px; overflow-y: auto;">
                    <table class="table table-hover table-striped">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Years of Experience</th>
                                <th>Total Jobs</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="handyman in handymen" :key="handyman.id">
                                <td>{{ handyman.id }}</td>
                                <td>{{ handyman.user?.name }}</td>
                                <td>{{ handyman.user?.email }}</td>
                                <td>{{ handyman.years_experience }} years</td>
                                <td>{{ handyman.total_jobs }}</td>
                                <td>
                                    <span class="badge badge-warning">
                                        {{ handyman.rating }} <i class="fas fa-star"></i>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="getApprovalBadge(handyman.approval_status)">
                                        {{ getApprovalText(handyman.approval_status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button @click="viewDetail(handyman)" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button 
                                            v-if="handyman.approval_status === 'pending'"
                                            @click="approveHandyman(handyman.id)"
                                            class="btn btn-sm btn-success"
                                            title="Approve"
                                        >
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button 
                                            v-if="handyman.approval_status === 'pending'"
                                            @click="rejectHandyman(handyman.id)"
                                            class="btn btn-sm btn-danger"
                                            title="Reject"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="handymen.length === 0 && !loading">
                                <td colspan="8" class="text-center">Registered Handymen: 0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile card view -->
                <div class="card-body d-md-none">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="handymen.length === 0" class="text-center py-4">
                        <p class="text-muted">Registered Handymen: 0</p>
                    </div>
                    <div v-else class="handyman-cards">
                        <div v-for="handyman in handymen" :key="handyman.id" class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">{{ handyman.user?.name }}</h5>
                                    <span class="badge" :class="getApprovalBadge(handyman.approval_status)">
                                        {{ getApprovalText(handyman.approval_status) }}
                                    </span>
                                </div>
                                <p class="card-text small text-muted mb-2">
                                    <i class="fas fa-envelope"></i> {{ handyman.user?.email }}
                                </p>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <small class="text-muted">Years of Experience:</small><br>
                                        <strong>{{ handyman.years_experience }} years</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total Jobs:</small><br>
                                        <strong>{{ handyman.total_jobs }}</strong>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Rating:</small>
                                    <span class="badge badge-warning ml-2">
                                        {{ handyman.rating }} <i class="fas fa-star"></i>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button @click="viewDetail(handyman)" class="btn btn-sm btn-info mr-1">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button 
                                        v-if="handyman.approval_status === 'pending'"
                                        @click="approveHandyman(handyman.id)"
                                        class="btn btn-sm btn-success mr-1"
                                    >
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button 
                                        v-if="handyman.approval_status === 'pending'"
                                        @click="rejectHandyman(handyman.id)"
                                        class="btn btn-sm btn-danger"
                                    >
                                        <i class="fas fa-times"></i>
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

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="modal fade show modal-backdrop-custom" @click.self="closeModals">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">New Handyman</h4>
                    <button type="button" class="close" @click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveHandyman">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Full Name *</label>
                                    <input 
                                        v-model="handymanForm.name"
                                        type="text"
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input 
                                        v-model="handymanForm.email"
                                        type="email"
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Password *</label>
                                    <input 
                                        v-model="handymanForm.password"
                                        type="password"
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input 
                                        v-model="handymanForm.phone"
                                        type="text"
                                        class="form-control"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Bio</label>
                            <textarea 
                                v-model="handymanForm.bio"
                                class="form-control"
                                rows="3"
                            ></textarea>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Years of Experience</label>
                                    <input 
                                        v-model="handymanForm.years_experience"
                                        type="number"
                                        class="form-control"
                                    >
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Certification</label>
                                    <input 
                                        v-model="handymanForm.certification"
                                        type="text"
                                        class="form-control"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Service Categories</label>
                            <div class="row">
                                <div v-for="category in categories" :key="category.id" class="col-12 col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input 
                                            :id="`cat-${category.id}`"
                                            v-model="handymanForm.service_categories"
                                            :value="category.id"
                                            type="checkbox"
                                            class="custom-control-input"
                                        >
                                        <label :for="`cat-${category.id}`" class="custom-control-label">
                                            {{ category.name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeModals">Cancel</button>
                    <button type="button" class="btn btn-primary" @click="saveHandyman" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="selectedHandyman" class="modal fade show modal-backdrop-custom" @click.self="selectedHandyman = null">
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Handyman Details</h4>
                    <button type="button" class="close" @click="selectedHandyman = null">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <h5>Personal Information</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Name:</dt>
                                <dd class="col-sm-8">{{ selectedHandyman.user?.name }}</dd>

                                <dt class="col-sm-4">Email:</dt>
                                <dd class="col-sm-8 text-break">{{ selectedHandyman.user?.email }}</dd>

                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">{{ selectedHandyman.user?.phone }}</dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge" :class="getApprovalBadge(selectedHandyman.approval_status)">
                                        {{ getApprovalText(selectedHandyman.approval_status) }}
                                    </span>
                                </dd>
                            </dl>
                        </div>

                        <div class="col-12 col-md-6">
                            <h5>Professional Information</h5>
                            <dl class="row">
                                <dt class="col-sm-5">Years of Experience:</dt>
                                <dd class="col-sm-7">{{ selectedHandyman.years_experience }} years</dd>

                                <dt class="col-sm-5">Certification:</dt>
                                <dd class="col-sm-7">{{ selectedHandyman.certification || 'N/A' }}</dd>

                                <dt class="col-sm-5">Rating:</dt>
                                <dd class="col-sm-7">
                                    {{ selectedHandyman.rating }} <i class="fas fa-star text-warning"></i>
                                </dd>

                                <dt class="col-sm-5">Total Jobs:</dt>
                                <dd class="col-sm-7">{{ selectedHandyman.total_jobs }}</dd>
                            </dl>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Bio</h5>
                            <p>{{ selectedHandyman.bio || 'No data' }}</p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Services Offered</h5>
                            <div class="d-flex flex-wrap">
                                <span 
                                    v-for="category in selectedHandyman.service_categories" 
                                    :key="category.id"
                                    class="badge badge-info mr-1 mb-1"
                                >
                                    {{ category.name }}
                                </span>
                            </div>
                            <span v-if="!selectedHandyman.service_categories || selectedHandyman.service_categories.length === 0">
                                No data
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-column flex-sm-row">
                    <div class="mb-2 mb-sm-0">
                        <button 
                            v-if="selectedHandyman.approval_status === 'pending'"
                            @click="approveHandyman(selectedHandyman.id)"
                            class="btn btn-success mr-2"
                        >
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button 
                            v-if="selectedHandyman.approval_status === 'pending'"
                            @click="rejectHandyman(selectedHandyman.id)"
                            class="btn btn-danger"
                        >
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" @click="selectedHandyman = null">
                        Close
                    </button>
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
    name: 'HandymanManagement',
    setup() {
        const handymen = ref([]);
        const categories = ref([]);
        const loading = ref(false);
        const selectedHandyman = ref(null);
        const showCreateModal = ref(false);
        const submitting = ref(false);

        const filters = reactive({
            search: '',
            approval_status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const handymanForm = reactive({
            name: '',
            email: '',
            password: '',
            phone: '',
            bio: '',
            years_experience: '',
            certification: '',
            service_categories: [],
        });

        const fetchHandymen = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/handymen', { params: filters });
                handymen.value = response.data.data;
                pagination.value = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                };
            } catch (error) {
                console.error('Error fetching handymen:', error);
            } finally {
                loading.value = false;
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

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchHandymen();
            }
        };

        const viewDetail = (handyman) => {
            selectedHandyman.value = handyman;
        };

        const saveHandyman = async () => {
            submitting.value = true;
            try {
                await axios.post('/handymen', handymanForm);
                Swal.fire('Success!', 'Handyman created successfully', 'success');
                closeModals();
                fetchHandymen();
            } catch (error) {
                Swal.fire('Error', error.response?.data?.message || 'Failed to create handyman', 'error');
            } finally {
                submitting.value = false;
            }
        };

        const approveHandyman = async (id) => {
            try {
                await axios.post(`/handymen/${id}/approve`);
                Swal.fire('Approved!', 'Handyman has been approved', 'success');
                selectedHandyman.value = null;
                fetchHandymen();
            } catch (error) {
                Swal.fire('Error', 'Failed to approve handyman', 'error');
            }
        };

        const rejectHandyman = async (id) => {
            const result = await Swal.fire({
                title: 'Reject Handyman?',
                text: "This will notify the user",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reject',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.post(`/handymen/${id}/reject`);
                    Swal.fire('Rejected', 'Handyman has been rejected', 'success');
                    selectedHandyman.value = null;
                    fetchHandymen();
                } catch (error) {
                    Swal.fire('Error', 'Failed to reject handyman', 'error');
                }
            }
        };

        const closeModals = () => {
            showCreateModal.value = false;
            Object.assign(handymanForm, {
                name: '',
                email: '',
                password: '',
                phone: '',
                bio: '',
                years_experience: '',
                certification: '',
                service_categories: [],
            });
        };

        const getApprovalBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                approved: 'badge-success',
                rejected: 'badge-danger',
            };
            return badges[status] || 'badge-secondary';
        };

        const getApprovalText = (status) => {
            const texts = {
                pending: 'Pending',
                approved: 'Approved',
                rejected: 'Rejected',
            };
            return texts[status] || status;
        };

        onMounted(() => {
            fetchHandymen();
            fetchCategories();
        });

        return {
            handymen,
            categories,
            loading,
            filters,
            pagination,
            selectedHandyman,
            showCreateModal,
            submitting,
            handymanForm,
            fetchHandymen,
            changePage,
            viewDetail,
            saveHandyman,
            approveHandyman,
            rejectHandyman,
            closeModals,
            getApprovalBadge,
            getApprovalText,
        };
    },
};
</script>

<style scoped>
.table-responsive {
    -webkit-overflow-scrolling: touch;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.modal-backdrop-custom {
    display: block;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
    overflow-y: auto;
    padding: 15px;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 30px);
}

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.handyman-cards {
    max-height: 600px;
    overflow-y: auto;
}

.handyman-cards::-webkit-scrollbar {
    width: 6px;
}

.handyman-cards::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.handyman-cards::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.text-break {
    word-break: break-word;
    overflow-wrap: break-word;
}

@media (max-width: 767.98px) {
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .btn {
        font-size: 0.875rem;
    }
    
    .modal-dialog {
        margin: 0;
        max-width: 100%;
    }
    
    .modal-content {
        border-radius: 0;
    }
    
    .modal-footer {
        padding: 0.75rem;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .modal-footer .btn:last-child {
        margin-bottom: 0;
    }
}

@media (min-width: 768px) {
    .modal-dialog-scrollable {
        max-height: calc(100vh - 60px);
    }
}

.badge {
    font-size: 0.85em;
    padding: 0.35em 0.65em;
}

.btn-group {
    white-space: nowrap;
}

@media (max-width: 575.98px) {
    .float-right {
        float: none !important;
        width: 100%;
    }
}
</style>