import axios from 'axios';

export default {
    namespaced: true,
    
    state: {
        conversations: [],
        activeConversation: null,
        messages: [],
        unreadCount: 0,
    },
    
    mutations: {
        SET_CONVERSATIONS(state, conversations) {
            state.conversations = conversations;
        },
        
        SET_ACTIVE_CONVERSATION(state, conversation) {
            state.activeConversation = conversation;
        },
        
        SET_MESSAGES(state, messages) {
            state.messages = messages;
        },
        
        ADD_MESSAGE(state, message) {
            state.messages.push(message);
        },
        
        SET_UNREAD_COUNT(state, count) {
            state.unreadCount = count;
        },
        
        UPDATE_CONVERSATION_LAST_MESSAGE(state, { conversationId, message }) {
            const conversation = state.conversations.find(c => c.id === conversationId);
            if (conversation) {
                conversation.last_message_at = message.created_at;
                conversation.messages = [message];
            }
        },
    },
    
    actions: {
        async fetchConversations({ commit }, params = {}) {
            try {
                const response = await axios.get('/conversations', { params });
                commit('SET_CONVERSATIONS', response.data);
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async fetchConversation({ commit }, conversationId) {
            try {
                const response = await axios.get(`/conversations/${conversationId}`);
                commit('SET_ACTIVE_CONVERSATION', response.data);
                commit('SET_MESSAGES', response.data.messages);
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async sendMessage({ commit }, { conversationId, message, attachments }) {
            try {
                const formData = new FormData();
                formData.append('message', message);
                
                if (attachments && attachments.length > 0) {
                    attachments.forEach((file, index) => {
                        formData.append(`attachments[${index}]`, file);
                    });
                }
                
                const response = await axios.post(
                    `/conversations/${conversationId}/messages`,
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    }
                );
                
                commit('ADD_MESSAGE', response.data.data);
                commit('UPDATE_CONVERSATION_LAST_MESSAGE', {
                    conversationId,
                    message: response.data.data,
                });
                
                return response.data;
            } catch (error) {
                throw error;
            }
        },
        
        async fetchUnreadCount({ commit }) {
            try {
                const response = await axios.get('/conversations/unread/count');
                commit('SET_UNREAD_COUNT', response.data.unread_count);
                return response.data.unread_count;
            } catch (error) {
                throw error;
            }
        },
        
        async markAsRead({ commit }, conversationId) {
            try {
                await axios.post(`/conversations/${conversationId}/messages/mark-read`);
            } catch (error) {
                throw error;
            }
        },
    },
    
    getters: {
        conversations: state => state.conversations,
        activeConversation: state => state.activeConversation,
        messages: state => state.messages,
        unreadCount: state => state.unreadCount,
    },
};