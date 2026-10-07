<template>
    <div class="login-container">
        <div class="login-wrapper">
            <!-- Left Side - Image -->
            <div class="login-image-section">
                <div class="image-overlay">
                    <div class="brand-content">
                        <h1 class="brand-title">
                            <i class="fas fa-tools"></i>
                            Heaven's Home
                        </h1>
                        <p class="brand-subtitle">Expert Handyman Services</p>
                        <div class="brand-features">
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Certified Professionals</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>24/7 Service</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Quality Guarantee</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
            <div class="login-form-section">
                <div class="login-form-container">
                    <!-- Logo for mobile -->
                    <div class="mobile-logo">
                        <i class="fas fa-tools"></i>
                        <span>Heaven's Home</span>
                    </div>

                    <div class="form-header">
                        <h2>Welcome Back</h2>
                        <p>Enter your credentials to continue</p>
                    </div>

                    <form @submit.prevent="handleLogin" class="login-form">
                        <!-- Email Input -->
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input 
                                id="email"
                                v-model="form.email" 
                                type="email" 
                                class="form-input" 
                                placeholder="your@email.com"
                                :class="{ 'is-invalid': errors.email }"
                                required
                                autocomplete="email"
                            >
                            <span v-if="errors.email" class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ errors.email[0] }}
                            </span>
                        </div>

                        <!-- Password Input -->
                        <div class="form-group">
                            <label for="password">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="password-wrapper">
                                <input 
                                    id="password"
                                    v-model="form.password" 
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-input" 
                                    placeholder="Enter your password"
                                    :class="{ 'is-invalid': errors.password }"
                                    required
                                    autocomplete="current-password"
                                >
                                <button 
                                    type="button" 
                                    class="password-toggle"
                                    @click="showPassword = !showPassword"
                                >
                                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                                </button>
                            </div>
                            <span v-if="errors.password" class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ errors.password[0] }}
                            </span>
                        </div>

                        <!-- Error Alert -->
                        <div v-if="errorMessage" class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ errorMessage }}</span>
                            <button type="button" class="alert-close" @click="errorMessage = ''">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="form-options">
                            <label class="checkbox-container">
                                <input v-model="form.remember" type="checkbox" id="remember">
                                <span class="checkmark"></span>
                                <span class="checkbox-label">Remember me</span>
                            </label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit" :disabled="loading">
                            <span v-if="!loading">
                                <i class="fas fa-sign-in-alt"></i>
                                Sign In
                            </span>
                            <span v-else class="loading-content">
                                <i class="fas fa-spinner fa-spin"></i>
                                Signing in...
                            </span>
                        </button>

                        <!-- Register Link -->
                        <div class="register-link">
                            <span>Don't have an account?</span>
                            <router-link to="/register">
                                Sign up here
                                <i class="fas fa-arrow-right"></i>
                            </router-link>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="form-footer">
                        <p>&copy; 2026 Heaven's Home. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
    name: 'Login',
    setup() {
        const store = useStore();
        const router = useRouter();

        const form = ref({
            email: '',
            password: '',
            remember: false,
        });

        const errors = ref({});
        const errorMessage = ref('');
        const loading = ref(false);
        const showPassword = ref(false);

        const handleLogin = async () => {
            errors.value = {};
            errorMessage.value = '';
            loading.value = true;

            console.log('Attempting login with:', form.value.email);

            try {
                const response = await store.dispatch('auth/login', form.value);
                
                console.log('Login successful:', response);

                const user = response.user;
                const role = user.roles[0].name;
                
                console.log('User role:', role);

                switch (role) {
                    case 'client':
                        router.push('/dashboard/client');
                        break;
                    case 'handyman':
                        router.push('/dashboard/handyman');
                        break;
                    case 'admin':
                    case 'superadmin':
                        router.push('/dashboard/admin');
                        break;
                    default:
                        router.push('/');
                }
            } catch (error) {
                console.error('Login error:', error);

                if (error.response?.status === 422) {
                    errors.value = error.response.data.errors || {};
                    errorMessage.value = 'Please verify the data entered';
                } else if (error.response?.data?.message) {
                    errorMessage.value = error.response.data.message;
                } else if (error.message === 'Network Error') {
                    errorMessage.value = 'Connection error. Please verify that the server is running.';
                } else {
                    errorMessage.value = 'Login error. Please try again.';
                }
            } finally {
                loading.value = false;
            }
        };

        return {
            form,
            errors,
            errorMessage,
            loading,
            showPassword,
            handleLogin,
        };
    },
};
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    padding: 20px;
}

.login-wrapper {
    display: flex;
    width: 100%;
    max-width: 1200px;
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.login-image-section {
    flex: 1;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    position: relative;
    min-height: 700px;
    overflow: hidden;
}

.login-image-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    opacity: 0.4;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
    z-index: 1;
}

.brand-content {
    color: white;
    text-align: center;
}

.brand-title {
    font-size: 52px;
    font-weight: 800;
    margin-bottom: 16px;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    letter-spacing: -1px;
}

.brand-title i {
    margin-right: 12px;
    color: #60a5fa;
}

.brand-subtitle {
    font-size: 22px;
    margin-bottom: 50px;
    opacity: 0.95;
    font-weight: 500;
}

.brand-features {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 50px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 17px;
    padding: 18px 24px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(12px);
    border-color: rgba(255, 255, 255, 0.3);
}

.feature-item i {
    color: #60a5fa;
    font-size: 22px;
}

.login-form-section {
    flex: 1;
    padding: 70px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #ffffff;
}

.mobile-logo {
    display: none;
    align-items: center;
    gap: 12px;
    font-size: 28px;
    font-weight: 800;
    color: #1e3a8a;
    margin-bottom: 40px;
    justify-content: center;
}

.mobile-logo i {
    font-size: 36px;
    color: #3b82f6;
}

.login-form-container {
    max-width: 460px;
    width: 100%;
    margin: 0 auto;
}

.form-header {
    margin-bottom: 48px;
}

.form-header h2 {
    font-size: 36px;
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}

.form-header p {
    color: #64748b;
    font-size: 17px;
    font-weight: 400;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 28px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-group label {
    font-weight: 600;
    color: #1e293b;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group label i {
    color: #3b82f6;
    font-size: 16px;
}

.form-input {
    width: 100%;
    padding: 16px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #f8fafc;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder {
    color: #94a3b8;
}

.form-input.is-invalid {
    border-color: #ef4444;
    background: #fef2f2;
}

.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 18px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 8px;
    transition: all 0.3s ease;
    border-radius: 6px;
}

.password-toggle:hover {
    color: #3b82f6;
    background: #f1f5f9;
}

.error-message {
    color: #ef4444;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.alert-error {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px;
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
    color: #dc2626;
    font-size: 15px;
    position: relative;
}

.alert-close {
    margin-left: auto;
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    padding: 6px;
    transition: opacity 0.3s ease;
    border-radius: 6px;
}

.alert-close:hover {
    opacity: 0.7;
    background: #fee2e2;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: -8px;
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    user-select: none;
}

.checkbox-container input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #3b82f6;
}

.checkbox-label {
    font-size: 15px;
    color: #475569;
    font-weight: 500;
}

.forgot-password {
    color: #3b82f6;
    font-size: 15px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.forgot-password:hover {
    color: #2563eb;
    text-decoration: underline;
}

.btn-submit {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 17px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    letter-spacing: 0.3px;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5);
}

.btn-submit:active:not(:disabled) {
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.loading-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.register-link {
    text-align: center;
    font-size: 15px;
    color: #64748b;
    margin-top: 8px;
}

.register-link a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 700;
    margin-left: 6px;
    transition: all 0.3s ease;
}

.register-link a:hover {
    color: #2563eb;
}

.form-footer {
    margin-top: 50px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}

@media (max-width: 968px) {
    .login-image-section {
        display: none;
    }

    .mobile-logo {
        display: flex;
    }

    .login-form-section {
        padding: 50px 40px;
    }

    .form-header h2 {
        font-size: 32px;
    }
}

@media (max-width: 640px) {
    .login-container {
        padding: 15px;
    }

    .login-wrapper {
        border-radius: 20px;
    }

    .login-form-section {
        padding: 40px 24px;
    }

    .form-header h2 {
        font-size: 28px;
    }

    .form-header p {
        font-size: 15px;
    }

    .form-options {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }

    .mobile-logo {
        font-size: 24px;
    }

    .mobile-logo i {
        font-size: 30px;
    }

    .btn-submit {
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .login-form-section {
        padding: 30px 20px;
    }

    .form-header {
        margin-bottom: 36px;
    }

    .login-form {
        gap: 24px;
    }
}
</style>