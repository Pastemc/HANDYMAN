<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Users</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="showCreateModal = true" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> <span class="d-none d-sm-inline">New User</span>
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
                                @keyup.enter="fetchUsers"
                                type="text" 
                                class="form-control form-control-sm" 
                                placeholder="Search..."
                            >
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                            <select v-model="filters.role" @change="fetchUsers" class="form-control form-control-sm">
                                <option value="">All Roles</option>
                                <option value="client">Client</option>
                                <option value="handyman">Handyman</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                            <select v-model="filters.status" @change="fetchUsers" class="form-control form-control-sm">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <button @click="fetchUsers" class="btn btn-primary btn-sm btn-block">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="card-body table-responsive p-0 d-none d-lg-block">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.id }}</td>
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span class="badge badge-primary badge-sm">
                                        {{ user.roles && user.roles.length > 0 ? user.roles[0].display_name : 'No Data' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-sm" :class="getStatusBadge(user.status)">
                                        {{ getStatusText(user.status) }}
                                    </span>
                                </td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button @click="editUser(user)" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            @click="deleteUser(user.id)"
                                            class="btn btn-sm btn-danger"
                                            :disabled="user.roles && user.roles[0] && user.roles[0].name === 'superadmin'"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0 && !loading">
                                <td colspan="7" class="text-center">No Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tablet Table View -->
                <div class="card-body table-responsive p-0 d-none d-md-block d-lg-none">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span class="badge badge-primary badge-sm">
                                        {{ user.roles && user.roles.length > 0 ? user.roles[0].display_name : 'No Data' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-sm" :class="getStatusBadge(user.status)">
                                        {{ getStatusText(user.status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button @click="editUser(user)" class="btn btn-sm btn-info" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button 
                                            @click="deleteUser(user.id)"
                                            class="btn btn-sm btn-danger"
                                            :disabled="user.roles && user.roles[0] && user.roles[0].name === 'superadmin'"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0 && !loading">
                                <td colspan="5" class="text-center">No Data</td>
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
                    <div v-else-if="users.length === 0" class="text-center py-4">
                        <p class="text-muted">No Data</p>
                    </div>
                    <div v-else class="user-cards">
                        <div v-for="user in users" :key="user.id" class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0 font-weight-bold">{{ user.name }}</h6>
                                    <span class="badge badge-sm" :class="getStatusBadge(user.status)">
                                        {{ getStatusText(user.status) }}
                                    </span>
                                </div>
                                <p class="card-text small text-muted mb-2">
                                    <i class="fas fa-envelope"></i> {{ user.email }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge badge-primary">
                                        {{ user.roles && user.roles.length > 0 ? user.roles[0].display_name : 'No Data' }}
                                    </span>
                                    <small class="text-muted">{{ formatDate(user.created_at) }}</small>
                                </div>
                                <div class="d-flex justify-content-end gap-2">
                                    <button @click="editUser(user)" class="btn btn-sm btn-info mr-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button 
                                        @click="deleteUser(user.id)"
                                        class="btn btn-sm btn-danger"
                                        :disabled="user.roles && user.roles[0] && user.roles[0].name === 'superadmin'"
                                    >
                                        <i class="fas fa-trash"></i> Delete
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

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal fade show modal-backdrop-custom" @click.self="closeModals">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-dialog-custom">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ showEditModal ? 'Edit User' : 'New User' }}</h5>
                    <button type="button" class="close" @click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveUser">
                        <div class="form-group">
                            <label>Name *</label>
                            <input 
                                v-model="userForm.name"
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': errors.name }"
                                required
                            >
                            <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input 
                                v-model="userForm.email"
                                type="email"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': errors.email }"
                                required
                            >
                            <div v-if="errors.email" class="invalid-feedback">{{ errors.email[0] }}</div>
                        </div>

                        <div class="form-group" v-if="!showEditModal">
                            <label>Password *</label>
                            <div class="input-group input-group-sm">
                                <input 
                                    v-model="userForm.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.password }"
                                    required
                                >
                                <div class="input-group-append">
                                    <button 
                                        class="btn btn-outline-secondary" 
                                        type="button"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-if="errors.password" class="invalid-feedback d-block">{{ errors.password[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>Phone</label>
                            <input 
                                v-model="userForm.phone"
                                type="text"
                                class="form-control form-control-sm"
                            >
                        </div>

                        <div class="form-group" v-if="!showEditModal">
                            <label>Role *</label>
                            <select v-model="userForm.role" class="form-control form-control-sm" required>
                                <option value="">Select</option>
                                <option value="client">Client</option>
                                <option value="handyman">Handyman</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label>Status</label>
                            <select v-model="userForm.status" class="form-control form-control-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="closeModals">Cancel</button>
                    <button type="button" class="btn btn-primary btn-sm" @click="saveUser" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        Save
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
    name: 'UserManagement',
    setup() {
        const users = ref([]);
        const loading = ref(false);
        const showCreateModal = ref(false);
        const showEditModal = ref(false);
        const submitting = ref(false);
        const showPassword = ref(false);
        const errors = ref({});

        const filters = reactive({
            search: '',
            role: '',
            status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const userForm = reactive({
            id: null,
            name: '',
            email: '',
            password: '',
            phone: '',
            role: '',
            status: 'active',
        });

        const fetchUsers = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/users', { params: filters });
                users.value = response.data.data;
                pagination.value = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                };
            } catch (error) {
                console.error('Error fetching users:', error);
            } finally {
                loading.value = false;
            }
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchUsers();
            }
        };

        const editUser = (user) => {
            Object.assign(userForm, {
                id: user.id,
                name: user.name,
                email: user.email,
                phone: user.phone,
                status: user.status,
            });
            showEditModal.value = true;
        };

        const saveUser = async () => {
            errors.value = {};
            submitting.value = true;

            try {
                if (showEditModal.value) {
                    await axios.put(`/users/${userForm.id}`, userForm);
                } else {
                    await axios.post('/users', userForm);
                }

                Swal.fire('Success!', 'User saved successfully', 'success');
                closeModals();
                fetchUsers();
            } catch (error) {
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors || {};
                } else {
                    Swal.fire('Error', 'Failed to save user', 'error');
                }
            } finally {
                submitting.value = false;
            }
        };

        const deleteUser = async (id) => {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/users/${id}`);
                    Swal.fire('Deleted', 'User deleted successfully', 'success');
                    fetchUsers();
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'Failed to delete user', 'error');
                }
            }
        };

        const closeModals = () => {
            showCreateModal.value = false;
            showEditModal.value = false;
            showPassword.value = false;
            Object.assign(userForm, {
                id: null,
                name: '',
                email: '',
                password: '',
                phone: '',
                role: '',
                status: 'active',
            });
            errors.value = {};
        };

        const getStatusBadge = (status) => {
            const badges = {
                active: 'badge-success',
                inactive: 'badge-secondary',
                suspended: 'badge-danger',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                active: 'Active',
                inactive: 'Inactive',
                suspended: 'Suspended',
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
            fetchUsers();
        });

        return {
            users,
            loading,
            filters,
            pagination,
            showCreateModal,
            showEditModal,
            submitting,
            showPassword,
            userForm,
            errors,
            fetchUsers,
            changePage,
            editUser,
            saveUser,
            deleteUser,
            closeModals,
            getStatusBadge,
            getStatusText,
            formatDate,
        };
    },
};
</script>

<style scoped>
/* ============ MODAL RESPONSIVE ============ */
.modal-backdrop-custom {
    display: flex !important;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.8);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    overflow: hidden;
}

.modal-dialog-custom {
    margin: 1rem;
    max-width: 95%;
    width: 100%;
    max-height: calc(100vh - 2rem);
}

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* ============ TABLE RESPONSIVE ============ */
.table-responsive {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
    overflow-x: auto;
}

/* ============ USER CARDS (MOBILE) ============ */
.user-cards {
    max-height: calc(100vh - 350px);
    overflow-y: auto;
}

.user-cards::-webkit-scrollbar {
    width: 6px;
}

.user-cards::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.user-cards::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

/* ============ BADGES ============ */
.badge-sm {
    font-size: 0.75rem;
    padding: 0.25em 0.5em;
}

/* ============ BUTTONS ============ */
.btn-group {
    display: flex;
    gap: 2px;
}

.input-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

/* ============ TABLETS (768px - 991px) ============ */
@media (min-width: 768px) {
    .modal-dialog-custom {
        max-width: 600px;
    }
    
    .table-responsive {
        max-height: calc(100vh - 280px);
    }
}

/* ============ LAPTOPS (992px - 1199px) ============ */
@media (min-width: 992px) {
    .modal-dialog-custom {
        max-width: 650px;
    }
}

/* ============ DESKTOPS (1200px+) ============ */
@media (min-width: 1200px) {
    .modal-dialog-custom {
        max-width: 700px;
    }
}

/* ============ MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .modal-dialog-custom {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
    
    .modal-header h5,
    .modal-title {
        font-size: 1rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .form-control-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    
    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 150px);
    }
    
    .table-responsive {
        max-height: calc(100vh - 250px);
    }
    
    .table-sm td,
    .table-sm th {
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
    }
    
    .badge-sm {
        font-size: 0.7rem;
        padding: 0.2em 0.4em;
    }
    
    .user-cards {
        max-height: calc(100vh - 300px);
    }
    
    .card-title {
        font-size: 0.95rem !important;
    }
    
    .card-text {
        font-size: 0.85rem !important;
    }
}

/* ============ SCROLLBAR STYLING ============ */
.modal-body::-webkit-scrollbar,
.table-responsive::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.modal-body::-webkit-scrollbar-track,
.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.modal-body::-webkit-scrollbar-thumb,
.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover,
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ============ PREVENT BODY SCROLL ============ */
body.modal-open {
    overflow: hidden;
}

/* ============ MOBILE VISIBILITY ============ */
@media (max-width: 767px) {
    .table {
        font-size: 0.875rem;
    }
    
    small.text-muted {
        font-size: 0.75rem;
    }
    
    .float-right {
        float: none !important;
        width: 100%;
    }
}

/* ============ FORM GROUPS ============ */
.form-group {
    margin-bottom: 1rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

/* ============ MODAL FOOTER ============ */
.modal-footer {
    padding: 0.75rem;
}

.modal-footer .btn {
    min-width: 70px;
}

@media (max-width: 576px) {
    .modal-footer {
        padding: 0.5rem;
        flex-direction: column;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin-bottom: 0.5rem;
        min-width: unset;
    }
    
    .modal-footer .btn:last-child {
        margin-bottom: 0;
    }
}

/* ============ INPUT GROUPS ============ */
@media (max-width: 576px) {
    .input-group-append .btn {
        padding: 0.25rem 0.5rem;
    }
}

/* ============ ERROR MESSAGES ============ */
.invalid-feedback {
    font-size: 0.875rem;
}

@media (max-width: 576px) {
    .invalid-feedback {
        font-size: 0.75rem;
    }
}

/* ============ CARD IMPROVEMENTS ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

/* ============ PAGINATION ============ */
.pagination {
    margin: 0;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

@media (max-width: 576px) {
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* ============ BUTTON GROUPS ============ */
.btn-group .btn {
    border-radius: 0.25rem;
}

.btn-group .btn:not(:last-child) {
    margin-right: 2px;
}

/* ============ LOADING STATE ============ */
.spinner-border {
    width: 2rem;
    height: 2rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* ============ UTILITIES ============ */
.gap-2 {
    gap: 0.5rem;
}

.text-break {
    word-break: break-word;
    overflow-wrap: break-word;
}
</style>