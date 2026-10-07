<template>
    <div class="location-tracker">
        <button 
            @click="toggleTracking" 
            class="btn btn-block"
            :class="isTracking ? 'btn-danger' : 'btn-success'"
            :disabled="loading"
        >
            <i class="fas" :class="isTracking ? 'fa-stop-circle' : 'fa-map-marker-alt'"></i>
            {{ isTracking ? $t('location.stop_sharing') : $t('location.share_location') }}
        </button>

        <div v-if="isTracking && destinationInfo" class="destination-info mt-3">
            <div class="alert alert-info">
                <h6 class="mb-2">
                    <i class="fas fa-flag-checkered"></i> 
                    <strong>{{ $t('location.destination') }}</strong>
                </h6>
                <p class="mb-1">
                    <i class="fas fa-route"></i> 
                    <strong>{{ $t('location.distance') }}:</strong> {{ Math.round(destinationInfo.distance) }}m
                </p>
                <p class="mb-0">
                    <i class="fas fa-clock"></i> 
                    <strong>{{ $t('location.status') }}:</strong> 
                    <span :class="destinationInfo.isNear ? 'text-success' : 'text-warning'">
                        {{ destinationInfo.isNear ? $t('location.near_destination') : $t('location.on_route') }}
                    </span>
                </p>
            </div>
        </div>

        <div v-if="currentLocation" class="location-card mt-3">
            <div class="location-header">
                <i class="fas fa-map-marker-alt text-success"></i>
                <strong>{{ $t('location.current_location') }}</strong>
            </div>
            <div class="location-body">
                <p class="mb-2">
                    <i class="fas fa-crosshairs"></i> 
                    <strong>{{ $t('location.latitude') }}:</strong> {{ currentLocation.latitude.toFixed(6) }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-crosshairs"></i> 
                    <strong>{{ $t('location.longitude') }}:</strong> {{ currentLocation.longitude.toFixed(6) }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-bullseye"></i> 
                    <strong>{{ $t('location.accuracy') }}:</strong> {{ Math.round(currentLocation.accuracy) }}m
                </p>
                <p v-if="currentLocation.address" class="mb-3">
                    <i class="fas fa-map-pin"></i> 
                    <strong>{{ $t('location.address') }}:</strong><br>
                    <small>{{ currentLocation.address }}</small>
                </p>
                <p class="text-muted small mb-3">
                    <i class="far fa-clock"></i> 
                    {{ $t('location.last_updated') }}: {{ formatDate(currentLocation.last_updated_at) }}
                </p>

                <a 
                    :href="getGoogleMapsUrl()" 
                    target="_blank"
                    class="btn btn-primary btn-block"
                >
                    <i class="fas fa-route"></i> {{ $t('location.open_google_maps') }}
                </a>
            </div>
        </div>

        <div v-if="error" class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-triangle"></i> {{ error }}
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'LocationTracker',
    props: {
        serviceRequestId: {
            type: Number,
            default: null,
        },
        autoStart: {
            type: Boolean,
            default: false,
        },
        destinationLat: {
            type: Number,
            default: null,
        },
        destinationLng: {
            type: Number,
            default: null,
        },
        arrivalRadius: {
            type: Number,
            default: 50, // 50 metros por defecto
        },
    },
    setup(props, { emit }) {
        const isTracking = ref(false);
        const loading = ref(false);
        const currentLocation = ref(null);
        const destinationInfo = ref(null);
        const error = ref(null);
        const hasArrived = ref(false);
        let watchId = null;
        let updateInterval = null;
        let distanceCheckInterval = null;

        const calculateDistance = (lat1, lon1, lat2, lon2) => {
            // Fórmula de Haversine para calcular distancia entre dos puntos
            const R = 6371e3; // Radio de la Tierra en metros
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c; // Distancia en metros
        };

        const checkArrival = () => {
            if (!currentLocation.value || !props.destinationLat || !props.destinationLng) {
                return;
            }

            const distance = calculateDistance(
                currentLocation.value.latitude,
                currentLocation.value.longitude,
                props.destinationLat,
                props.destinationLng
            );

            destinationInfo.value = {
                distance: distance,
                isNear: distance <= props.arrivalRadius,
            };

            // Si llegó al destino y no se ha notificado antes
            if (distance <= props.arrivalRadius && !hasArrived.value) {
                hasArrived.value = true;
                handleArrival(distance);
            }
        };

        const handleArrival = async (distance) => {
            console.log('¡Llegada detectada! Distancia:', Math.round(distance), 'metros');

            // Mostrar notificación de llegada
            await Swal.fire({
                icon: 'success',
                title: '¡Has llegado al destino!',
                html: `
                    <p>Estás a <strong>${Math.round(distance)} metros</strong> del cliente.</p>
                    <p>Se detendrá automáticamente el compartir ubicación.</p>
                `,
                timer: 5000,
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
            });

            // Detener tracking automáticamente
            await stopTracking();

            // Emitir evento de llegada
            emit('arrived', {
                distance: Math.round(distance),
                location: currentLocation.value,
            });

            // Enviar notificación al backend
            try {
                await axios.post(`/service-requests/${props.serviceRequestId}/arrived`, {
                    latitude: currentLocation.value.latitude,
                    longitude: currentLocation.value.longitude,
                    distance: Math.round(distance),
                });
            } catch (err) {
                console.error('Error notificando llegada:', err);
            }
        };

        const startTracking = async () => {
            if (!navigator.geolocation) {
                error.value = 'Tu navegador no soporta geolocalización';
                return;
            }

            try {
                loading.value = true;
                error.value = null;
                hasArrived.value = false;

                if (navigator.permissions) {
                    const permission = await navigator.permissions.query({ name: 'geolocation' });
                    
                    if (permission.state === 'denied') {
                        error.value = 'Permiso de ubicación denegado. Por favor, actívalo en la configuración de tu navegador.';
                        loading.value = false;
                        return;
                    }
                }

                watchId = navigator.geolocation.watchPosition(
                    async (position) => {
                        await updateLocation(position);
                        checkArrival(); // Verificar si llegó al destino
                    },
                    (err) => {
                        console.error('Error de geolocalización:', err);
                        error.value = getGeolocationError(err);
                        isTracking.value = false;
                        loading.value = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    }
                );

                isTracking.value = true;
                loading.value = false;

                // Actualizar ubicación al servidor cada 30 segundos
                updateInterval = setInterval(async () => {
                    if (currentLocation.value) {
                        await sendLocationToServer(currentLocation.value);
                    }
                }, 30000);

                // Verificar distancia cada 10 segundos
                distanceCheckInterval = setInterval(() => {
                    checkArrival();
                }, 10000);

                Swal.fire({
                    icon: 'success',
                    title: '¡Ubicación activada!',
                    html: props.destinationLat && props.destinationLng
                        ? '<p>Compartiendo ubicación en tiempo real</p><p><strong>Se detendrá automáticamente al llegar al destino</strong></p>'
                        : '<p>Compartiendo ubicación en tiempo real</p>',
                    timer: 3000,
                    showConfirmButton: false,
                });

            } catch (err) {
                console.error('Error iniciando tracking:', err);
                error.value = 'Error al iniciar el seguimiento de ubicación';
                loading.value = false;
            }
        };

        const stopTracking = async () => {
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }

            if (updateInterval) {
                clearInterval(updateInterval);
                updateInterval = null;
            }

            if (distanceCheckInterval) {
                clearInterval(distanceCheckInterval);
                distanceCheckInterval = null;
            }

            try {
                await axios.post('/locations/deactivate');
            } catch (err) {
                console.error('Error desactivando ubicación:', err);
            }

            isTracking.value = false;
            currentLocation.value = null;
            destinationInfo.value = null;

            if (!hasArrived.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Ubicación desactivada',
                    text: 'Ya no estás compartiendo tu ubicación',
                    timer: 2000,
                    showConfirmButton: false,
                });
            }
        };

        const toggleTracking = async () => {
            if (isTracking.value) {
                await stopTracking();
            } else {
                await startTracking();
            }
        };

        const updateLocation = async (position) => {
            const locationData = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy,
                service_request_id: props.serviceRequestId,
            };

            currentLocation.value = {
                ...locationData,
                last_updated_at: new Date().toISOString(),
            };

            await sendLocationToServer(locationData);
        };

        const sendLocationToServer = async (locationData) => {
            try {
                const response = await axios.post('/locations', locationData);
                if (response.data.location.address) {
                    currentLocation.value.address = response.data.location.address;
                }
            } catch (err) {
                console.error('Error enviando ubicación:', err);
                if (err.response?.status === 401) {
                    await stopTracking();
                    error.value = 'Sesión expirada. Por favor, inicia sesión nuevamente.';
                }
            }
        };

        const getGeolocationError = (err) => {
            switch (err.code) {
                case err.PERMISSION_DENIED:
                    return 'Permiso de ubicación denegado. Por favor, actívalo en la configuración.';
                case err.POSITION_UNAVAILABLE:
                    return 'Información de ubicación no disponible.';
                case err.TIMEOUT:
                    return 'Tiempo de espera agotado al obtener la ubicación.';
                default:
                    return 'Error desconocido al obtener la ubicación.';
            }
        };

        const getGoogleMapsUrl = () => {
            if (!currentLocation.value) return '#';
            
            // Si hay destino, crear ruta
            if (props.destinationLat && props.destinationLng) {
                return `https://www.google.com/maps/dir/?api=1&origin=${currentLocation.value.latitude},${currentLocation.value.longitude}&destination=${props.destinationLat},${props.destinationLng}&travelmode=driving`;
            }
            
            // Si no hay destino, solo mostrar ubicación actual
            return `https://www.google.com/maps/dir/?api=1&destination=${currentLocation.value.latitude},${currentLocation.value.longitude}`;
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleString('es-ES');
        };

        onMounted(() => {
            if (props.autoStart) {
                startTracking();
            }
        });

        onUnmounted(() => {
            stopTracking();
        });

        return {
            isTracking,
            loading,
            currentLocation,
            destinationInfo,
            error,
            toggleTracking,
            formatDate,
            getGoogleMapsUrl,
        };
    },
};
</script>

<style scoped>
.location-tracker {
    padding: 15px;
}

.destination-info .alert {
    border-left: 4px solid #17a2b8;
}

.location-card {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid #28a745;
}

.location-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 12px 15px;
    font-size: 16px;
}

.location-header i {
    margin-right: 8px;
}

.location-body {
    padding: 15px;
}

.location-body p {
    margin-bottom: 8px;
}

.location-body i {
    margin-right: 8px;
    color: #28a745;
}
</style>