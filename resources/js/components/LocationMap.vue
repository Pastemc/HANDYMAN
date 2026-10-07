<template>
    <div class="location-map">
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="text-muted mt-2">{{ $t('messages.loading') }}</p>
        </div>

        <div v-else-if="locations.length === 0" class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            {{ $t('location.not_available') }}
        </div>

        <div v-else class="locations-list">
            <div class="card location-item" v-for="location in locations" :key="location.id">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <i class="fas fa-user-circle text-primary"></i> 
                                {{ location.user.name }}
                            </h6>
                            <p class="text-muted small mb-2">
                                <i class="far fa-clock"></i> 
                                {{ formatDate(location.last_updated_at) }}
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-map-marker-alt text-success"></i>
                                <strong>{{ $t('location.latitude') }}:</strong> {{ parseFloat(location.latitude).toFixed(6) }}<br>
                                <i class="fas fa-map-marker-alt text-success"></i>
                                <strong>{{ $t('location.longitude') }}:</strong> {{ parseFloat(location.longitude).toFixed(6) }}
                            </p>
                            <p v-if="location.address" class="text-muted small mb-2">
                                <i class="fas fa-map-pin"></i> {{ location.address }}
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-bullseye"></i> 
                                {{ $t('location.accuracy') }}: {{ Math.round(location.accuracy) }}m
                            </p>
                        </div>
                    </div>
                    
                    <!-- Botón estilo WhatsApp -->
                    <a 
                        :href="getGoogleMapsUrl(location)" 
                        target="_blank" 
                        class="btn btn-success btn-block mt-3"
                    >
                        <i class="fas fa-route"></i> {{ $t('location.navigate') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export default {
    name: 'LocationMap',
    props: {
        serviceRequestId: {
            type: Number,
            default: null,
        },
        showAll: {
            type: Boolean,
            default: false,
        },
    },
    setup(props) {
        const locations = ref([]);
        const loading = ref(false);
        let refreshInterval = null;

        const loadLocations = async () => {
            loading.value = true;
            try {
                const endpoint = props.showAll 
                    ? '/locations' 
                    : `/locations/service-request/${props.serviceRequestId}`;
                
                const response = await axios.get(endpoint);
                locations.value = response.data.locations || [];
            } catch (error) {
                console.error('Error cargando ubicaciones:', error);
                locations.value = [];
            } finally {
                loading.value = false;
            }
        };

        const getGoogleMapsUrl = (location) => {
            return `https://www.google.com/maps/dir/?api=1&destination=${location.latitude},${location.longitude}`;
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleString('es-ES');
        };

        const setupRealtimeUpdates = () => {
            if (props.serviceRequestId) {
                window.Echo.channel(`service-request.${props.serviceRequestId}`)
                    .listen('.location.updated', (data) => {
                        console.log('Ubicación actualizada:', data);
                        loadLocations();
                    });
            } else if (props.showAll) {
                window.Echo.channel('locations')
                    .listen('.location.updated', (data) => {
                        console.log('Nueva ubicación:', data);
                        loadLocations();
                    });
            }
        };

        onMounted(() => {
            loadLocations();
            setupRealtimeUpdates();

            // Actualizar cada 30 segundos
            refreshInterval = setInterval(() => {
                loadLocations();
            }, 30000);
        });

        onUnmounted(() => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        return {
            locations,
            loading,
            formatDate,
            getGoogleMapsUrl,
        };
    },
};
</script>

<style scoped>
.location-map {
    padding: 15px;
}

.locations-list {
    max-height: 400px;
    overflow-y: auto;
}

.location-item {
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.location-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}
</style>