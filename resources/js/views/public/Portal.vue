<template>
    <div class="portal-container">
        <!-- Header -->
        <header class="portal-header">
            <div class="header-container">
                <div class="logo">
                    <i class="fas fa-tools"></i>
                    <span>Heaven's Home</span>
                </div>
                <nav class="main-nav">
                    <a href="#home">HOME</a>
                    <a href="#services">SERVICES</a>
                    <a href="#about">ABOUT</a>
                    <a href="#contact">CONTACT</a>
                </nav>
                <div class="header-actions">
                    <button @click="toggleMobileMenu" class="btn-mobile-menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button @click="goToLogin" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="login-text">LOGIN</span>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div v-if="showMobileMenu" class="mobile-menu">
                <a href="#home" @click="closeMobileMenu">HOME</a>
                <a href="#services" @click="closeMobileMenu">SERVICES</a>
                <a href="#about" @click="closeMobileMenu">ABOUT</a>
                <a href="#contact" @click="closeMobileMenu">CONTACT</a>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="home" class="hero-section">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">
                        The <span class="highlight">Handyman</span><br>
                        You Deserve
                    </h1>
                    <p class="hero-subtitle">
                        HAVE A PLUMBING ISSUE? 
                        <strong>CALL 24 HOUR EMERGENCY SERVICE.</strong>
                    </p>
                    <div class="hero-phone">
                        <i class="fas fa-phone-alt"></i>
                        <span>1-800-555-284</span>
                    </div>
                    <div class="hero-buttons">
                        <button @click="scrollToServices" class="btn-hero btn-hero-primary">
                            REQUEST JOB ESTIMATE
                        </button>
                        <button @click="scrollToServices" class="btn-hero btn-hero-secondary">
                            VIEW SERVICES
                        </button>
                    </div>
                </div>
                <div class="hero-image">
                    <img src="https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=600" alt="Professional Handyman">
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="services-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Services</h2>
                    <p class="section-subtitle">Select the service you need</p>
                </div>

                <div v-if="loadingCategories" class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading services...</p>
                </div>

                <div v-else class="services-grid">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="service-card"
                        @click="selectCategory(category)"
                    >
                        <div class="service-image">
                            <img 
                                v-if="category.image_url" 
                                :src="getImageUrl(category.image_url)" 
                                :alt="category.name"
                                @error="handleImageError"
                            >
                            <div class="service-icon-placeholder" :class="{ 'show': !category.image_url }">
                                <i :class="category.icon || 'fas fa-tools'"></i>
                            </div>
                        </div>
                        <div class="service-content">
                            <h3 class="service-name">{{ category.name }}</h3>
                            <p class="service-description">{{ category.description || 'Professional service' }}</p>
                            <button class="btn-service">
                                Request Service
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="about" class="features-section">
            <div class="container">
                <h2 class="section-title">Why Choose Us?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Certified Professionals</h3>
                        <p>All our technicians are certified and verified</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Fast Service</h3>
                        <p>Immediate response and record time attention</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3>Fair Prices</h3>
                        <p>Transparent quotes with no hidden costs</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3>Quality Guarantee</h3>
                        <p>We guarantee all our completed work</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registration Modal -->
        <div v-if="showRegistrationForm" class="modal-overlay" @click.self="closeModal">
            <div class="modal-container">
                <button class="btn-close-modal" @click="closeModal">
                    <i class="fas fa-times"></i>
                </button>

                <div class="modal-header">
                    <div class="modal-icon">
                        <i :class="selectedCategory?.icon || 'fas fa-tools'"></i>
                    </div>
                    <h3 class="modal-title">
                        {{ selectedCategory ? selectedCategory.name : 'Client Registration' }}
                    </h3>
                    <p class="modal-subtitle">Fill out the form below to get started</p>
                </div>

                <div class="modal-body">
                    <!-- Step 1: Email Verification -->
                    <div v-if="currentStep === 1" class="form-step">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            First we'll verify if you're already registered
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                class="form-control"
                                placeholder="your@email.com"
                                required
                            >
                        </div>

                        <button 
                            @click="checkEmail" 
                            class="btn btn-primary btn-block"
                            :disabled="!form.email || checking"
                        >
                            <span v-if="checking" class="spinner-sm"></span>
                            {{ checking ? 'Verifying...' : 'Verify Email' }}
                        </button>
                    </div>

                    <!-- Step 2: Complete Registration Form -->
                    <div v-if="currentStep === 2" class="form-step">
                        <!-- ✅ ALERTA DINÁMICA -->
                        <div v-if="registrationStatus === 'registered'" class="alert alert-success">
                            <i class="fas fa-user-check"></i>
                            <strong>Welcome back!</strong> Your information has been auto-filled. You can edit it if needed.
                        </div>
                        <div v-else-if="registrationStatus === 'pending'" class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>Request Pending!</strong> Your previous data has been loaded. You can make a new request.
                        </div>
                        <div v-else class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Email available. Complete your registration
                        </div>

                        <form @submit.prevent="submitRegistration">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input 
                                            v-model="form.name" 
                                            type="text" 
                                            class="form-control"
                                            placeholder="John Doe"
                                            required
                                        >
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone *</label>
                                        <input 
                                            v-model="form.phone" 
                                            type="tel" 
                                            class="form-control"
                                            placeholder="+1 999 999 999"
                                            required
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email Address *</label>
                                <input 
                                    v-model="form.email" 
                                    type="email" 
                                    class="form-control"
                                    readonly
                                    style="background: #f5f5f5;"
                                >
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Document Type *</label>
                                        <select v-model="form.document_type" class="form-control" required>
                                            <option value="" disabled>Select...</option>
                                            <option value="dni">DNI</option>
                                            <option value="passport">Passport</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Document Number *</label>
                                        <input 
                                            v-model="form.document_number" 
                                            type="text" 
                                            class="form-control"
                                            required
                                        >
                                        <small v-if="registrationStatus !== 'registered' && registrationStatus !== 'pending'" class="text-muted">
                                            This will be your initial password
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- PHOTO UPLOAD SECTION -->
                            <div class="form-group">
                                <label>Document Photos (Optional - Up to 5 photos)</label>
                                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">
                                    Upload up to 5 photos of your documents or project details
                                </p>
                                
                                <div class="photo-gallery">
                                    <div 
                                        v-for="index in MAX_PHOTOS" 
                                        :key="index"
                                        class="photo-slot"
                                    >
                                        <!-- Empty Slot -->
                                        <div v-if="!photoPreviews[index - 1]" class="photo-upload-slot">
                                            <div class="upload-buttons">
                                                <button 
                                                    type="button" 
                                                    class="btn-upload-mini"
                                                    @click="openFileInput(index - 1)"
                                                    title="Upload Photo"
                                                >
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                                <button 
                                                    type="button" 
                                                    class="btn-camera-mini"
                                                    @click="openCamera(index - 1)"
                                                    title="Take Photo"
                                                >
                                                    <i class="fas fa-camera"></i>
                                                </button>
                                            </div>
                                            <p class="slot-number">Photo {{ index }}</p>
                                            <input 
                                                :ref="el => fileInputs[index - 1] = el"
                                                type="file" 
                                                accept="image/*" 
                                                @change="handleFileSelect($event, index - 1)"
                                                style="display: none"
                                            >
                                            <input 
                                                :ref="el => cameraInputs[index - 1] = el"
                                                type="file" 
                                                accept="image/*" 
                                                capture="environment"
                                                @change="handleFileSelect($event, index - 1)"
                                                style="display: none"
                                            >
                                        </div>
                                        
                                        <!-- Photo Preview -->
                                        <div v-else class="photo-preview-slot">
                                            <img :src="photoPreviews[index - 1]" :alt="`Photo ${index}`" class="preview-image">
                                            <button 
                                                type="button" 
                                                class="btn-remove-mini"
                                                @click="removePhoto(index - 1)"
                                                title="Remove"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <span class="photo-badge">{{ index }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="photoPreviews.filter(Boolean).length > 0" class="photo-summary">
                                    <i class="fas fa-images"></i>
                                    {{ photoPreviews.filter(Boolean).length }} of {{ MAX_PHOTOS }} photos uploaded
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address *</label>
                                <input 
                                    v-model="form.address" 
                                    type="text" 
                                    class="form-control"
                                    placeholder="123 Main St"
                                    required
                                >
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Zip Code *</label>
                                        <input 
                                            v-model="form.zip_code" 
                                            type="text" 
                                            class="form-control"
                                            maxlength="5"
                                            pattern="\d{5}"
                                            placeholder="12345"
                                            @blur="lookupZipCode"
                                            required
                                        >
                                        <small v-if="lookingUpZip" class="text-info" style="display: block; margin-top: 0.25rem;">
                                            <i class="fas fa-spinner fa-spin"></i> Looking up ZIP code...
                                        </small>
                                        <small v-if="zipCodeError" class="text-danger" style="display: block; margin-top: 0.25rem;">
                                            <i class="fas fa-exclamation-circle"></i> {{ zipCodeError }}
                                        </small>
                                        <small v-if="!lookingUpZip && !zipCodeError" class="text-muted" style="display: block; margin-top: 0.25rem;">
                                            City and State will auto-fill
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>City *</label>
                                        <input 
                                            v-model="form.city" 
                                            type="text" 
                                            class="form-control"
                                            required
                                        >
                                        <small v-if="form.city && form.zip_code && !zipCodeError" class="text-success" style="display: block; margin-top: 0.25rem;">
                                            <i class="fas fa-check-circle"></i> Auto-filled from ZIP
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>State *</label>
                                        <input 
                                            v-model="form.state" 
                                            type="text" 
                                            class="form-control"
                                            required
                                        >
                                        <small v-if="form.state && form.zip_code && !zipCodeError" class="text-success" style="display: block; margin-top: 0.25rem;">
                                            <i class="fas fa-check-circle"></i> Auto-filled from ZIP
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Service of Interest</label>
                                <input 
                                    :value="selectedCategory.name" 
                                    type="text" 
                                    class="form-control"
                                    readonly
                                    style="background: #f5f5f5;"
                                >
                            </div>

                            <div class="form-group">
                                <label>Message (Optional)</label>
                                <textarea 
                                    v-model="form.message" 
                                    class="form-control" 
                                    rows="3"
                                    placeholder="Tell us about your project..."
                                ></textarea>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong> 
                                Your request will be reviewed by an administrator. You will receive an email notification.
                            </div>

                            <div class="form-actions">
                                <button 
                                    type="button" 
                                    @click="currentStep = 1" 
                                    class="btn btn-secondary"
                                >
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                                <button 
                                    type="submit" 
                                    class="btn btn-primary"
                                    :disabled="submitting"
                                >
                                    <span v-if="submitting" class="spinner-sm"></span>
                                    {{ submitting ? 'Sending...' : 'Submit Request' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Step 3: Result -->
                    <div v-if="currentStep === 3" class="form-step">
                        <div v-if="registrationStatus === 'success'" class="result-message">
                            <div class="result-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h4>Request Sent!</h4>
                            <p>We have received your request. We will contact you by email when approved.</p>
                            <button @click="closeModal" class="btn btn-success">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <div class="container">
                <div class="contact-content">
                    <div class="contact-info">
                        <h2>Get In Touch</h2>
                        <p>We're here to help with all your home service needs</p>
                        
                        <div class="contact-item">
                            <i class="fas fa-phone-alt"></i>
                            <div>
                                <h4>Call Us</h4>
                                <p>1-800-555-284</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email Us</h4>
                                <p>claude271109@gmail.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Visit Us</h4>
                                <p>Available 24/7 for Emergency Services</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-column">
                        <div class="footer-logo">
                            <i class="fas fa-tools"></i>
                            <span>Heaven's Home</span>
                        </div>
                        <p>Professional home service experts</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                        </div>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#about">About</a></li>
                            <li><a href="#contact">Contact</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Services</h4>
                        <ul>
                            <li><a href="#services">Plumbing</a></li>
                            <li><a href="#services">Electrical</a></li>
                            <li><a href="#services">Carpentry</a></li>
                            <li><a href="#services">Painting</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-column">
                        <h4>Contact</h4>
                        <ul>
                            <li><i class="fas fa-phone"></i> 1-800-555-284</li>
                            <li><i class="fas fa-envelope"></i> claude271109@gmail.com</li>
                            <li><i class="fas fa-clock"></i> 24/7 Service</li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; 2026 Heaven's Home. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'Portal',
    setup() {
        const router = useRouter();
        const categories = ref([]);
        const loadingCategories = ref(false);
        const showRegistrationForm = ref(false);
        const selectedCategory = ref(null);
        const currentStep = ref(1);
        const checking = ref(false);
        const submitting = ref(false);
        const registrationStatus = ref('');
        const showMobileMenu = ref(false);
        
        // Multi-photo upload
        const photoFiles = ref([]);
        const photoPreviews = ref([]);
        const fileInputs = ref([]);
        const cameraInputs = ref([]);
        const MAX_PHOTOS = 5;

        // ZIP Code lookup
        const lookingUpZip = ref(false);
        const zipCodeError = ref('');

        const form = reactive({
            name: '',
            email: '',
            phone: '',
            document_type: '',
            document_number: '',
            address: '',
            city: '',
            state: '',
            zip_code: '',
            service_category_id: null,
            message: '',
        });

        const toggleMobileMenu = () => {
            showMobileMenu.value = !showMobileMenu.value;
        };

        const closeMobileMenu = () => {
            showMobileMenu.value = false;
        };

        const openFileInput = (index) => {
            if (fileInputs.value[index]) {
                fileInputs.value[index].click();
            }
        };

        const openCamera = (index) => {
            if (cameraInputs.value[index]) {
                cameraInputs.value[index].click();
            }
        };

        const handleFileSelect = (event, index) => {
            const file = event.target.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid File Type',
                        text: 'Please select an image file',
                    });
                    return;
                }

                photoFiles.value[index] = file;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    photoPreviews.value[index] = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        };

        const removePhoto = (index) => {
            photoPreviews.value[index] = null;
            photoFiles.value[index] = null;
            if (fileInputs.value[index]) fileInputs.value[index].value = '';
            if (cameraInputs.value[index]) cameraInputs.value[index].value = '';
        };

        const removeAllPhotos = () => {
            photoPreviews.value = [];
            photoFiles.value = [];
            fileInputs.value.forEach(input => {
                if (input) input.value = '';
            });
            cameraInputs.value.forEach(input => {
                if (input) input.value = '';
            });
        };

        const getImageUrl = (imagePath) => {
            if (!imagePath) return '';
            
            if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
                return imagePath;
            }
            
            if (imagePath.startsWith('/storage')) {
                return `${window.location.origin}${imagePath}`;
            }
            
            if (imagePath.startsWith('storage/')) {
                return `${window.location.origin}/${imagePath}`;
            }
            
            return `${window.location.origin}/storage/${imagePath}`;
        };

        const handleImageError = (event) => {
            console.error('Error loading image:', event.target.src);
            event.target.style.display = 'none';
            const placeholder = event.target.parentElement.querySelector('.service-icon-placeholder');
            if (placeholder) {
                placeholder.classList.add('show');
            }
        };

        const fetchCategories = async () => {
            loadingCategories.value = true;
            try {
                const response = await axios.get('/portal/categories');
                categories.value = response.data;
                console.log('Categories loaded:', categories.value);
            } catch (error) {
                console.error('Error loading categories:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading services',
                });
            } finally {
                loadingCategories.value = false;
            }
        };

        const scrollToServices = () => {
            document.getElementById('services')?.scrollIntoView({ 
                behavior: 'smooth' 
            });
            closeMobileMenu();
        };

        const goToLogin = () => {
            router.push('/login');
        };

        const selectCategory = (category) => {
            selectedCategory.value = category;
            form.service_category_id = category.id;
            showRegistrationForm.value = true;
            currentStep.value = 1;
        };

        // ✅ FUNCIÓN PARA AUTO-LLENAR DATOS (COMPLETA)
        const fillUserData = (userData) => {
            if (userData) {
                form.name = userData.name || '';
                form.phone = userData.phone || '';
                form.document_type = userData.document_type || '';      // ✅ AHORA SÍ SE LLENA
                form.document_number = userData.document_number || '';  // ✅ AHORA SÍ SE LLENA
                form.address = userData.address || '';
                form.city = userData.city || '';
                form.state = userData.state || '';
                form.zip_code = userData.zip_code || '';
                
                // ✅ DEBUG: Ver qué datos llegan
                console.log('✅ Datos auto-llenados:', {
                    document_type: form.document_type,
                    document_number: form.document_number,
                });
            }
        };

        // ✅ CHECK EMAIL CON SWEETALERT
        const checkEmail = async () => {
            if (!form.email) return;

            checking.value = true;
            try {
                const response = await axios.post('/portal/check-registration', {
                    email: form.email
                });

                registrationStatus.value = response.data.status;
                const userData = response.data.user_data;

                // ✅ DEBUG: Ver respuesta completa
                console.log('📥 Respuesta del servidor:', response.data);
                console.log('📋 User Data recibida:', userData);

                if (response.data.status === 'registered') {
                    showRegistrationForm.value = false;
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Welcome Back!',
                            html: `
                                <div style="text-align: left; padding: 1rem;">
                                    <p style="margin-bottom: 1rem; font-size: 1rem;">
                                        Great news! You're already registered in our system.
                                    </p>
                                    <div style="background: #dcfce7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid #22c55e;">
                                        <p style="margin: 0.5rem 0; font-size: 0.95rem;"><strong>Name:</strong> ${userData.name}</p>
                                        <p style="margin: 0.5rem 0; font-size: 0.95rem;"><strong>Email:</strong> ${userData.email}</p>
                                        <p style="margin: 0.5rem 0; font-size: 0.95rem;"><strong>Phone:</strong> ${userData.phone}</p>
                                        <p style="margin: 0.5rem 0; font-size: 0.95rem;"><strong>Document:</strong> ${userData.document_type?.toUpperCase()} - ${userData.document_number}</p>
                                    </div>
                                    <p style="margin: 0; color: #15803d; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> 
                                        Your information will be auto-filled
                                    </p>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Continue with Request',
                            cancelButtonText: 'Go to Login',
                            confirmButtonColor: '#22c55e',
                            cancelButtonColor: '#3b82f6',
                            reverseButtons: true,
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fillUserData(userData);
                                showRegistrationForm.value = true;
                                currentStep.value = 2;
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                goToLogin();
                            }
                        });
                    }, 100);

                } else if (response.data.status === 'pending') {
                    showRegistrationForm.value = false;
                    
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'info',
                            title: 'Request Pending',
                            html: `
                                <div style="text-align: left; padding: 1rem;">
                                    <p style="margin-bottom: 1rem;">You have a pending registration request.</p>
                                    <div style="background: #fef3c7; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; border-left: 4px solid #f59e0b;">
                                        <p style="margin: 0.5rem 0;"><strong>Name:</strong> ${userData.name}</p>
                                        <p style="margin: 0.5rem 0;"><strong>Email:</strong> ${userData.email}</p>
                                        <p style="margin: 0.5rem 0;"><strong>Phone:</strong> ${userData.phone}</p>
                                        <p style="margin: 0.5rem 0;"><strong>Document:</strong> ${userData.document_type?.toUpperCase()} - ${userData.document_number}</p>
                                    </div>
                                    <p style="margin: 0; color: #92400e;">
                                        <i class="fas fa-clock"></i> 
                                        We will contact you soon
                                    </p>
                                </div>
                            `,
                            confirmButtonText: 'Make Another Request',
                            confirmButtonColor: '#f59e0b',
                            allowOutsideClick: false,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fillUserData(userData);
                                showRegistrationForm.value = true;
                                currentStep.value = 2;
                            }
                        });
                    }, 100);

                } else {
                    currentStep.value = 2;
                }

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error verifying email',
                });
            } finally {
                checking.value = false;
            }
        };

        const lookupZipCode = async () => {
            if (!form.zip_code || form.zip_code.length !== 5) {
                zipCodeError.value = '';
                return;
            }

            lookingUpZip.value = true;
            zipCodeError.value = '';

            try {
                const response = await axios.get(`/portal/lookup-zipcode/${form.zip_code}`);
                
                if (response.data.success) {
                    const data = response.data.data;
                    
                    form.city = data.city;
                    form.state = data.state_abbreviation;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Location Found!',
                        html: `
                            <p><strong>City:</strong> ${data.city}</p>
                            <p><strong>State:</strong> ${data.state} (${data.state_abbreviation})</p>
                        `,
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }
            } catch (error) {
                console.error('ZIP lookup error:', error);
                
                if (error.response?.status === 404) {
                    zipCodeError.value = 'ZIP code not found. Please verify.';
                } else {
                    zipCodeError.value = 'Error looking up ZIP code.';
                }
            } finally {
                lookingUpZip.value = false;
            }
        };

        const submitRegistration = async () => {
            submitting.value = true;
            try {
                const formData = new FormData();
                formData.append('name', form.name);
                formData.append('email', form.email);
                formData.append('phone', form.phone);
                formData.append('document_type', form.document_type);
                formData.append('document_number', form.document_number);
                formData.append('address', form.address);
                formData.append('city', form.city);
                formData.append('state', form.state);
                formData.append('zip_code', form.zip_code);
                formData.append('service_category_id', form.service_category_id);
                formData.append('message', form.message || '');
                
                photoFiles.value.forEach((file, index) => {
                    if (file) {
                        formData.append(`document_photos[${index}]`, file);
                    }
                });

                await axios.post('/portal/submit-registration', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                registrationStatus.value = 'success';
                currentStep.value = 3;

                Swal.fire({
                    icon: 'success',
                    title: 'Request Sent!',
                    text: 'We will contact you soon',
                    timer: 3000,
                });

            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error submitting request',
                });
            } finally {
                submitting.value = false;
            }
        };

        const closeModal = () => {
            showRegistrationForm.value = false;
            selectedCategory.value = null;
            currentStep.value = 1;
            registrationStatus.value = '';
            removeAllPhotos();
            Object.keys(form).forEach(key => {
                form[key] = key === 'service_category_id' ? null : '';
            });
        };

        onMounted(() => {
            fetchCategories();

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                        closeMobileMenu();
                    }
                });
            });
        });

        return {
            categories,
            loadingCategories,
            showRegistrationForm,
            selectedCategory,
            currentStep,
            checking,
            submitting,
            registrationStatus,
            showMobileMenu,
            photoFiles,
            photoPreviews,
            fileInputs,
            cameraInputs,
            MAX_PHOTOS,
            lookingUpZip,
            zipCodeError,
            form,
            toggleMobileMenu,
            closeMobileMenu,
            openFileInput,
            openCamera,
            handleFileSelect,
            removePhoto,
            removeAllPhotos,
            getImageUrl,
            handleImageError,
            scrollToServices,
            goToLogin,
            selectCategory,
            fillUserData,
            checkEmail,
            lookupZipCode,
            submitRegistration,
            closeModal,
        };
    }
};
</script>

<style scoped>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.portal-container {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow-x: hidden;
}

/* ============ HEADER ============ */
.portal-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    padding: 1rem 0;
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a365d;
}

.logo i {
    font-size: 2rem;
    color: #3b82f6;
}

.main-nav {
    display: flex;
    gap: 2rem;
}

.main-nav a {
    text-decoration: none;
    color: #4a5568;
    font-weight: 600;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    transition: color 0.3s;
}

.main-nav a:hover {
    color: #3b82f6;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-mobile-menu {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #4a5568;
    cursor: pointer;
    padding: 0.5rem;
}

.btn-login {
    padding: 0.75rem 1.5rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-login:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.mobile-menu {
    display: none;
    flex-direction: column;
    background: white;
    padding: 1rem;
    border-top: 1px solid #e2e8f0;
}

.mobile-menu a {
    padding: 0.75rem;
    text-decoration: none;
    color: #4a5568;
    font-weight: 600;
    border-bottom: 1px solid #f1f5f9;
}

/* ============ HERO SECTION ============ */
.hero-section {
    margin-top: 80px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    min-height: 600px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    max-width: 1400px;
    margin: 0 auto;
    padding: 4rem 1.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    position: relative;
    z-index: 1;
}

.hero-text {
    width: 100%;
}

.hero-title {
    font-size: clamp(2rem, 5vw, 4rem);
    font-weight: 800;
    color: white;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.hero-title .highlight {
    color: #60a5fa;
}

.hero-subtitle {
    font-size: clamp(0.9rem, 2vw, 1.125rem);
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.hero-phone {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: clamp(1.5rem, 3vw, 2.5rem);
    font-weight: 700;
    color: white;
    margin-bottom: 2rem;
}

.hero-phone i {
    color: #60a5fa;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-hero {
    padding: 1rem 2rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 700;
    font-size: clamp(0.875rem, 2vw, 1rem);
    cursor: pointer;
    transition: all 0.3s;
    letter-spacing: 0.5px;
}

.btn-hero-primary {
    background: #ef4444;
    color: white;
}

.btn-hero-primary:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
}

.btn-hero-secondary {
    background: white;
    color: #1e3a8a;
}

.btn-hero-secondary:hover {
    background: #f0f9ff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.3);
}

.hero-image {
    position: relative;
    display: flex;
    justify-content: center;
}

.hero-image img {
    width: 100%;
    max-width: 500px;
    border-radius: 1rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

/* ============ SERVICES SECTION ============ */
.services-section {
    padding: 6rem 1.5rem;
    background: #f7fafc;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    color: #1a202c;
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: clamp(1rem, 2vw, 1.25rem);
    color: #718096;
}

.loading-spinner {
    text-align: center;
    padding: 4rem 0;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #e2e8f0;
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
    gap: 2rem;
}

.service-card {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    cursor: pointer;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.service-image {
    height: 250px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.service-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.service-icon-placeholder {
    font-size: 5rem;
    color: white;
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.service-icon-placeholder.show {
    display: flex;
}

.service-content {
    padding: 2rem;
}

.service-name {
    font-size: clamp(1.25rem, 2vw, 1.5rem);
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.75rem;
}

.service-description {
    color: #718096;
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.btn-service {
    width: 100%;
    padding: 0.875rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.btn-service:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* ============ FEATURES SECTION ============ */
.features-section {
    padding: 6rem 1.5rem;
    background: white;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
    gap: 2.5rem;
    margin-top: 3rem;
}

.feature-card {
    text-align: center;
    padding: 2rem 1rem;
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.feature-card h3 {
    font-size: clamp(1.25rem, 2vw, 1.5rem);
    color: #1a202c;
    margin-bottom: 0.75rem;
}

.feature-card p {
    color: #718096;
    line-height: 1.6;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
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
    border-radius: 1.5rem;
    max-width: 650px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
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

.btn-close-modal {
    position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #f7fafc;
    color: #4a5568;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.3s;
    z-index: 10;
}

.btn-close-modal:hover {
    background: #e2e8f0;
    transform: rotate(90deg);
}

.modal-header {
    padding: 3rem 2rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modal-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
}

.modal-title {
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    margin-bottom: 0.5rem;
}

.modal-subtitle {
    opacity: 0.9;
    font-size: clamp(0.875rem, 2vw, 0.95rem);
}

.modal-body {
    padding: 2rem 1.5rem;
}

.form-step {
    animation: fadeIn 0.3s;
}

.alert {
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.alert i {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.alert-info {
    background: #e0f2fe;
    color: #0369a1;
    border-left: 4px solid #0ea5e9;
}

.alert-success {
    background: #dcfce7;
    color: #15803d;
    border-left: 4px solid #22c55e;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border-left: 4px solid #f59e0b;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #374151;
    font-weight: 600;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.5rem;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.text-muted {
    font-size: 0.875rem;
    color: #9ca3af;
    margin-top: 0.25rem;
    display: block;
}

.text-info {
    color: #0ea5e9;
}

.text-success {
    color: #22c55e;
}

.text-danger {
    color: #ef4444;
}

.row {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr;
}

.col-md-6,
.col-md-4 {
    width: 100%;
}

/* ============ GALERÍA DE FOTOS ============ */
.photo-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 110px), 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.photo-slot {
    aspect-ratio: 1;
    border-radius: 0.5rem;
    overflow: hidden;
}

.photo-upload-slot {
    width: 100%;
    height: 100%;
    border: 2px dashed #cbd5e0;
    border-radius: 0.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    background: #f9fafb;
    transition: all 0.3s;
    cursor: pointer;
}

.photo-upload-slot:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}

.upload-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-upload-mini,
.btn-camera-mini {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    font-size: 0.875rem;
}

.btn-upload-mini {
    background: #3b82f6;
    color: white;
}

.btn-upload-mini:hover {
    background: #2563eb;
    transform: scale(1.1);
}

.btn-camera-mini {
    background: #6b7280;
    color: white;
}

.btn-camera-mini:hover {
    background: #4b5563;
    transform: scale(1.1);
}

.slot-number {
    font-size: 0.75rem;
    color: #9ca3af;
    margin: 0;
    font-weight: 600;
}

.photo-preview-slot {
    width: 100%;
    height: 100%;
    position: relative;
    border-radius: 0.5rem;
    overflow: hidden;
    background: #000;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.btn-remove-mini {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    font-size: 0.75rem;
}

.btn-remove-mini:hover {
    background: #dc2626;
    transform: scale(1.15);
}

.photo-badge {
    position: absolute;
    bottom: 0.25rem;
    left: 0.25rem;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
}

.photo-summary {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #1e40af;
    font-size: 0.875rem;
    font-weight: 600;
}

.photo-summary i {
    font-size: 1rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-direction: column;
}

.btn {
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-success {
    background: #22c55e;
    color: white;
}

.btn-success:hover {
    background: #16a34a;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-block {
    width: 100%;
}

.spinner-sm {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.result-message {
    text-align: center;
    padding: 2rem 1rem;
}

.result-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
}

.result-icon.success {
    background: #dcfce7;
    color: #22c55e;
}

.result-icon.warning {
    background: #fef3c7;
    color: #f59e0b;
}

.result-message h4 {
    font-size: clamp(1.25rem, 3vw, 1.75rem);
    color: #1a202c;
    margin-bottom: 0.75rem;
}

.result-message p {
    color: #718096;
    margin-bottom: 2rem;
    font-size: clamp(0.875rem, 2vw, 1rem);
}

/* ============ CONTACT SECTION ============ */
.contact-section {
    padding: 6rem 1.5rem;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    color: white;
}

.contact-content {
    max-width: 800px;
    margin: 0 auto;
}

.contact-content h2 {
    font-size: clamp(2rem, 4vw, 2.5rem);
    margin-bottom: 1rem;
}

.contact-content > p {
    font-size: clamp(1rem, 2vw, 1.125rem);
    margin-bottom: 3rem;
    opacity: 0.9;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    backdrop-filter: blur(10px);
}

.contact-item i {
    font-size: 2rem;
    color: #60a5fa;
    flex-shrink: 0;
}

.contact-item h4 {
    font-size: clamp(1rem, 2vw, 1.25rem);
    margin-bottom: 0.25rem;
}

.contact-item p {
    opacity: 0.9;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

/* ============ FOOTER ============ */
.footer {
    background: #1a202c;
    color: white;
    padding: 4rem 1.5rem 2rem;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr));
    gap: 3rem;
    margin-bottom: 3rem;
}

.footer-column h4 {
    font-size: clamp(1rem, 2vw, 1.25rem);
    margin-bottom: 1.5rem;
    color: #60a5fa;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: clamp(1.25rem, 2vw, 1.5rem);
    font-weight: 700;
    margin-bottom: 1rem;
}

.footer-logo i {
    font-size: clamp(1.5rem, 3vw, 2rem);
    color: #3b82f6;
}

.footer-column p {
    color: #a0aec0;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.social-links {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.social-links a {
    width: 40px;
    height: 40px;
    background: rgba(59, 130, 246, 0.2);
    border-radius: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #60a5fa;
    transition: all 0.3s;
}

.social-links a:hover {
    background: #3b82f6;
    color: white;
    transform: translateY(-3px);
}

.footer-column ul {
    list-style: none;
}

.footer-column ul li {
    margin-bottom: 0.75rem;
}

.footer-column ul li a {
    color: #a0aec0;
    text-decoration: none;
    transition: color 0.3s;
    font-size: clamp(0.875rem, 1.5vw, 1rem);
}

.footer-column ul li a:hover {
    color: #60a5fa;
}

.footer-column ul li i {
    margin-right: 0.5rem;
    color: #3b82f6;
}

.footer-bottom {
    text-align: center;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: #a0aec0;
    font-size: clamp(0.75rem, 1.5vw, 0.875rem);
}

/* ============ RESPONSIVE DESIGN ============ */
@media (min-width: 768px) {
    .row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .form-actions {
        flex-direction: row;
    }
    
    .btn-primary {
        flex: 1;
    }
    
    .photo-gallery {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (max-width: 968px) {
    .main-nav {
        display: none;
    }
    
    .btn-mobile-menu {
        display: block;
    }
    
    .mobile-menu {
        display: flex;
    }
    
    .hero-content {
        grid-template-columns: 1fr;
        text-align: center;
        padding: 3rem 1.5rem;
    }
    
    .hero-image {
        display: none;
    }
    
    .hero-buttons {
        justify-content: center;
    }
}

@media (max-width: 640px) {
    .portal-header {
        padding: 0.75rem 0;
    }
    
    .logo {
        font-size: 1.25rem;
    }
    
    .logo i {
        font-size: 1.5rem;
    }
    
    .btn-login .login-text {
        display: none;
    }
    
    .btn-login {
        padding: 0.75rem;
    }
    
    .hero-section {
        margin-top: 70px;
    }
    
    .services-section,
    .features-section,
    .contact-section,
    .footer {
        padding: 4rem 1rem;
    }
    
    .modal-body {
        padding: 1.5rem 1rem;
    }
    
    .btn-hero {
        width: 100%;
        justify-content: center;
    }
    
    .photo-gallery {
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 90px), 1fr));
        gap: 0.5rem;
    }
}

@media (max-width: 480px) {
    .service-image {
        height: 200px;
    }
    
    .service-content {
        padding: 1.5rem;
    }
    
    .feature-card {
        padding: 1.5rem 0.5rem;
    }
    
    .modal-header {
        padding: 2rem 1rem 1.5rem;
    }
    
    .modal-icon {
        width: 60px;
        height: 60px;
        font-size: 2rem;
    }
}
</style>