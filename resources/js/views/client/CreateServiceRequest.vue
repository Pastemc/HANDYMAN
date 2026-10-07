<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Nueva Solicitud de Servicio</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Completa los datos de tu solicitud</h3>
                </div>

                <form @submit.prevent="submitRequest">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Título *</label>
                                    <input 
                                        v-model="form.title" 
                                        type="text" 
                                        class="form-control"
                                        placeholder="Ej: Reparación de tubería"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Categoría de Servicio *</label>
                                    <select v-model="form.service_category_id" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Descripción *</label>
                            <textarea 
                                v-model="form.description" 
                                class="form-control" 
                                rows="4"
                                placeholder="Describe detalladamente el problema o servicio que necesitas"
                                required
                            ></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Dirección *</label>
                                    <input 
                                        v-model="form.address" 
                                        type="text" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Ciudad *</label>
                                    <input 
                                        v-model="form.city" 
                                        type="text" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Estado *</label>
                                    <input 
                                        v-model="form.state" 
                                        type="text" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Código Postal *</label>
                                    <input 
                                        v-model="form.zip_code" 
                                        type="text" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha Preferida *</label>
                                    <input 
                                        v-model="form.preferred_date" 
                                        type="date" 
                                        class="form-control"
                                        :min="today"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hora Preferida *</label>
                                    <input 
                                        v-model="form.preferred_time" 
                                        type="time" 
                                        class="form-control"
                                        required
                                    >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Prioridad *</label>
                                    <select v-model="form.priority" class="form-control" required>
                                        <option value="low">Baja</option>
                                        <option value="normal" selected>Normal</option>
                                        <option value="high">Alta</option>
                                        <option value="urgent">Urgente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN DE FOTOS -->
                        <div class="form-group">
                            <label>📸 Fotos del Problema (Opcional - Máximo 5)</label>
                            <div class="custom-file">
                                <input 
                                    type="file" 
                                    class="custom-file-input" 
                                    id="photos"
                                    @change="handlePhotoUpload"
                                    accept="image/*"
                                    multiple
                                >
                                <label class="custom-file-label" for="photos">
                                    {{ photoLabel }}
                                </label>
                            </div>
                            <small class="text-muted">Puedes subir hasta 5 fotos (JPG, PNG, máx 5MB cada una)</small>
                        </div>

                        <!-- PREVIEW DE FOTOS -->
                        <div v-if="photoPreviews.length > 0" class="row mt-3">
                            <div v-for="(preview, index) in photoPreviews" :key="index" class="col-6 col-md-2 mb-3">
                                <div class="position-relative">
                                    <img :src="preview" class="img-thumbnail" style="width: 100%; height: 120px; object-fit: cover;">
                                    <button 
                                        type="button"
                                        @click="removePhoto(index)"
                                        class="btn btn-danger btn-sm position-absolute"
                                        style="top: 5px; right: 5px; padding: 2px 6px;"
                                    >
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Nota:</strong> Una vez enviada la solicitud, un administrador la revisará y asignará un handyman disponible.
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-6">
                                <button 
                                    type="button" 
                                    @click="$router.back()" 
                                    class="btn btn-secondary btn-block"
                                >
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </button>
                            </div>
                            <div class="col-6">
                                <button 
                                    type="submit" 
                                    class="btn btn-primary btn-block"
                                    :disabled="submitting"
                                >
                                    <span v-if="submitting" class="spinner-border spinner-border-sm mr-2"></span>
                                    <i v-else class="fas fa-paper-plane"></i>
                                    {{ submitting ? 'Enviando...' : 'Enviar Solicitud' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'CreateServiceRequest',
    setup() {
        const router = useRouter();
        const categories = ref([]);
        const submitting = ref(false);
        const photos = ref([]);
        const photoPreviews = ref([]);

        const form = reactive({
            title: '',
            description: '',
            service_category_id: '',
            address: '',
            city: '',
            state: '',
            zip_code: '',
            preferred_date: '',
            preferred_time: '',
            priority: 'normal',
        });

        const today = computed(() => {
            const date = new Date();
            return date.toISOString().split('T')[0];
        });

        const photoLabel = computed(() => {
            if (photos.value.length === 0) return 'Seleccionar fotos...';
            if (photos.value.length === 1) return '1 foto seleccionada';
            return `${photos.value.length} fotos seleccionadas`;
        });

        const fetchCategories = async () => {
            try {
                const response = await axios.get('/service-categories');
                categories.value = response.data;
            } catch (error) {
                console.error('Error:', error);
            }
        };

        const handlePhotoUpload = (event) => {
            const files = Array.from(event.target.files);
            
            // Validar máximo 5 fotos
            if (files.length > 5) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Máximo 5 fotos',
                    text: 'Solo puedes subir hasta 5 fotos',
                });
                event.target.value = '';
                return;
            }

            // Validar tamaño (máx 5MB por foto)
            const maxSize = 5 * 1024 * 1024; // 5MB
            const invalidFiles = files.filter(file => file.size > maxSize);
            
            if (invalidFiles.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Archivos muy grandes',
                    text: 'Cada foto debe pesar máximo 5MB',
                });
                event.target.value = '';
                return;
            }

            // Guardar archivos
            photos.value = files;

            // Crear previews
            photoPreviews.value = [];
            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreviews.value.push(e.target.result);
                };
                reader.readAsDataURL(file);
            });
        };

        const removePhoto = (index) => {
            const filesArray = Array.from(photos.value);
            filesArray.splice(index, 1);
            photos.value = filesArray;
            photoPreviews.value.splice(index, 1);
        };

        const submitRequest = async () => {
            submitting.value = true;

            try {
                const formData = new FormData();
                
                // Agregar campos del formulario
                Object.keys(form).forEach(key => {
                    formData.append(key, form[key]);
                });

                // Agregar fotos
                photos.value.forEach((photo, index) => {
                    formData.append(`photos[${index}]`, photo);
                });

                const response = await axios.post('/service-requests', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Solicitud Enviada!',
                    text: 'Tu solicitud ha sido creada exitosamente',
                    timer: 2000,
                });

                router.push('/dashboard/client/my-requests');

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al crear la solicitud',
                });
            } finally {
                submitting.value = false;
            }
        };

        onMounted(() => {
            fetchCategories();
        });

        return {
            categories,
            form,
            submitting,
            photos,
            photoPreviews,
            today,
            photoLabel,
            fetchCategories,
            handlePhotoUpload,
            removePhoto,
            submitRequest,
        };
    },
};
</script>

<style scoped>
.custom-file-label::after {
    content: "Buscar";
}
</style>