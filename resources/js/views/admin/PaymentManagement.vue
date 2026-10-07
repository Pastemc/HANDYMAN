<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $t('payments.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <button 
                        v-if="!currentRegister"
                        @click="showOpenCashModal = true" 
                        class="btn btn-success float-right"
                    >
                        <i class="fas fa-cash-register"></i> {{ $t('payments.open_cash') }}
                    </button>
                    <button 
                        v-else
                        @click="showCloseCashModal = true" 
                        class="btn btn-danger float-right"
                    >
                        <i class="fas fa-lock"></i> {{ $t('payments.close_cash') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Cash Register Status -->
            <div v-if="currentRegister" class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>{{ $t('payments.cash_opened') }}:</strong> {{ formatDateTime(currentRegister.opened_at) }}
                            </div>
                            <div class="col-md-4">
                                <strong>{{ $t('payments.opening_balance') }}:</strong> ${{ formatNumber(currentRegister.opening_balance) }}
                            </div>
                            <div class="col-md-4">
                                <strong>{{ $t('payments.current_balance') }}:</strong> ${{ currentBalance }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ $t('payments.no_cash_open') }}.</strong> {{ $t('payments.must_open_cash') }}.
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>${{ formatNumber(totalTransactions) }}</h3>
                            <p>{{ $t('payments.total_transactions') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ totalPayments || 0 }}</h3>
                            <p>{{ $t('payments.total_payments') }}</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments List -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $t('payments.payment_list') }}</h3>
                    <div class="card-tools">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <select v-model="filters.payment_method" @change="fetchPayments" class="form-control form-control-sm">
                                    <option value="">{{ $t('payments.all_methods') }}</option>
                                    <option value="cash">{{ $t('payments.cash') }}</option>
                                    <option value="credit_card">{{ $t('payments.credit_card') }}</option>
                                    <option value="debit_card">{{ $t('payments.debit_card') }}</option>
                                    <option value="transfer">{{ $t('payments.transfer') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select v-model="filters.status" @change="fetchPayments" class="form-control form-control-sm">
                                    <option value="">{{ $t('requests.all_status') }}</option>
                                    <option value="pending">{{ $t('status.pending') }}</option>
                                    <option value="completed">{{ $t('status.completed') }}</option>
                                    <option value="failed">{{ $t('status.failed') }}</option>
                                    <option value="refunded">{{ $t('status.refunded') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla Desktop -->
                <div class="card-body table-responsive p-0 d-none d-md-block">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>{{ $t('payments.payment_number') }}</th>
                                <th>{{ $t('requests.client') }}</th>
                                <th>{{ $t('requests.handyman') }}</th>
                                <th>{{ $t('payments.amount') }}</th>
                                <th>{{ $t('payments.payment_method') }}</th>
                                <th>{{ $t('status.status') }}</th>
                                <th>{{ $t('forms.date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="payment in payments" :key="payment.id">
                                <td>{{ payment.payment_number }}</td>
                                <td>{{ payment.service_request?.client?.name || '-' }}</td>
                                <td>{{ payment.service_request?.handyman?.name || '-' }}</td>
                                <td>${{ formatNumber(payment.amount) }}</td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ getPaymentMethodText(payment.payment_method) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(payment.status)">
                                        {{ getStatusText(payment.status) }}
                                    </span>
                                </td>
                                <td>{{ formatDate(payment.created_at) }}</td>
                            </tr>
                            <tr v-if="payments.length === 0 && !loading">
                                <td colspan="7" class="text-center">{{ $t('messages.no_data') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Cards Mobile -->
                <div class="card-body d-md-none">
                    <div v-if="loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="payments.length === 0" class="text-center py-4">
                        <p class="text-muted">{{ $t('messages.no_data') }}</p>
                    </div>
                    <div v-else class="payment-cards">
                        <div v-for="payment in payments" :key="payment.id" class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">{{ payment.payment_number }}</h6>
                                    <span class="badge" :class="getStatusBadge(payment.status)">
                                        {{ getStatusText(payment.status) }}
                                    </span>
                                </div>
                                <p class="small text-muted mb-1">
                                    <i class="fas fa-user"></i> {{ payment.service_request?.client?.name || '-' }}
                                </p>
                                <p class="small text-muted mb-2">
                                    <i class="fas fa-tools"></i> {{ payment.service_request?.handyman?.name || '-' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong class="text-success">${{ formatNumber(payment.amount) }}</strong>
                                    <span class="badge badge-secondary">
                                        {{ getPaymentMethodText(payment.payment_method) }}
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">{{ formatDate(payment.created_at) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer clearfix">
                    <ul class="pagination pagination-sm m-0 float-right" v-if="pagination.last_page > 1">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">«</a>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">{{ pagination.current_page }}</span>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">»</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Cash Register Modal -->
    <div v-if="showOpenCashModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title text-white">
                        <i class="fas fa-cash-register"></i> {{ $t('payments.open_cash') }}
                    </h4>
                    <button type="button" class="close text-white" @click="closeOpenModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ $t('payments.opening_balance') }} *</label>
                        <input 
                            v-model.number="openCashForm.opening_balance" 
                            type="number" 
                            step="0.01"
                            min="0"
                            class="form-control" 
                            placeholder="0.00"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label>{{ $t('payments.opening_notes') }}</label>
                        <textarea 
                            v-model="openCashForm.notes" 
                            class="form-control" 
                            rows="3"
                            :placeholder="$t('forms.notes') + '...'"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeOpenModal">
                        {{ $t('buttons.cancel') }}
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-success" 
                        @click="openCashRegister" 
                        :disabled="submitting || openCashForm.opening_balance === null || openCashForm.opening_balance === ''"
                    >
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="fas fa-check"></i>
                        {{ $t('payments.open_cash') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Close Cash Register Modal -->
    <div v-if="showCloseCashModal" class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title text-white">
                        <i class="fas fa-lock"></i> {{ $t('payments.close_cash') }}
                    </h4>
                    <button type="button" class="close text-white" @click="closeCloseModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h5>{{ $t('payments.daily_summary') }}</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-6">{{ $t('payments.opening_balance') }}:</dt>
                            <dd class="col-sm-6">${{ formatNumber(currentRegister?.opening_balance || 0) }}</dd>

                            <dt class="col-sm-6">{{ $t('payments.daily_income') }}:</dt>
                            <dd class="col-sm-6">${{ dailyIncome }}</dd>

                            <dt class="col-sm-6">{{ $t('payments.expected_balance') }}:</dt>
                            <dd class="col-sm-6"><strong>${{ currentBalance }}</strong></dd>
                        </dl>
                    </div>

                    <div class="form-group">
                        <label>{{ $t('payments.real_closing_balance') }} *</label>
                        <input 
                            v-model.number="closeCashForm.closing_balance" 
                            type="number" 
                            step="0.01"
                            min="0"
                            class="form-control" 
                            placeholder="0.00"
                            required
                        >
                    </div>

                    <div v-if="closeCashForm.closing_balance !== null && closeCashForm.closing_balance !== ''" class="alert" :class="getDifferenceClass()">
                        <strong>{{ $t('payments.difference') }}:</strong> ${{ calculateDifference() }}
                    </div>

                    <div class="form-group">
                        <label>{{ $t('payments.closing_notes') }}</label>
                        <textarea 
                            v-model="closeCashForm.notes" 
                            class="form-control" 
                            rows="3"
                            :placeholder="$t('forms.notes') + '...'"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeCloseModal">
                        {{ $t('buttons.cancel') }}
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-danger" 
                        @click="closeCashRegister" 
                        :disabled="submitting || closeCashForm.closing_balance === null || closeCashForm.closing_balance === ''"
                    >
                        <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="fas fa-lock"></i>
                        {{ $t('payments.close_cash') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'PaymentManagement',
    setup() {
        const payments = ref([]);
        const currentRegister = ref(null);
        const loading = ref(false);
        const showOpenCashModal = ref(false);
        const showCloseCashModal = ref(false);
        const submitting = ref(false);

        const filters = reactive({
            payment_method: '',
            status: '',
            page: 1,
        });

        const pagination = ref({
            current_page: 1,
            last_page: 1,
        });

        const totalTransactions = ref(0);
        const totalPayments = ref(0);

        const openCashForm = reactive({
            opening_balance: null,
            notes: '',
        });

        const closeCashForm = reactive({
            closing_balance: null,
            notes: '',
        });

        const dailyIncome = computed(() => {
            if (!payments.value || payments.value.length === 0) return '0.00';
            
            const total = payments.value
                .filter(p => p.status === 'completed' && p.cash_register_id === currentRegister.value?.id)
                .reduce((sum, p) => sum + parseFloat(p.amount || 0), 0);
            
            return total.toFixed(2);
        });

        const currentBalance = computed(() => {
            if (!currentRegister.value) return '0.00';
            const opening = parseFloat(currentRegister.value.opening_balance || 0);
            const income = parseFloat(dailyIncome.value || 0);
            return (opening + income).toFixed(2);
        });

        const formatNumber = (value) => {
            if (value === null || value === undefined || value === '') return '0.00';
            return parseFloat(value).toFixed(2);
        };

        const fetchCurrentRegister = async () => {
            try {
                const response = await axios.get('/cash-registers/current');
                
                if (response.data && response.data.id) {
                    currentRegister.value = response.data;
                } else {
                    currentRegister.value = null;
                }
            } catch (error) {
                currentRegister.value = null;
            }
        };

        const fetchPayments = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/payments', { params: filters });
                payments.value = response.data.data || [];
                pagination.value = {
                    current_page: response.data.current_page || 1,
                    last_page: response.data.last_page || 1,
                };

                console.log('Pagos cargados:', payments.value);
                
            } catch (error) {
                console.error('Error fetching payments:', error);
                payments.value = [];
            } finally {
                loading.value = false;
            }
        };

        const fetchStatistics = async () => {
            try {
                const response = await axios.get('/payments/platform/revenue');
                totalTransactions.value = parseFloat(response.data.total_transactions || 0);
                totalPayments.value = response.data.total_payments || 0;
            } catch (error) {
                console.error('Error fetching statistics:', error);
                totalTransactions.value = 0;
                totalPayments.value = 0;
            }
        };

        const setupRealtimeListeners = () => {
            if (!window.Echo) return;

            window.Echo.channel('payments')
                .listen('.payment.created', (e) => {
                    console.log('Nuevo pago creado:', e.payment);
                    
                    // Agregar al principio de la lista
                    payments.value.unshift(e.payment);
                    
                    // Actualizar totales
                    if (e.payment.status === 'completed') {
                        totalTransactions.value += parseFloat(e.payment.amount || 0);
                        totalPayments.value += 1;
                    }

                    // Toast
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Nuevo pago registrado',
                        showConfirmButton: false,
                        timer: 3000,
                    });
                });
        };

        const openCashRegister = async () => {
            if (openCashForm.opening_balance === null || openCashForm.opening_balance === '' || openCashForm.opening_balance < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingresa un saldo inicial válido',
                });
                return;
            }

            submitting.value = true;
            try {
                await axios.post('/cash-registers/open', {
                    opening_balance: parseFloat(openCashForm.opening_balance),
                    notes: openCashForm.notes || null,
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Caja abierta correctamente',
                    timer: 2000,
                });

                closeOpenModal();
                await fetchCurrentRegister();
                await fetchPayments();
                await fetchStatistics();

            } catch (error) {
                console.error('Error opening cash register:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al abrir la caja',
                });
            } finally {
                submitting.value = false;
            }
        };

        const closeCashRegister = async () => {
            if (closeCashForm.closing_balance === null || closeCashForm.closing_balance === '' || closeCashForm.closing_balance < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor ingresa un saldo de cierre válido',
                });
                return;
            }

            submitting.value = true;
            try {
                await axios.post(`/cash-registers/${currentRegister.value.id}/close`, {
                    closing_balance: parseFloat(closeCashForm.closing_balance),
                    expected_balance: parseFloat(currentBalance.value),
                    notes: closeCashForm.notes || null,
                });

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Caja cerrada correctamente',
                    timer: 2000,
                });

                closeCloseModal();
                currentRegister.value = null;
                await fetchCurrentRegister();
                await fetchPayments();
                await fetchStatistics();

            } catch (error) {
                console.error('Error closing cash register:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Error al cerrar la caja',
                });
            } finally {
                submitting.value = false;
            }
        };

        const calculateDifference = () => {
            if (closeCashForm.closing_balance === null || closeCashForm.closing_balance === '') {
                return '0.00';
            }
            const diff = parseFloat(closeCashForm.closing_balance) - parseFloat(currentBalance.value);
            return diff.toFixed(2);
        };

        const getDifferenceClass = () => {
            const diff = parseFloat(calculateDifference());
            if (diff === 0) return 'alert-success';
            if (diff > 0) return 'alert-info';
            return 'alert-warning';
        };

        const changePage = (page) => {
            if (page >= 1 && page <= pagination.value.last_page) {
                filters.page = page;
                fetchPayments();
            }
        };

        const closeOpenModal = () => {
            showOpenCashModal.value = false;
            openCashForm.opening_balance = null;
            openCashForm.notes = '';
        };

        const closeCloseModal = () => {
            showCloseCashModal.value = false;
            closeCashForm.closing_balance = null;
            closeCashForm.notes = '';
        };

        const getPaymentMethodText = (method) => {
            const methods = {
                cash: 'Efectivo',
                credit_card: 'Tarjeta de Crédito',
                debit_card: 'Tarjeta de Débito',
                transfer: 'Transferencia',
            };
            return methods[method] || method;
        };

        const getStatusBadge = (status) => {
            const badges = {
                pending: 'badge-warning',
                completed: 'badge-success',
                failed: 'badge-danger',
                refunded: 'badge-info',
            };
            return badges[status] || 'badge-secondary';
        };

        const getStatusText = (status) => {
            const texts = {
                pending: 'Pendiente',
                completed: 'Completado',
                failed: 'Fallido',
                refunded: 'Reembolsado',
            };
            return texts[status] || status;
        };

        const formatDate = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('es-ES');
        };

        const formatDateTime = (date) => {
            if (!date) return '-';
            return new Date(date).toLocaleString('es-ES');
        };

        onMounted(async () => {
            await fetchCurrentRegister();
            await fetchPayments();
            await fetchStatistics();
            setupRealtimeListeners();
        });

        onUnmounted(() => {
            if (window.Echo) {
                window.Echo.leave('payments');
            }
        });

        return {
            payments,
            currentRegister,
            loading,
            filters,
            pagination,
            totalTransactions,
            totalPayments,
            showOpenCashModal,
            showCloseCashModal,
            submitting,
            openCashForm,
            closeCashForm,
            dailyIncome,
            currentBalance,
            fetchPayments,
            changePage,
            openCashRegister,
            closeCashRegister,
            calculateDifference,
            getDifferenceClass,
            getPaymentMethodText,
            getStatusBadge,
            getStatusText,
            formatDate,
            formatDateTime,
            formatNumber,
            closeOpenModal,
            closeCloseModal,
        };
    },
};
</script>

<style scoped>
.payment-cards {
    max-height: 600px;
    overflow-y: auto;
}

.payment-cards::-webkit-scrollbar {
    width: 6px;
}

.payment-cards::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.payment-cards::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

@media (max-width: 767.98px) {
    .modal-dialog {
        margin: 10px;
    }
}
</style>