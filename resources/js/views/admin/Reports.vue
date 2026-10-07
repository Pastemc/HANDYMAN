<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <h1 class="m-0">{{ $t('reports.title') }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('reports.select_date_range') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <div class="form-group mb-0">
                                <label>{{ $t('reports.from_date') }}</label>
                                <input 
                                    v-model="filters.start_date" 
                                    type="date" 
                                    class="form-control"
                                    :max="filters.end_date || today"
                                >
                            </div>
                        </div>
                        <div class="col-md-4 col-12 mb-3 mb-md-0">
                            <div class="form-group mb-0">
                                <label>{{ $t('reports.to_date') }}</label>
                                <input 
                                    v-model="filters.end_date" 
                                    type="date" 
                                    class="form-control"
                                    :min="filters.start_date"
                                    :max="today"
                                >
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group mb-0">
                                <label class="d-none d-md-block">&nbsp;</label>
                                <button 
                                    @click="generateReport" 
                                    class="btn btn-primary btn-block"
                                    :disabled="loading"
                                >
                                    <span v-if="loading" class="spinner-border spinner-border-sm mr-1"></span>
                                    <i v-else class="fas fa-chart-bar"></i>
                                    {{ $t('reports.generate_report') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ summary.total_users || 0 }}</h3>
                            <p>{{ $t('dashboard.total_users') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ summary.total_handymen || 0 }}</h3>
                            <p>{{ $t('handymen.registered_handymen') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hard-hat"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ summary.total_requests || 0 }}</h3>
                            <p>{{ $t('requests.total_requests') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 class="text-truncate">${{ summary.total_revenue || '0.00' }}</h3>
                            <p>{{ $t('reports.total_revenue') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Stats -->
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $t('status.completed') }}</span>
                            <span class="info-box-number">{{ summary.completed_requests || 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $t('status.pending') }}</span>
                            <span class="info-box-number">{{ summary.pending_requests || 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-star"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $t('handymen.rating') }}</span>
                            <span class="info-box-number">{{ summary.average_rating || 0 }} ★</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-12 mb-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $t('payments.total_transactions') }}</span>
                            <span class="info-box-number">${{ summary.total_transactions || '0.00' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen y Métricas -->
            <div class="row">
                <div class="col-lg-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title text-white">{{ $t('reports.report_summary') }}</h3>
                            <div class="card-tools">
                                <button 
                                    @click="exportPDF" 
                                    class="btn btn-sm btn-danger"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-file-pdf"></i>
                                    <span class="d-none d-sm-inline ml-1">PDF</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.from_date') }}:</td>
                                            <td>{{ formatDate(period.start_date) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.to_date') }}:</td>
                                            <td>{{ formatDate(period.end_date) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.total_revenue') }}:</td>
                                            <td class="text-success font-weight-bold">${{ summary.total_revenue || '0.00' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('dashboard.platform_income') }}:</td>
                                            <td>${{ summary.platform_revenue || '0.00' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.total_jobs') }}:</td>
                                            <td>{{ summary.total_requests || 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.average_rating') }}:</td>
                                            <td>{{ summary.average_rating || 0 }} / 5.0</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12 mb-3">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h3 class="card-title text-white">{{ $t('reports.performance_metrics') }}</h3>
                            <div class="card-tools">
                                <button 
                                    @click="exportExcel" 
                                    class="btn btn-sm btn-success"
                                    :disabled="loading"
                                >
                                    <i class="fas fa-file-excel"></i>
                                    <span class="d-none d-sm-inline ml-1">Excel</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('users.active_users') }}:</td>
                                            <td>{{ metrics.active_users || 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('handymen.approved') }}:</td>
                                            <td>{{ metrics.approved_handymen || 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('handymen.pending_approval') }}:</td>
                                            <td>{{ metrics.pending_approval || 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('reports.customer_satisfaction') }}:</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div 
                                                        class="progress-bar bg-success" 
                                                        :style="{ width: calculateSatisfaction() + '%' }"
                                                    >
                                                        {{ calculateSatisfaction() }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">{{ $t('categories.total_categories') }}:</td>
                                            <td>{{ metrics.total_categories || 0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios Más Solicitados -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $t('reports.top_services') }}</h3>
                        </div>
                        <div class="card-body">
                            <!-- Vista Desktop -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ $t('categories.category_name') }}</th>
                                            <th>{{ $t('requests.total_requests') }}</th>
                                            <th>{{ $t('reports.total_revenue') }}</th>
                                            <th>{{ $t('reports.average_rating') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(service, index) in mostRequestedServices" :key="index">
                                            <td>{{ service.category_name }}</td>
                                            <td>{{ service.total_requests }}</td>
                                            <td>${{ service.total_revenue }}</td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    {{ service.average_rating }} ★
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="mostRequestedServices.length === 0">
                                            <td colspan="4" class="text-center">{{ $t('dashboard.no_data_available') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Vista Móvil -->
                            <div class="d-md-none">
                                <div 
                                    v-for="(service, index) in mostRequestedServices" 
                                    :key="index"
                                    class="card mb-2"
                                >
                                    <div class="card-body p-3">
                                        <h5 class="mb-2">{{ service.category_name }}</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted d-block">{{ $t('nav.requests') }}</small>
                                                <strong>{{ service.total_requests }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">{{ $t('reports.total_revenue') }}</small>
                                                <strong>${{ service.total_revenue }}</strong>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted d-block">{{ $t('handymen.rating') }}</small>
                                            <span class="badge badge-warning">
                                                {{ service.average_rating }} ★
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="mostRequestedServices.length === 0" class="text-center py-3">
                                    {{ $t('dashboard.no_data_available') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import i18n from '../../plugins/i18n';

export default {
    name: 'Reports',
    setup() {
        const summary = ref({});
        const metrics = ref({});
        const mostRequestedServices = ref([]);
        const period = ref({});
        const loading = ref(false);

        const filters = reactive({
            start_date: '',
            end_date: '',
        });

        const today = computed(() => {
            return new Date().toISOString().split('T')[0];
        });

        const generateReport = async () => {
            loading.value = true;
            try {
                console.log('Generando reporte con fechas:', filters);
                
                const response = await axios.get('/reports', { 
                    params: {
                        start_date: filters.start_date,
                        end_date: filters.end_date,
                    }
                });
                
                console.log('Respuesta del servidor:', response.data);
                
                summary.value = response.data.summary || {};
                metrics.value = response.data.metrics || {};
                mostRequestedServices.value = response.data.most_requested_services || [];
                period.value = response.data.period || {};

            } catch (error) {
                console.error('Error generando reporte:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo generar el reporte: ' + (error.response?.data?.message || error.message),
                });
            } finally {
                loading.value = false;
            }
        };

        const exportPDF = async () => {
            try {
                loading.value = true;
                const response = await axios.get('/reports/export/pdf', {
                    params: {
                        start_date: filters.start_date,
                        end_date: filters.end_date,
                        lang: i18n.locale,
                    },
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], { type: 'application/pdf' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `reporte_${filters.start_date}_${filters.end_date}.pdf`;
                link.click();
                window.URL.revokeObjectURL(url);

                Swal.fire({
                    icon: 'success',
                    title: 'PDF Generado',
                    text: 'El reporte se ha descargado exitosamente',
                    timer: 2000,
                });
            } catch (error) {
                console.error('Error descargando PDF:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo generar el PDF',
                });
            } finally {
                loading.value = false;
            }
        };

        const exportExcel = async () => {
            try {
                loading.value = true;
                const response = await axios.get('/reports/export/excel', {
                    params: {
                        start_date: filters.start_date,
                        end_date: filters.end_date,
                        lang: i18n.locale,
                    },
                    responseType: 'blob',
                });

                const blob = new Blob([response.data], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `reporte_${filters.start_date}_${filters.end_date}.csv`;
                link.click();
                window.URL.revokeObjectURL(url);

                Swal.fire({
                    icon: 'success',
                    title: 'Excel Generado',
                    text: 'El reporte se ha descargado exitosamente',
                    timer: 2000,
                });
            } catch (error) {
                console.error('Error descargando Excel:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo generar el Excel',
                });
            } finally {
                loading.value = false;
            }
        };

        const formatDate = (date) => {
            if (!date) return '';
            const currentLocale = i18n.locale === 'es' ? 'es-ES' : 'en-US';
            return new Date(date).toLocaleDateString(currentLocale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
        };

        const calculateSatisfaction = () => {
            const rating = metrics.value.client_satisfaction || summary.value.average_rating || 0;
            return Math.round((rating / 5) * 100);
        };

        onMounted(() => {
            // Últimos 30 días por defecto
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            
            filters.start_date = startDate.toISOString().split('T')[0];
            filters.end_date = endDate.toISOString().split('T')[0];
            
            generateReport();
        });

        return {
            summary,
            metrics,
            mostRequestedServices,
            period,
            loading,
            filters,
            today,
            generateReport,
            exportPDF,
            exportExcel,
            formatDate,
            calculateSatisfaction,
        };
    },
};
</script>

<style scoped>
/* Ajustes para móvil */
@media (max-width: 767px) {
    .small-box {
        margin-bottom: 10px;
    }

    .small-box .inner h3 {
        font-size: 1.5rem;
    }

    .info-box {
        margin-bottom: 10px;
    }

    .info-box-number {
        font-size: 1rem;
    }

    .card-header .card-title {
        font-size: 1rem;
    }

    .table-sm td,
    .table-sm th {
        font-size: 0.85rem;
    }
}

/* Mejorar responsividad de tablas */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Ajustar progress bar en móvil */
@media (max-width: 576px) {
    .progress {
        min-width: 100px;
    }
}
</style>