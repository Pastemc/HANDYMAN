<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('categories.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="showCreateModal = true" class="btn btn-primary float-right">
                        <i class="fas fa-plus"></i> {{ $t('categories.new_category') }}
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
                        <div class="col-md-4">
                            <input 
                                v-model="filters.search"
                                @keyup.enter="fetchCategories"
                                type="text" 
                                class="form-control" 
                                :placeholder="$t('buttons.search') + '...'"
                            >
                        </div>
                        <div class="col-md-2">
                            <button @click="fetchCategories" class="btn btn-primary">
                                <i class="fas fa-search"></i> {{ $t('buttons.search') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ $t('categories.images') }}</th>
                                <th>{{ $t('categories.category_name') }}</th>
                                <th>{{ $t('categories.category_description') }}</th>
                                <th>{{ $t('users.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="category in categories" :key="category.id">
                                <td>{{ category.id }}</td>
                                <td>
                                    <div v-if="category.images_urls && category.images_urls.length > 0" class="d-flex flex-wrap">
                                        <img 
                                            v-for="(imageUrl, index) in category.images_urls.slice(0, 3)" 
                                            :key="index"
                                            :src="'http://127.0.0.1:8000' + imageUrl" 
                                            alt="Category Image"
                                            class="img-thumbnail mr-1 mb-1"
                                            style="width: 60px; height: 60px; object-fit: cover;"
                                        >
                                        <span v-if="category.images_urls.length > 3" class="badge badge-info align-self-center">
                                            +{{ category.images_urls.length - 3 }}
                                        </span>
                                    </div>
                                    <i v-else class="fas fa-image fa-2x text-muted"></i>
                                </td>
                                <td>{{ category.name }}</td>
                                <td>{{ category.description }}</td>
                                <td>
                                    <button @click="editCategory(category)" class="btn btn-sm btn-info" :title="$t('buttons.edit')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button 
                                        @click="deleteCategory(category.id)"
                                        class="btn btn-sm btn-danger ml-1"
                                        :title="$t('buttons.delete')"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0 && !loading">
                                <td colspan="5" class="text-center">{{ $t('messages.no_data') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="modal fade show modal-backdrop-custom" style="display: block;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">
                        {{ showEditModal ? $t('categories.edit_category') : $t('categories.new_category') }}
                    </h4>
                    <button type="button" class="close" @click="closeModals">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveCategory">
                        <div class="form-group">
                            <label>{{ $t('categories.category_name') }} *</label>
                            <input 
                                v-model="categoryForm.name"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.name }"
                                placeholder="Ej: Pintura y acabados"
                                required
                            >
                            <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('categories.category_description') }}</label>
                            <textarea 
                                v-model="categoryForm.description"
                                class="form-control"
                                rows="3"
                                :class="{ 'is-invalid': errors.description }"
                                placeholder="Describe los servicios incluidos en esta categoría..."
                            ></textarea>
                            <div v-if="errors.description" class="invalid-feedback">{{ errors.description[0] }}</div>
                        </div>

                        <div class="form-group">
                            <label>{{ $t('categories.images') }}</label>
                            <div class="custom-file">
                                <input 
                                    type="file"
                                    @change="handleImagesUpload"
                                    accept="image/*"
                                    multiple
                                    class="custom-file-input"
                                    id="categoryImages"
                                >
                                <label class="custom-file-label" for="categoryImages">
                                    Elegir archivos...
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Formatos: JPG, PNG, GIF, WEBP. Máximo 10MB por imagen. Puedes subir múltiples imágenes.
                            </small>
                            
                            <!-- Preview de imágenes existentes (modo edición) -->
                            <div v-if="showEditModal && existingImages.length > 0" class="mt-3">
                                <h6><i class="fas fa-images"></i> Imágenes Actuales:</h6>
                                <div class="images-preview-grid">
                                    <div v-for="(imageUrl, index) in existingImages" :key="'existing-' + index" class="image-preview-item">
                                        <img :src="'http://127.0.0.1:8000' + imageUrl" class="img-thumbnail">
                                        <button 
                                            type="button"
                                            @click="removeExistingImage(index)" 
                                            class="btn btn-danger btn-sm btn-remove-image"
                                            title="Eliminar imagen"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview de nuevas imágenes -->
                            <div v-if="imagePreviews.length > 0" class="mt-3">
                                <h6><i class="fas fa-plus-circle text-success"></i> Nuevas Imágenes:</h6>
                                <div class="images-preview-grid">
                                    <div v-for="(preview, index) in imagePreviews" :key="'new-' + index" class="image-preview-item">
                                        <img :src="preview" class="img-thumbnail">
                                        <button 
                                            type="button"
                                            @click="removeNewImage(index)" 
                                            class="btn btn-danger btn-sm btn-remove-image"
                                            title="Eliminar imagen"
                                        >
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeModals">
                        <i class="fas fa-times"></i> {{ $t('buttons.cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary" @click="saveCategory" :disabled="submitting">
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="fas fa-save"></i>
                        {{ $t('buttons.save') }}
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
    name: 'ServiceCategoryManagement',
    setup() {
        const categories = ref([]);
        const loading = ref(false);
        const showCreateModal = ref(false);
        const showEditModal = ref(false);
        const submitting = ref(false);
        const errors = ref({});
        const imagePreviews = ref([]);
        const selectedImages = ref([]);
        const existingImages = ref([]);
        const existingImagePaths = ref([]);

        const filters = reactive({
            search: '',
        });

        const categoryForm = reactive({
            id: null,
            name: '',
            description: '',
        });

        const fetchCategories = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/service-categories', { params: filters });
                categories.value = response.data;
            } catch (error) {
                console.error('Error fetching categories:', error);
            } finally {
                loading.value = false;
            }
        };

        const handleImagesUpload = (event) => {
            const files = Array.from(event.target.files);
            
            files.forEach(file => {
                if (file.size > 10 * 1024 * 1024) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: `${file.name} supera los 10MB`,
                    });
                    return;
                }

                selectedImages.value.push(file);

                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreviews.value.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });

            // Actualizar label del input file
            const fileNames = files.map(f => f.name).join(', ');
            event.target.nextElementSibling.textContent = fileNames || 'Elegir archivos...';
        };

        const removeNewImage = (index) => {
            selectedImages.value.splice(index, 1);
            imagePreviews.value.splice(index, 1);
        };

        const removeExistingImage = (index) => {
            existingImages.value.splice(index, 1);
            existingImagePaths.value.splice(index, 1);
        };

        const editCategory = (category) => {
            Object.assign(categoryForm, {
                id: category.id,
                name: category.name,
                description: category.description,
            });
            
            existingImages.value = [...(category.images_urls || [])];
            existingImagePaths.value = [...(category.images || [])];
            imagePreviews.value = [];
            selectedImages.value = [];
            showEditModal.value = true;
        };

        const saveCategory = async () => {
            errors.value = {};
            submitting.value = true;

            try {
                const formData = new FormData();
                formData.append('name', categoryForm.name);
                formData.append('description', categoryForm.description || '');
                
                // Agregar nuevas imágenes
                selectedImages.value.forEach((file, index) => {
                    formData.append(`images[${index}]`, file);
                });

                // Agregar imágenes existentes a mantener (solo en modo edición)
                if (showEditModal.value) {
                    existingImagePaths.value.forEach((path, index) => {
                        formData.append(`keep_images[${index}]`, path);
                    });
                }

                if (showEditModal.value) {
                    formData.append('_method', 'PUT');
                    await axios.post(`/service-categories/${categoryForm.id}`, formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                } else {
                    await axios.post('/service-categories', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                }

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Categoría guardada correctamente',
                    timer: 2000,
                    showConfirmButton: false,
                });
                closeModals();
                fetchCategories();
            } catch (error) {
                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors || {};
                } else {
                    Swal.fire('Error', 'No se pudo guardar la categoría', 'error');
                }
            } finally {
                submitting.value = false;
            }
        };

        const deleteCategory = async (id) => {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/service-categories/${id}`);
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'Categoría eliminada correctamente',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                    fetchCategories();
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'No se pudo eliminar la categoría', 'error');
                }
            }
        };

        const closeModals = () => {
            showCreateModal.value = false;
            showEditModal.value = false;
            Object.assign(categoryForm, {
                id: null,
                name: '',
                description: '',
            });
            errors.value = {};
            imagePreviews.value = [];
            selectedImages.value = [];
            existingImages.value = [];
            existingImagePaths.value = [];
        };

        onMounted(() => {
            fetchCategories();
        });

        return {
            categories,
            loading,
            filters,
            showCreateModal,
            showEditModal,
            submitting,
            categoryForm,
            errors,
            imagePreviews,
            existingImages,
            fetchCategories,
            handleImagesUpload,
            removeNewImage,
            removeExistingImage,
            editCategory,
            saveCategory,
            deleteCategory,
            closeModals,
        };
    },
};
</script>

<style scoped>
/* ========== MODAL BACKDROP ========== */
.modal-backdrop-custom {
    background-color: rgba(0, 0, 0, 0.5);
}

/* ========== MODAL ========== */
.modal-dialog {
    max-width: 900px;
}

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.modal-content {
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}

.modal-header .close {
    color: white;
    opacity: 1;
    text-shadow: none;
}

.modal-header .close:hover {
    opacity: 0.8;
}

.modal-title {
    font-weight: 600;
}

/* ========== GRID DE IMÁGENES ========== */
.images-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.image-preview-item .img-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.image-preview-item:hover .img-thumbnail {
    transform: scale(1.05);
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.btn-remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 28px;
    height: 28px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
}

.image-preview-item:hover .btn-remove-image {
    opacity: 1;
}

/* ========== CUSTOM FILE INPUT ========== */
.custom-file-label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.custom-file-label::after {
    content: "Buscar";
}

/* ========== FORM STYLING ========== */
.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* ========== BOTONES ========== */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

/* ========== RESPONSIVE MOBILE ========== */
@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
        max-width: calc(100% - 20px);
    }

    .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 150px);
    }

    .images-preview-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer .btn {
        width: 100%;
        margin-bottom: 10px;
    }

    .modal-footer .btn:last-child {
        margin-bottom: 0;
    }
}

@media (max-width: 576px) {
    .modal-dialog {
        margin: 5px;
        max-width: calc(100% - 10px);
    }

    .modal-header {
        padding: 12px;
    }

    .modal-title {
        font-size: 1.1rem;
    }

    .modal-body {
        padding: 15px;
    }

    .images-preview-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .form-group {
        margin-bottom: 15px;
    }
}

/* ========== SCROLL PERSONALIZADO ========== */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ========== ANIMACIONES ========== */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.modal-content {
    animation: fadeIn 0.3s ease-out;
}

/* ========== MEJORAS VISUALES ========== */
h6 {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

.form-text {
    display: flex;
    align-items: center;
    gap: 5px;
}

.badge-info {
    font-size: 0.75rem;
    padding: 4px 8px;
}
</style>