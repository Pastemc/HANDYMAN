<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('nav.profile') }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Profile Card -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="user-avatar-large mx-auto">
                                    {{ getInitials }}
                                </div>
                            </div>

                            <h3 class="profile-username text-center">{{ user?.name }}</h3>

                            <p class="text-muted text-center">{{ user?.email }}</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>{{ $t('forms.role') }}</b> 
                                    <a class="float-right">
                                        <span class="badge badge-primary">
                                            {{ getRoleName(user?.roles?.[0]?.name) }}
                                        </span>
                                    </a>
                                </li>
                                <li class="list-group-item">
                                    <b>{{ $t('users.register_date') }}</b> 
                                    <a class="float-right">{{ formatDate(user?.created_at) }}</a>
                                </li>
                            </ul>

                            <button @click="showChangePhotoModal = true" class="btn btn-secondary btn-block">
                                <i class="fas fa-camera"></i> {{ $t('profile.change_photo') || 'Cambiar Foto' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('users.personal_info') }}</h3>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="updateProfile">
                                <div class="form-group">
                                    <label>{{ $t('forms.name') }} *</label>
                                    <input 
                                        v-model="profileForm.name"
                                        type="text"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>{{ $t('forms.email') }} *</label>
                                    <input 
                                        v-model="profileForm.email"
                                        type="email"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>{{ $t('forms.phone') }}</label>
                                    <input 
                                        v-model="profileForm.phone"
                                        type="text"
                                        class="form-control"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>{{ $t('forms.address') }}</label>
                                    <input 
                                        v-model="profileForm.address"
                                        type="text"
                                        class="form-control"
                                    >
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ $t('forms.city') }}</label>
                                            <input 
                                                v-model="profileForm.city"
                                                type="text"
                                                class="form-control"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ $t('forms.state') }}</label>
                                            <input 
                                                v-model="profileForm.state"
                                                type="text"
                                                class="form-control"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                                        <i v-else class="fas fa-save"></i>
                                        {{ $t('profile.update_profile') || 'Actualizar Perfil' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('profile.change_password') || 'Cambiar Contraseña' }}</h3>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="changePassword">
                                <div class="form-group">
                                    <label>{{ $t('profile.new_password') || 'Nueva Contraseña' }} *</label>
                                    <input 
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>{{ $t('forms.confirm_password') }} *</label>
                                    <input 
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="form-control"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-danger" :disabled="submittingPassword">
                                        <span v-if="submittingPassword" class="spinner-border spinner-border-sm mr-1"></span>
                                        <i v-else class="fas fa-key"></i>
                                        {{ $t('profile.change_password') || 'Cambiar Contraseña' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'Profile',
    setup() {
        const store = useStore();
        const user = computed(() => store.getters['auth/user']);
        const submitting = ref(false);
        const submittingPassword = ref(false);
        const showChangePhotoModal = ref(false);

        const profileForm = reactive({
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            state: '',
        });

        const passwordForm = reactive({
            password: '',
            password_confirmation: '',
        });

        const getInitials = computed(() => {
            const name = user.value?.name || '';
            return name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2) || '?';
        });

        const loadUserData = () => {
            if (user.value) {
                Object.assign(profileForm, {
                    name: user.value.name || '',
                    email: user.value.email || '',
                    phone: user.value.phone || '',
                    address: user.value.address || '',
                    city: user.value.city || '',
                    state: user.value.state || '',
                });
            }
        };

        const updateProfile = async () => {
            submitting.value = true;
            try {
                const response = await axios.put('/me', profileForm);
                await store.dispatch('auth/fetchUser');
                Swal.fire('Success!', 'Profile updated successfully', 'success');
            } catch (error) {
                Swal.fire('Error', error.response?.data?.message || 'Failed to update profile', 'error');
            } finally {
                submitting.value = false;
            }
        };

        const changePassword = async () => {
            if (passwordForm.password !== passwordForm.password_confirmation) {
                Swal.fire('Error', 'Passwords do not match', 'error');
                return;
            }

            submittingPassword.value = true;
            try {
                await axios.put('/me/password', passwordForm);
                Swal.fire('Success!', 'Password changed successfully', 'success');
                passwordForm.password = '';
                passwordForm.password_confirmation = '';
            } catch (error) {
                Swal.fire('Error', 'Failed to change password', 'error');
            } finally {
                submittingPassword.value = false;
            }
        };

        const getRoleName = (role) => {
            const i18n = window.i18n;
            if (i18n && role) {
                return i18n.t(`users.${role}`);
            }
            return role || '-';
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
            loadUserData();
        });

        return {
            user,
            profileForm,
            passwordForm,
            submitting,
            submittingPassword,
            showChangePhotoModal,
            getInitials,
            updateProfile,
            changePassword,
            getRoleName,
            formatDate,
        };
    },
};
</script>

<style scoped>
.user-avatar-large {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 40px;
}
</style>