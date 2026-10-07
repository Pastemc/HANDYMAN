<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="m-0">{{ $t('nav.locations') || 'Ubicaciones en Tiempo Real' }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marked-alt"></i> 
                        Todas las Ubicaciones Activas
                    </h3>
                    <div class="card-tools">
                        <button @click="refreshLocations" class="btn btn-sm btn-primary">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <LocationMap :show-all="true" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LocationMap from '../../components/LocationMap.vue';

export default {
    name: 'AllLocations',
    components: {
        LocationMap,
    },
    setup() {
        const refreshLocations = () => {
            window.location.reload();
        };

        return {
            refreshLocations,
        };
    },
};
</script>