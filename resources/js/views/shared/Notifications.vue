<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Notifications</h1>
                </div>
                <div class="col-sm-6">
                    <div class="header-actions">
                        <button 
                            v-if="notifications.length > 0"
                            @click="markAllAsRead" 
                            class="btn btn-primary"
                            :disabled="loading"
                        >
                            <i class="fas fa-check-double"></i> 
                            <span class="d-none d-sm-inline">Mark All as Read</span>
                        </button>
                        <button 
                            v-if="notifications.length > 0"
                            @click="deleteAll" 
                            class="btn btn-danger"
                            :disabled="loading"
                        >
                            <i class="fas fa-trash"></i> 
                            <span class="d-none d-sm-inline">Delete All</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="text-muted mt-3">Loading notifications...</p>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="notifications.length === 0" class="empty-state text-center py-5">
                        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No Notifications</h4>
                        <p class="text-muted">You don't have any notifications at this time</p>
                    </div>

                    <!-- Notifications List -->
                    <div v-else class="notifications-list">
                        <div 
                            v-for="notification in notifications" 
                            :key="notification.id"
                            class="notification-item"
                            :class="{ 'unread': !notification.is_read }"
                        >
                            <div class="notification-content">
                                <div class="notification-icon-wrapper">
                                    <i :class="getNotificationIcon(notification.type)"></i>
                                </div>
                                <div class="notification-body">
                                    <h5 class="notification-title">
                                        {{ notification.title }}
                                        <span v-if="!notification.is_read" class="badge badge-primary">New</span>
                                    </h5>
                                    <p class="notification-message">{{ notification.message }}</p>
                                    <small class="notification-time">
                                        <i class="far fa-clock"></i> {{ formatDate(notification.created_at) }}
                                    </small>
                                </div>
                                <div class="notification-actions">
                                    <button 
                                        v-if="!notification.is_read"
                                        @click="markAsRead(notification.id)" 
                                        class="btn btn-sm btn-outline-primary"
                                        title="Mark as Read"
                                    >
                                        <i class="fas fa-check"></i>
                                        <span class="d-none d-md-inline ml-1">Read</span>
                                    </button>
                                    <button 
                                        @click="deleteNotification(notification.id)" 
                                        class="btn btn-sm btn-outline-danger"
                                        title="Delete"
                                    >
                                        <i class="fas fa-trash"></i>
                                        <span class="d-none d-md-inline ml-1">Delete</span>
                                    </button>
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
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'Notifications',
    setup() {
        const notifications = ref([]);
        const loading = ref(false);
        let intervalId = null;

        const fetchNotifications = async () => {
            loading.value = true;
            try {
                const response = await axios.get('/notifications');
                notifications.value = response.data;
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                loading.value = false;
            }
        };

        const markAsRead = async (id) => {
            try {
                await axios.post(`/notifications/${id}/read`);
                const notification = notifications.value.find(n => n.id === id);
                if (notification) {
                    notification.is_read = true;
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
                Swal.fire('Error', 'Failed to mark notification as read', 'error');
            }
        };

        const markAllAsRead = async () => {
            try {
                await axios.post('/notifications/read-all');
                notifications.value.forEach(n => n.is_read = true);
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'All notifications marked as read',
                    timer: 2000,
                    showConfirmButton: false
                });
            } catch (error) {
                console.error('Error marking all as read:', error);
                Swal.fire('Error', 'Failed to mark all notifications as read', 'error');
            }
        };

        const deleteNotification = async (id) => {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: 'This notification will be deleted',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`/notifications/${id}`);
                    notifications.value = notifications.value.filter(n => n.id !== id);
                    Swal.fire('Deleted!', 'Notification has been deleted', 'success');
                } catch (error) {
                    Swal.fire('Error', 'Failed to delete notification', 'error');
                }
            }
        };

        const deleteAll = async () => {
            const result = await Swal.fire({
                title: 'Delete all notifications?',
                text: 'This action cannot be undone',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete all',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete('/notifications');
                    notifications.value = [];
                    Swal.fire('Deleted!', 'All notifications have been deleted', 'success');
                } catch (error) {
                    Swal.fire('Error', 'Failed to delete notifications', 'error');
                }
            }
        };

        const getNotificationIcon = (type) => {
            const icons = {
                service_request: 'fas fa-briefcase text-primary',
                payment: 'fas fa-dollar-sign text-success',
                message: 'fas fa-envelope text-info',
                assignment: 'fas fa-user-check text-warning',
                completion: 'fas fa-check-circle text-success',
                cancellation: 'fas fa-times-circle text-danger',
                default: 'fas fa-bell text-secondary',
            };
            return icons[type] || icons.default;
        };

        const formatDate = (date) => {
            if (!date) return '';
            const d = new Date(date);
            const now = new Date();
            const diff = Math.floor((now - d) / 1000); // seconds

            if (diff < 60) return 'Just now';
            if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
            if (diff < 86400) return `${Math.floor(diff / 3600)} h ago`;
            if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
            
            return d.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        onMounted(() => {
            fetchNotifications();
            
            // Reload every 30 seconds
            intervalId = setInterval(fetchNotifications, 30000);
        });

        onUnmounted(() => {
            if (intervalId) {
                clearInterval(intervalId);
            }
        });

        return {
            notifications,
            loading,
            markAsRead,
            markAllAsRead,
            deleteNotification,
            deleteAll,
            getNotificationIcon,
            formatDate,
        };
    },
};
</script>

<style scoped>
/* ============ BASE STYLES ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.header-actions .btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    transition: all 0.3s;
}

.header-actions .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ============ CARD ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-body {
    padding: 1.5rem;
}

/* ============ LOADING STATE ============ */
.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
}

/* ============ EMPTY STATE ============ */
.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.5;
}

.empty-state h4 {
    margin-top: 1rem;
    font-weight: 600;
}

.empty-state p {
    font-size: 1rem;
    margin-top: 0.5rem;
}

/* ============ NOTIFICATIONS LIST ============ */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.notification-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1.25rem;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.notification-item:hover {
    transform: translateX(5px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
}

.notification-item.unread {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

/* ============ NOTIFICATION ICON ============ */
.notification-icon-wrapper {
    min-width: 50px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    flex-shrink: 0;
}

.notification-icon-wrapper i {
    font-size: 1.5rem;
}

/* ============ NOTIFICATION BODY ============ */
.notification-body {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 1rem;
    font-weight: 600;
    color: #343a40;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.notification-title .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.notification-message {
    font-size: 0.875rem;
    color: #6c757d;
    margin: 0 0 0.5rem 0;
    line-height: 1.5;
}

.notification-time {
    font-size: 0.8125rem;
    color: #adb5bd;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

/* ============ NOTIFICATION ACTIONS ============ */
.notification-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.notification-actions .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

/* ============ BADGES ============ */
.badge-primary {
    background: #007bff;
    color: white;
}

/* ============ BUTTONS ============ */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.3s;
    cursor: pointer;
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

.btn-danger {
    background: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #c82333;
    border-color: #c82333;
}

.btn-outline-primary {
    color: #007bff;
    border-color: #007bff;
    background: transparent;
}

.btn-outline-primary:hover {
    background: #007bff;
    color: white;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
    background: transparent;
}

.btn-outline-danger:hover {
    background: #dc3545;
    color: white;
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d !important;
}

.text-primary {
    color: #007bff !important;
}

.text-success {
    color: #28a745 !important;
}

.text-info {
    color: #17a2b8 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.text-secondary {
    color: #6c757d !important;
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .notification-content {
        gap: 0.75rem;
    }

    .notification-icon-wrapper {
        min-width: 45px;
        width: 45px;
        height: 45px;
    }

    .notification-icon-wrapper i {
        font-size: 1.25rem;
    }
}

/* ============ RESPONSIVE - MOBILE (< 768px) ============ */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0;
    }

    .content-header .row {
        flex-direction: column;
        gap: 1rem;
    }

    .header-actions {
        justify-content: flex-start;
    }

    .card-body {
        padding: 1rem;
    }

    .notification-item {
        padding: 1rem;
    }

    .notification-content {
        flex-wrap: wrap;
    }

    .notification-actions {
        width: 100%;
        justify-content: flex-end;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #dee2e6;
    }

    .empty-state {
        padding: 2rem 1rem;
    }

    .empty-state i {
        font-size: 3rem !important;
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .header-actions {
        width: 100%;
    }

    .header-actions .btn {
        flex: 1;
        justify-content: center;
        padding: 0.5rem;
        font-size: 0.8125rem;
    }

    .notification-item {
        padding: 0.75rem;
    }

    .notification-icon-wrapper {
        min-width: 40px;
        width: 40px;
        height: 40px;
    }

    .notification-icon-wrapper i {
        font-size: 1.125rem;
    }

    .notification-title {
        font-size: 0.9375rem;
    }

    .notification-message {
        font-size: 0.8125rem;
    }

    .notification-time {
        font-size: 0.75rem;
    }

    .notification-actions .btn {
        padding: 0.375rem 0.5rem;
        font-size: 0.8125rem;
    }

    .empty-state h4 {
        font-size: 1.125rem;
    }

    .empty-state p {
        font-size: 0.875rem;
    }
}

/* ============ ACCESSIBILITY ============ */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* ============ ANIMATIONS ============ */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notification-item {
    animation: fadeIn 0.3s ease-out;
}

/* ============ PRINT STYLES ============ */
@media print {
    .header-actions,
    .notification-actions {
        display: none !important;
    }

    .notification-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>