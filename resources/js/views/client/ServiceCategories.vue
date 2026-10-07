<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('categories.service_categories') }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12 mb-4" v-for="category in categories" :key="category.id">
                    <div class="card card-primary card-outline category-card">
                        <div class="card-body box-profile">
                            <!-- CARRUSEL DE IMÁGENES -->
                            <div class="text-center category-image-container">
                                <div 
                                    v-if="category.images_urls && category.images_urls.length > 0"
                                    :id="`categoryCarousel${category.id}`"
                                    class="carousel slide"
                                    data-ride="carousel"
                                    data-interval="3000"
                                >
                                    <!-- Indicadores -->
                                    <ol class="carousel-indicators" v-if="category.images_urls.length > 1">
                                        <li 
                                            v-for="(image, index) in category.images_urls" 
                                            :key="'indicator-' + index"
                                            :data-target="`#categoryCarousel${category.id}`" 
                                            :data-slide-to="index"
                                            :class="{ active: index === 0 }"
                                        ></li>
                                    </ol>

                                    <!-- Imágenes del carrusel -->
                                    <div class="carousel-inner">
                                        <div 
                                            v-for="(imageUrl, index) in category.images_urls" 
                                            :key="'img-' + index"
                                            class="carousel-item"
                                            :class="{ active: index === 0 }"
                                        >
                                            <img 
                                                :src="'http://127.0.0.1:8000' + imageUrl" 
                                                class="d-block w-100 category-carousel-image"
                                                :alt="category.name"
                                            >
                                        </div>
                                    </div>

                                    <!-- Controles de navegación (solo si hay más de 1 imagen) -->
                                    <a 
                                        v-if="category.images_urls.length > 1"
                                        class="carousel-control-prev" 
                                        :href="`#categoryCarousel${category.id}`" 
                                        role="button" 
                                        data-slide="prev"
                                    >
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Anterior</span>
                                    </a>
                                    <a 
                                        v-if="category.images_urls.length > 1"
                                        class="carousel-control-next" 
                                        :href="`#categoryCarousel${category.id}`" 
                                        role="button" 
                                        data-slide="next"
                                    >
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Siguiente</span>
                                    </a>
                                </div>

                                <!-- Ícono por defecto si no hay imágenes -->
                                <div v-else class="category-icon-placeholder">
                                    <i :class="category.icon || 'fas fa-tools'" class="fa-4x text-primary"></i>
                                </div>
                            </div>

                            <!-- Información de la categoría -->
                            <h3 class="profile-username text-center mt-3">{{ category.name }}</h3>
                            <p class="text-muted text-center category-description">{{ category.description }}</p>
                            
                            <!-- Botón para crear solicitud -->
                            <router-link 
                                :to="{ path: '/dashboard/client/request/create', query: { category: category.id } }" 
                                class="btn btn-primary btn-block"
                            >
                                <b>{{ $t('requests.create_request') }}</b>
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay categorías -->
            <div v-if="categories.length === 0" class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">{{ $t('messages.no_data') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export default {
    name: 'ServiceCategories',
    setup() {
        const categories = ref([]);

        const fetchCategories = async () => {
            try {
                const response = await axios.get('/service-categories');
                categories.value = response.data.filter(c => c.is_active);
                
                // Inicializar carruseles después de cargar las categorías
                if (categories.value.length > 0) {
                    setTimeout(() => {
                        categories.value.forEach(category => {
                            if (category.images_urls && category.images_urls.length > 0) {
                                window.$(`#categoryCarousel${category.id}`).carousel({
                                    interval: 3000,
                                    ride: 'carousel',
                                    pause: 'hover'
                                });
                            }
                        });
                    }, 200);
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };

        onMounted(() => {
            fetchCategories();
        });

        onUnmounted(() => {
            // Limpiar carruseles al desmontar el componente
            window.$('.carousel').carousel('dispose');
        });

        return {
            categories,
        };
    },
};
</script>

<style scoped>
/* ========== TARJETA DE CATEGORÍA ========== */
.category-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
    height: 100%;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* ========== CONTENEDOR DE IMAGEN/CARRUSEL ========== */
.category-image-container {
    position: relative;
    width: 100%;
    margin-bottom: 20px;
}

.category-carousel-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 8px;
}

.category-icon-placeholder {
    width: 100%;
    height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    border-radius: 8px;
}

/* ========== CARRUSEL ========== */
.carousel-indicators {
    bottom: 10px;
}

.carousel-indicators li {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.6);
    border: 2px solid #fff;
}

.carousel-indicators .active {
    background-color: #007bff;
    transform: scale(1.2);
}

.carousel-control-prev,
.carousel-control-next {
    width: 40px;
    height: 40px;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.category-card:hover .carousel-control-prev,
.category-card:hover .carousel-control-next {
    opacity: 0.8;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    opacity: 1;
    background-color: rgba(0, 0, 0, 0.7);
}

.carousel-control-prev {
    left: 10px;
}

.carousel-control-next {
    right: 10px;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    width: 20px;
    height: 20px;
}

/* ========== TEXTO DE LA TARJETA ========== */
.profile-username {
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
    margin-bottom: 10px;
}

.category-description {
    font-size: 0.95rem;
    line-height: 1.5;
    min-height: 60px;
}

/* ========== BOTÓN ========== */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border-radius: 8px;
    padding: 10px 20px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 992px) {
    .category-carousel-image,
    .category-icon-placeholder {
        height: 220px;
    }
}

@media (max-width: 768px) {
    .category-carousel-image,
    .category-icon-placeholder {
        height: 200px;
    }

    .profile-username {
        font-size: 1.3rem;
    }

    .category-description {
        font-size: 0.9rem;
        min-height: 50px;
    }
}

@media (max-width: 576px) {
    .category-carousel-image,
    .category-icon-placeholder {
        height: 180px;
    }

    .carousel-control-prev,
    .carousel-control-next {
        width: 35px;
        height: 35px;
    }
}

/* ========== ANIMACIONES ========== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.category-card {
    animation: fadeInUp 0.5s ease-out;
}

.category-card:nth-child(1) { animation-delay: 0.1s; }
.category-card:nth-child(2) { animation-delay: 0.2s; }
.category-card:nth-child(3) { animation-delay: 0.3s; }
.category-card:nth-child(4) { animation-delay: 0.4s; }
.category-card:nth-child(5) { animation-delay: 0.5s; }
.category-card:nth-child(6) { animation-delay: 0.6s; }

/* ========== TRANSICIONES SUAVES ========== */
.carousel-item {
    transition: transform 0.6s ease-in-out;
}

.carousel-item-next,
.carousel-item-prev {
    display: block;
}
</style>