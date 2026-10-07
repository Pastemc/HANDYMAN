import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        notifications: [],
        unreadCount: 0,
    },
    
    mutations: {
        SET_NOTIFICATIONS(state, notifications) {
            state.notifications = notifications;
        },
        
        SET_UNREAD_COUNT(state, count) {
            state.unreadCount = count;
        },
        
        ADD_NOTIFICATION(state, notification) {
            state.notifications.unshift(notification);
            if (!notification.is_read) {
                state.unreadCount++;
            }
        },
        
        MARK_AS_READ(state, notificationId) {
            const notification = state.notifications.find(n => n.id === notificationId);
            if (notification && !notification.is_read) {
                notification.is_read = true;
                state.unreadCount--;
            }
        },
        
        MARK_ALL_AS_READ(state) {
            state.notifications.forEach(n => n.is_read = true);
            state.unreadCount = 0;
        },
        
        REMOVE_NOTIFICATION(state, notificationId) {
            const index = state.notifications.findIndex(n => n.id === notificationId);
            if (index !== -1) {
                if (!state.notifications[index].is_read) {
                    state.unreadCount--;
                }
                state.notifications.splice(index, 1);
            }
        },
    },
    
    actions: {
        async fetchNotifications({ commit }, params = {}) {
            try {
                const response = await axios.get('/notifications', { params });
                commit('SET_NOTIFICATIONS', response.data.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async fetchUnreadCount({ commit }) {
            try {
                const response = await axios.get('/notifications/unread/count');
                commit('SET_UNREAD_COUNT', response.data.unread_count);
                return response.data.unread_count;
            } catch (error) {
                throw error;
            }
        },
        
        async markAsRead({ commit }, notificationId) {
            try {
                await axios.post(`/notifications/${notificationId}/read`);
                commit('MARK_AS_READ', notificationId);
            } catch (error) {
                throw error;
            }
        },
        
        async markAllAsRead({ commit }) {
            try {
                await axios.post('/notifications/read-all');
                commit('MARK_ALL_AS_READ');
            } catch (error) {
                throw error;
            }
        },
        
        async deleteNotification({ commit }, notificationId) {
            try {
                await axios.delete(`/notifications/${notificationId}`);
                commit('REMOVE_NOTIFICATION', notificationId);
            } catch (error) {
                throw error;
            }
        },
    },
    
    getters: {
        notifications: state => state.notifications,
        unreadCount: state => state.unreadCount,
        unreadNotifications: state => state.notifications.filter(n => !n.is_read),
    },
};