<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Professional Profile</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Profile Card -->
                <div class="col-lg-4 col-md-5 mb-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body box-profile">
                            <div class="text-center">
                                <div class="user-avatar-large mx-auto">
                                    {{ getInitials }}
                                </div>
                            </div>

                            <h3 class="profile-username text-center">{{ profile?.user?.name }}</h3>
                            <p class="text-muted text-center">Handyman</p>

                            <ul class="list-group list-group-unbordered mb-3">
                                <li class="list-group-item">
                                    <b>Rating</b> 
                                    <span class="float-right">
                                        {{ profile?.rating || 0 }} <i class="fas fa-star text-warning"></i>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Total Jobs</b> 
                                    <span class="float-right">{{ profile?.total_jobs || 0 }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Status</b> 
                                    <span class="float-right">
                                        <span class="badge" :class="getApprovalBadge(profile?.approval_status)">
                                            {{ getApprovalText(profile?.approval_status) }}
                                        </span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="col-lg-8 col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Professional Information</h3>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="updateProfile">
                                <div class="form-group">
                                    <label>Bio</label>
                                    <textarea 
                                        v-model="formData.bio"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Tell clients about yourself..."
                                    ></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Years of Experience</label>
                                            <input 
                                                v-model="formData.years_experience"
                                                type="number"
                                                class="form-control"
                                                placeholder="0"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Hourly Rate ($)</label>
                                            <input 
                                                v-model="formData.hourly_rate"
                                                type="number"
                                                step="0.01"
                                                class="form-control"
                                                placeholder="0.00"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Certification</label>
                                    <input 
                                        v-model="formData.certification"
                                        type="text"
                                        class="form-control"
                                        placeholder="Enter your certifications..."
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Availability</label>
                                    <select v-model="formData.is_available" class="form-control">
                                        <option :value="true">Available</option>
                                        <option :value="false">Not Available</option>
                                    </select>
                                </div>

                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn-primary btn-block" :disabled="submitting">
                                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                                        {{ submitting ? 'Saving...' : 'Save Changes' }}
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
    name: 'HandymanProfile',
    setup() {
        const store = useStore();
        const profile = ref(null);
        const submitting = ref(false);

        const formData = reactive({
            bio: '',
            years_experience: '',
            hourly_rate: '',
            certification: '',
            is_available: true,
        });

        const getInitials = computed(() => {
            const name = profile.value?.user?.name || '';
            return name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .substring(0, 2) || '?';
        });

        const fetchProfile = async () => {
            try {
                const user = store.getters['auth/user'];
                const response = await axios.get('/handymen');
                const handyman = response.data.data.find(h => h.user_id === user.id);
                
                if (handyman) {
                    profile.value = handyman;
                    Object.assign(formData, {
                        bio: handyman.bio || '',
                        years_experience: handyman.years_experience || '',
                        hourly_rate: handyman.hourly_rate || '',
                        certification: handyman.certification || '',
                        is_available: handyman.is_available !== false,
                    });
                }
            } catch (error) {
                console.error('Error fetching profile:', error);
            }
        };

        const updateProfile = async () => {
            submitting.value = true;
            try {
                await axios.put(`/handymen/${profile.value.id}`, formData);
                Swal.fire('Success!', 'Profile updated successfully', 'success');
                fetchProfile();
            } catch (error) {
                Swal.fire('Error', 'Failed to update profile', 'error');
            } finally {
                submitting.value = false;
            }
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
            fetchProfile();
        });

        return {
            profile,
            formData,
            submitting,
            getInitials,
            updateProfile,
            getApprovalBadge,
            getApprovalText,
        };
    },
};
</script>

<style scoped>
/* ============ BASE ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.content {
    padding: 1.5rem 0;
}

.container-fluid {
    padding: 0 1rem;
}

/* ============ CARDS ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 1.5rem;
}

.card-primary.card-outline {
    border-top: 3px solid #007bff;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 0;
}

.card-body {
    padding: 1.5rem;
}

/* ============ PROFILE BOX ============ */
.box-profile {
    text-align: center;
}

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
    margin-bottom: 1rem;
}

.profile-username {
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
    margin: 1rem 0 0.5rem 0;
}

.text-muted {
    color: #6c757d;
}

/* ============ LIST GROUP ============ */
.list-group-unbordered {
    border-radius: 0;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
    padding: 0.75rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.list-group-item:first-child {
    border-top: 0;
}

.list-group-item b {
    font-weight: 600;
    color: #343a40;
}

.float-right {
    margin-left: auto;
}

/* ============ BADGES ============ */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
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

.text-warning {
    color: #ffc107 !important;
}

/* ============ FORM ============ */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: #343a40;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* ============ BUTTONS ============ */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #0056b3;
    border-color: #0056b3;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-block {
    display: flex;
    width: 100%;
}

/* ============ SPINNER ============ */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2rem;
}

.spinner-border {
    display: inline-block;
    border: 0.25rem solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.mr-1 {
    margin-right: 0.25rem !important;
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .user-avatar-large {
        width: 90px;
        height: 90px;
        font-size: 36px;
    }

    .profile-username {
        font-size: 1.375rem;
    }
}

/* ============ RESPONSIVE - MOBILE (< 768px) ============ */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0;
    }

    .content {
        padding: 1rem 0;
    }

    .card-body {
        padding: 1rem;
    }

    .card-header {
        padding: 0.75rem 1rem;
    }

    .card-title {
        font-size: 1.125rem;
    }

    .user-avatar-large {
        width: 80px;
        height: 80px;
        font-size: 32px;
    }

    .profile-username {
        font-size: 1.25rem;
    }

    .list-group-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .float-right {
        margin-left: 0;
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0 0.75rem;
    }

    .content-header h1 {
        font-size: 1.5rem;
    }

    .user-avatar-large {
        width: 70px;
        height: 70px;
        font-size: 28px;
    }

    .profile-username {
        font-size: 1.125rem;
    }

    .card-body {
        padding: 0.75rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-control {
        font-size: 0.9375rem;
        padding: 0.5rem;
    }

    .btn {
        padding: 0.5rem 0.75rem;
        font-size: 0.9375rem;
    }

    .list-group-item {
        padding: 0.5rem 0;
        font-size: 0.9375rem;
    }

    .badge {
        font-size: 0.8125rem;
        padding: 0.25rem 0.5rem;
    }
}

/* ============ PRINT STYLES ============ */
@media print {
    .btn {
        display: none !important;
    }

    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>