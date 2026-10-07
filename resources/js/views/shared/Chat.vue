<template>
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Chat</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Conversations List -->
                <div class="col-lg-4 col-md-5 col-12 mb-3 mb-md-0">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Conversations</h3>
                            <div class="card-tools">
                                <button 
                                    v-if="currentUser && currentUser.roles && currentUser.roles.some(r => r.name === 'client')"
                                    @click="createNewConversation"
                                    class="btn btn-sm btn-primary"
                                    title="New conversation with Admin"
                                >
                                    <i class="fas fa-plus"></i> 
                                    <span class="d-none d-sm-inline">Contact Admin</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0 conversation-list">
                            <div v-if="loading && conversations.length === 0" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                            </div>

                            <div v-else-if="conversations.length === 0" class="text-center py-5">
                                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No conversations</p>
                                <button 
                                    v-if="currentUser && currentUser.roles && currentUser.roles.some(r => r.name === 'client')"
                                    @click="createNewConversation"
                                    class="btn btn-primary mt-3"
                                >
                                    <i class="fas fa-plus"></i> Contact Admin
                                </button>
                            </div>

                            <div v-else class="list-group list-group-flush">
                                <a 
                                    v-for="conv in conversations" 
                                    :key="conv.id"
                                    href="#"
                                    @click.prevent="selectConversation(conv)"
                                    class="list-group-item list-group-item-action"
                                    :class="{ 'active': selectedConversation?.id === conv.id }"
                                >
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div class="flex-grow-1 text-truncate">
                                            <h6 class="mb-1 text-truncate">
                                                {{ getConversationName(conv) }}
                                            </h6>
                                            <small class="text-muted text-truncate d-block">
                                                {{ conv.service_request?.request_number || 'General Inquiry' }}
                                            </small>
                                        </div>
                                        <div class="ml-2">
                                            <span v-if="conv.unread_count > 0" class="badge badge-primary badge-pill">
                                                {{ conv.unread_count }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div class="col-lg-8 col-md-7 col-12">
                    <div class="card">
                        <div class="card-header" v-if="selectedConversation">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="mb-2 mb-sm-0">
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                            {{ getConversationName(selectedConversation) }}
                                        </span>
                                    </h3>
                                    <small class="text-muted d-block text-truncate">
                                        {{ selectedConversation.service_request?.request_number || 'General Inquiry' }}
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <!-- Video Call Button (Only Client with Admin/SuperAdmin) -->
                                    <button 
                                        v-if="canStartVideoCall"
                                        @click="startVideoCall"
                                        class="btn btn-sm btn-success mr-2"
                                        title="Start video call"
                                    >
                                        <i class="fas fa-video"></i>
                                        <span class="d-none d-sm-inline ml-1">Video Call</span>
                                    </button>

                                    <!-- Rate Button -->
                                    <button 
                                        v-if="canRateService(selectedConversation)"
                                        @click="showRatingModal"
                                        class="btn btn-sm btn-warning"
                                    >
                                        <i class="fas fa-star"></i> 
                                        <span class="d-none d-sm-inline">Rate Service</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body messages-container" ref="messagesContainer">
                            <div v-if="!selectedConversation" class="text-center no-conversation">
                                <i class="fas fa-comment-dots fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">Select a conversation</h4>
                            </div>

                            <div v-else-if="loadingMessages" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="text-muted mt-2">Loading...</p>
                            </div>

                            <div v-else>
                                <div v-if="messages.length === 0" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No messages</p>
                                    <p class="text-muted">Start the conversation!</p>
                                </div>

                                <div v-else>
                                    <div 
                                        v-for="message in messages" 
                                        :key="message.id"
                                        class="direct-chat-msg"
                                        :class="{ 'right': isMyMessage(message) }"
                                    >
                                        <div class="direct-chat-infos clearfix">
                                            <span class="direct-chat-name" :class="{ 'float-right': isMyMessage(message) }">
                                                {{ message.sender?.name || 'User' }}
                                            </span>
                                            <span class="direct-chat-timestamp" :class="{ 'float-left': isMyMessage(message) }">
                                                {{ formatMessageTime(message.created_at) }}
                                            </span>
                                        </div>

                                        <!-- Text Message -->
                                        <div v-if="message.message_type === 'text'" class="direct-chat-text">
                                            {{ message.message }}
                                        </div>

                                        <!-- Location Message (WhatsApp Style) -->
                                        <div v-else-if="message.message_type === 'location'" class="direct-chat-location">
                                            <div class="location-message-card">
                                                <div class="location-header">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <strong>{{ message.sender?.name || 'User' }}</strong>
                                                </div>
                                                <div class="location-body">
                                                    <p class="mb-1">
                                                        <i class="fas fa-crosshairs text-success"></i>
                                                        <strong>Lat:</strong> {{ parseFloat(message.latitude).toFixed(6) }}
                                                    </p>
                                                    <p class="mb-1">
                                                        <i class="fas fa-crosshairs text-success"></i>
                                                        <strong>Lng:</strong> {{ parseFloat(message.longitude).toFixed(6) }}
                                                    </p>
                                                    <p v-if="message.location_address" class="mb-2 text-muted small">
                                                        <i class="fas fa-map-pin"></i> {{ message.location_address }}
                                                    </p>
                                                    <a 
                                                        :href="getNavigationUrl(message, selectedConversation)"
                                                        target="_blank"
                                                        class="btn btn-success btn-sm btn-block"
                                                    >
                                                        <i class="fas fa-route"></i> Navigate
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer" v-if="selectedConversation">
                            <form @submit.prevent="sendMessage">
                                <div class="input-group">
                                    <!-- Attach Location Button (Clients Only) -->
                                    <div v-if="isClient" class="input-group-prepend">
                                        <button 
                                            type="button" 
                                            class="btn btn-success"
                                            @click="sendLocation"
                                            :disabled="sendingLocation"
                                            title="Share location"
                                        >
                                            <span v-if="sendingLocation" class="spinner-border spinner-border-sm"></span>
                                            <i v-else class="fas fa-map-marker-alt"></i>
                                        </button>
                                    </div>

                                    <input 
                                        v-model="newMessage"
                                        type="text" 
                                        class="form-control" 
                                        placeholder="Type a message..."
                                    >
                                    <span class="input-group-append">
                                        <button type="submit" class="btn btn-primary" :disabled="!newMessage.trim() || sending">
                                            <span v-if="sending" class="spinner-border spinner-border-sm mr-1"></span>
                                            <i v-else class="fas fa-paper-plane"></i> 
                                            <span class="d-none d-sm-inline ml-1">Send</span>
                                        </button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Call Component -->
    <VideoCall 
        ref="videoCallComponent"
        :current-user-id="currentUser?.id"
    />

    <!-- Rating Modal -->
    <div v-if="showRatingModalFlag" class="modal fade show modal-backdrop-custom" @click.self="closeRatingModal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white">
                        <i class="fas fa-star"></i> Rate Service
                    </h4>
                    <button type="button" class="close text-white" @click="closeRatingModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Service Details:</strong> {{ selectedConversation?.service_request?.request_number }}<br>
                        <strong>Handyman:</strong> {{ selectedConversation?.handyman?.name }}
                    </div>

                    <div class="form-group text-center">
                        <label class="d-block mb-3">
                            <strong>Rating:</strong>
                        </label>
                        <div class="rating-stars">
                            <i 
                                v-for="star in 5" 
                                :key="star"
                                @click="ratingForm.rating = star"
                                @mouseover="hoverRating = star"
                                @mouseleave="hoverRating = 0"
                                class="fas fa-star fa-2x fa-lg-3x"
                                :class="{
                                    'text-warning': star <= (hoverRating || ratingForm.rating),
                                    'text-muted': star > (hoverRating || ratingForm.rating)
                                }"
                                style="cursor: pointer; margin: 0 3px;"
                            ></i>
                        </div>
                        <p class="mt-2 text-muted">
                            {{ getRatingText(ratingForm.rating) }}
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Comment (Optional):</label>
                        <textarea 
                            v-model="ratingForm.comment" 
                            class="form-control" 
                            rows="4"
                            placeholder="Your review..."
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeRatingModal">
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        class="btn btn-warning" 
                        @click="submitRating" 
                        :disabled="submittingRating || !ratingForm.rating"
                    >
                        <span v-if="submittingRating" class="spinner-border spinner-border-sm mr-1"></span>
                        <i v-else class="fas fa-star"></i>
                        Submit Review
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { useStore } from 'vuex';
import axios from 'axios';
import Swal from 'sweetalert2';
import VideoCall from '../../components/VideoCall.vue';

export default {
    name: 'Chat',
    components: {
        VideoCall,
    },
    setup() {
        const store = useStore();
        const conversations = ref([]);
        const messages = ref([]);
        const selectedConversation = ref(null);
        const newMessage = ref('');
        const loading = ref(false);
        const loadingMessages = ref(false);
        const sending = ref(false);
        const sendingLocation = ref(false);
        const messagesContainer = ref(null);
        const showRatingModalFlag = ref(false);
        const submittingRating = ref(false);
        const hoverRating = ref(0);
        const videoCallComponent = ref(null);
        let refreshInterval = null;
        let previousScrollHeight = 0;

        const ratingForm = ref({
            rating: 0,
            comment: '',
        });

        const currentUser = computed(() => store.state.auth.user);

        const isClient = computed(() => {
            return currentUser.value?.roles?.some(r => r.name === 'client') || false;
        });

        const isHandyman = computed(() => {
            return currentUser.value?.roles?.some(r => r.name === 'handyman') || false;
        });

        const isAdmin = computed(() => {
            const roles = currentUser.value?.roles?.map(r => r.name) || [];
            return roles.includes('admin') || roles.includes('superadmin');
        });

        const canStartVideoCall = computed(() => {
            if (!selectedConversation.value || !currentUser.value) return false;
            return isClient.value;
        });

        const fetchConversations = async (silent = false) => {
            if (!silent) {
                loading.value = true;
            }
            
            try {
                const response = await axios.get('/conversations');
                conversations.value = response.data;
            } catch (error) {
                console.error('Error fetching conversations:', error);
                if (!silent) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Could not load conversations',
                        timer: 2000,
                    });
                }
            } finally {
                loading.value = false;
            }
        };

        const fetchMessages = async (conversationId, silent = false) => {
            if (!silent) {
                loadingMessages.value = true;
                messages.value = [];
            }
            
            if (messagesContainer.value && silent) {
                previousScrollHeight = messagesContainer.value.scrollHeight;
            }
            
            try {
                const response = await axios.get(`/conversations/${conversationId}/messages`);
                const newMessages = response.data || [];
                
                if (!silent || JSON.stringify(messages.value) !== JSON.stringify(newMessages)) {
                    messages.value = newMessages;
                    
                    try {
                        await axios.post(`/conversations/${conversationId}/messages/mark-read`);
                    } catch (error) {
                        console.error('Error marking as read:', error);
                    }
                    
                    nextTick(() => {
                        if (silent && messagesContainer.value) {
                            const newScrollHeight = messagesContainer.value.scrollHeight;
                            const heightDifference = newScrollHeight - previousScrollHeight;
                            
                            if (heightDifference > 0) {
                                const wasNearBottom = (previousScrollHeight - messagesContainer.value.scrollTop - messagesContainer.value.clientHeight) < 100;
                                
                                if (wasNearBottom) {
                                    scrollToBottom();
                                }
                            }
                        } else {
                            scrollToBottom();
                        }
                    });
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
                if (!silent) {
                    messages.value = [];
                }
            } finally {
                if (!silent) {
                    loadingMessages.value = false;
                }
            }
        };

        const selectConversation = (conversation) => {
            selectedConversation.value = conversation;
            fetchMessages(conversation.id, false);
        };

        const sendMessage = async () => {
            if (!newMessage.value.trim() || !selectedConversation.value) return;

            sending.value = true;
            try {
                const response = await axios.post(`/conversations/${selectedConversation.value.id}/messages`, {
                    message: newMessage.value.trim(),
                    message_type: 'text',
                });

                messages.value.push(response.data);
                newMessage.value = '';

                nextTick(() => {
                    scrollToBottom();
                });
            } catch (error) {
                console.error('Error sending message:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not send message',
                });
            } finally {
                sending.value = false;
            }
        };

        const sendLocation = async () => {
            if (!selectedConversation.value) return;

            if (!navigator.geolocation) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Your browser does not support geolocation',
                });
                return;
            }

            sendingLocation.value = true;

            Swal.fire({
                title: 'Getting location...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    try {
                        let address = null;
                        try {
                            const geoResponse = await fetch(
                                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}&zoom=18`,
                                {
                                    headers: {
                                        'User-Agent': 'HeavensHome/1.0'
                                    }
                                }
                            );
                            const geoData = await geoResponse.json();
                            address = geoData.display_name;
                        } catch (err) {
                            console.error('Error getting address:', err);
                        }

                        const response = await axios.post(`/conversations/${selectedConversation.value.id}/messages`, {
                            message_type: 'location',
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            location_address: address,
                        });

                        messages.value.push(response.data);

                        Swal.fire({
                            icon: 'success',
                            title: 'Location sent!',
                            timer: 2000,
                            showConfirmButton: false,
                        });

                        nextTick(() => {
                            scrollToBottom();
                        });
                    } catch (error) {
                        console.error('Error sending location:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not send location',
                        });
                    } finally {
                        sendingLocation.value = false;
                    }
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    sendingLocation.value = false;
                    
                    let errorMessage = 'Could not get location';
                    
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMessage = 'Location permission denied. Please enable it in settings.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMessage = 'Location information unavailable.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMessage = 'Request timeout.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0,
                }
            );
        };

        const startVideoCall = () => {
            if (!selectedConversation.value || !videoCallComponent.value) return;
            
            const receiverId = selectedConversation.value.client_id === currentUser.value.id
                ? selectedConversation.value.handyman_id
                : selectedConversation.value.client_id;
            
            videoCallComponent.value.initiateCall(receiverId, selectedConversation.value.id);
        };

        const getNavigationUrl = (message, conversation) => {
            if (isHandyman.value && conversation?.service_request?.client_latitude && conversation?.service_request?.client_longitude) {
                return `https://www.google.com/maps/dir/?api=1&origin=current+location&destination=${conversation.service_request.client_latitude},${conversation.service_request.client_longitude}&travelmode=driving`;
            }
            
            return `https://www.google.com/maps/dir/?api=1&destination=${message.latitude},${message.longitude}`;
        };

        const scrollToBottom = () => {
            if (messagesContainer.value) {
                messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
            }
        };

        const createNewConversation = async () => {
            try {
                const response = await axios.post('/conversations');
                conversations.value.unshift(response.data.conversation);
                selectConversation(response.data.conversation);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Conversation created!',
                    text: 'You can now send messages to the administrator',
                    timer: 2000,
                });
            } catch (error) {
                console.error('Error creating conversation:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.message || 'Could not create conversation',
                });
            }
        };

        const canRateService = (conversation) => {
            if (!currentUser.value || !conversation) return false;
            if (!currentUser.value.roles.some(r => r.name === 'client')) return false;
            if (!conversation.service_request_id) return false;
            if (conversation.service_request?.status !== 'completed') return false;
            return true;
        };

        const showRatingModal = () => {
            ratingForm.value = { rating: 0, comment: '' };
            showRatingModalFlag.value = true;
        };

        const closeRatingModal = () => {
            showRatingModalFlag.value = false;
            ratingForm.value = { rating: 0, comment: '' };
            hoverRating.value = 0;
        };

        const getRatingText = (rating) => {
            const texts = {
                0: 'Select a rating',
                1: 'Very Bad',
                2: 'Bad',
                3: 'Regular',
                4: 'Good',
                5: 'Excellent',
            };
            return texts[rating] || '';
        };

        const submitRating = async () => {
            if (!ratingForm.value.rating) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select a rating',
                });
                return;
            }

            submittingRating.value = true;
            try {
                await axios.post('/reviews', {
                    service_request_id: selectedConversation.value.service_request_id,
                    rating: ratingForm.value.rating,
                    comment: ratingForm.value.comment,
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Thank you!',
                    text: 'Your rating has been submitted',
                    timer: 2000,
                });

                closeRatingModal();
                fetchConversations(true);

            } catch (error) {
                console.error('Error submitting rating:', error);
                const message = error.response?.data?.message || 'Could not submit rating';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                });
            } finally {
                submittingRating.value = false;
            }
        };

        const isMyMessage = (message) => {
            return message.sender_id === currentUser.value?.id;
        };

        const getConversationName = (conversation) => {
            if (!currentUser.value) return '';
            
            if (currentUser.value.id === conversation.client_id) {
                return conversation.handyman?.name || 'Administrator';
            } else {
                return conversation.client?.name || 'Client';
            }
        };

        const formatMessageTime = (date) => {
            if (!date) return '';
            const d = new Date(date);
            return d.toLocaleString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
            });
        };

        onMounted(() => {
            fetchConversations(false);
            
            refreshInterval = setInterval(() => {
                fetchConversations(true);
                if (selectedConversation.value) {
                    fetchMessages(selectedConversation.value.id, true);
                }
            }, 10000);
        });

        onUnmounted(() => {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });

        return {
            conversations,
            messages,
            selectedConversation,
            newMessage,
            loading,
            loadingMessages,
            sending,
            sendingLocation,
            messagesContainer,
            currentUser,
            showRatingModalFlag,
            submittingRating,
            hoverRating,
            ratingForm,
            videoCallComponent,
            isClient,
            isHandyman,
            isAdmin,
            canStartVideoCall,
            fetchConversations,
            selectConversation,
            sendMessage,
            sendLocation,
            startVideoCall,
            getNavigationUrl,
            createNewConversation,
            canRateService,
            showRatingModal,
            closeRatingModal,
            getRatingText,
            submitRating,
            isMyMessage,
            getConversationName,
            formatMessageTime,
        };
    },
};
</script>

<style scoped>
/* ============ BASE ============ */
.content-header {
    padding: 1.5rem 0;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.content {
    padding: 1.5rem 0;
}

.container-fluid {
    padding: 0 1rem;
}

/* ============ CARD ============ */
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #343a40;
    margin: 0;
}

.card-tools {
    display: flex;
    gap: 0.5rem;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

/* ============ CONVERSATIONS LIST ============ */
.conversation-list {
    height: 600px;
    overflow-y: auto;
}

.conversation-list::-webkit-scrollbar {
    width: 6px;
}

.conversation-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.conversation-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.conversation-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.list-group-item:hover {
    background: #f8f9fa;
}

.list-group-item.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.list-group-item.active .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
}

/* ============ MESSAGES CONTAINER ============ */
.messages-container {
    height: 500px;
    overflow-y: auto;
    padding: 1.5rem;
    background: #f8f9fa;
}

.messages-container::-webkit-scrollbar {
    width: 6px;
}

.messages-container::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.messages-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.messages-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.no-conversation {
    padding-top: 150px;
}

/* ============ MESSAGES ============ */
.direct-chat-msg {
    margin-bottom: 1.5rem;
    clear: both;
}

.direct-chat-msg.right {
    text-align: right;
}

.direct-chat-infos {
    display: block;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.direct-chat-name {
    font-weight: 600;
    color: #343a40;
}

.direct-chat-timestamp {
    color: #6c757d;
    font-size: 0.75rem;
}

.direct-chat-text {
    display: inline-block;
    border-radius: 1rem;
    padding: 0.75rem 1rem;
    background: white;
    border: 1px solid #dee2e6;
    color: #212529;
    max-width: 70%;
    word-wrap: break-word;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.direct-chat-msg.right .direct-chat-text {
    background: #007bff;
    border-color: #007bff;
    color: white;
}

/* ============ LOCATION MESSAGE ============ */
.direct-chat-location {
    display: inline-block;
    max-width: 70%;
}

.location-message-card {
    background: white;
    border: 2px solid #28a745;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.direct-chat-msg.right .location-message-card {
    border-color: #007bff;
}

.location-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-weight: 600;
}

.direct-chat-msg.right .location-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.location-header i {
    margin-right: 0.5rem;
}

.location-body {
    padding: 1rem;
}

.location-body p {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.location-body i {
    margin-right: 0.5rem;
}

/* ============ BADGES ============ */
.badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-pill {
    border-radius: 10rem;
}

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
    padding: 0.5rem 1rem;
    font-size: 1rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
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

.btn-success {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background: #218838;
    border-color: #218838;
}

.btn-warning {
    background: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover:not(:disabled) {
    background: #e0a800;
    border-color: #e0a800;
}

.btn-secondary {
    background: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    border-color: #5a6268;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-block {
    display: flex;
    width: 100%;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* ============ FORM CONTROLS ============ */
.form-control {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group {
    display: flex;
}

.input-group-prepend,
.input-group-append {
    display: flex;
}

.input-group .form-control {
    flex: 1;
    border-radius: 0;
}

.input-group-prepend .btn {
    border-radius: 0.375rem 0 0 0.375rem;
}

.input-group-append .btn {
    border-radius: 0 0.375rem 0.375rem 0;
}

/* ============ MODAL ============ */
.modal-backdrop-custom {
    display: block !important;
    background: rgba(0, 0, 0, 0.5);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    overflow-y: auto;
    padding: 1rem;
}

.modal-dialog {
    margin: 1.75rem auto;
    max-width: 500px;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.modal-content {
    border-radius: 0.5rem;
    border: none;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.modal-header.bg-warning {
    background: #ffc107 !important;
    border-bottom: none;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
}

.close {
    background: transparent;
    border: none;
    font-size: 2rem;
    font-weight: 300;
    line-height: 1;
    cursor: pointer;
    opacity: 0.8;
}

.close:hover {
    opacity: 1;
}

/* ============ ALERT ============ */
.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    border: 1px solid transparent;
}

.alert-info {
    background: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

/* ============ RATING STARS ============ */
.rating-stars {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.rating-stars i {
    transition: all 0.2s ease;
    cursor: pointer;
}

.rating-stars i:hover {
    transform: scale(1.2);
}

.text-warning {
    color: #ffc107 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* ============ SPINNER ============ */
.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.2rem solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

.spinner-border-sm {
    width: 0.875rem;
    height: 0.875rem;
    border-width: 0.15rem;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* ============ UTILITIES ============ */
.text-center {
    text-align: center;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.text-white {
    color: white !important;
}

.clearfix::after {
    content: "";
    display: table;
    clear: both;
}

.float-left {
    float: left;
}

.float-right {
    float: right;
}

.d-block {
    display: block;
}

.d-flex {
    display: flex;
}

.flex-grow-1 {
    flex-grow: 1;
}

.gap-2 {
    gap: 0.5rem;
}

.mb-0 {
    margin-bottom: 0 !important;
}

.mb-1 {
    margin-bottom: 0.25rem !important;
}

.mb-2 {
    margin-bottom: 0.5rem !important;
}

.mb-3 {
    margin-bottom: 1rem !important;
}

.mt-2 {
    margin-top: 0.5rem !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

.ml-1 {
    margin-left: 0.25rem !important;
}

.ml-2 {
    margin-left: 0.5rem !important;
}

.mr-1 {
    margin-right: 0.25rem !important;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

/* ============ RESPONSIVE - TABLET (768px - 991px) ============ */
@media (max-width: 991px) {
    .conversation-list {
        height: 500px;
    }

    .messages-container {
        height: 450px;
    }
}

/* ============ RESPONSIVE - MOBILE (< 768px) ============ */
@media (max-width: 768px) {
    .content-header {
        padding: 1rem 0;
    }

    .content {
        padding: 1rem 0;
    }

    .card-header {
        padding: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .card-footer {
        padding: 0.75rem 1rem;
    }

    .conversation-list {
        height: 400px;
    }

    .messages-container {
        height: 400px;
        padding: 1rem;
    }

    .no-conversation {
        padding-top: 100px;
    }

    .direct-chat-text {
        max-width: 85%;
    }

    .direct-chat-location {
        max-width: 90%;
    }

    .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }
}

/* ============ RESPONSIVE - MOBILE SMALL (< 576px) ============ */
@media (max-width: 576px) {
    .container-fluid {
        padding: 0 0.75rem;
    }

    .content-header h1 {
        font-size: 1.5rem;
    }

    .card-title {
        font-size: 1.125rem;
    }

    .conversation-list {
        height: 350px;
    }

    .messages-container {
        height: 350px;
        padding: 0.75rem;
    }

    .direct-chat-text {
        padding: 0.5rem 0.75rem;
        font-size: 0.9375rem;
    }

    .location-header {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .location-body {
        padding: 0.75rem;
    }

    .location-body p {
        font-size: 0.8125rem;
    }

    .rating-stars i {
        font-size: 2rem !important;
        margin: 0 2px !important;
    }

    .btn {
        font-size: 0.9375rem;
        padding: 0.5rem 0.75rem;
    }

    .btn-sm {
        font-size: 0.8125rem;
        padding: 0.375rem 0.5rem;
    }

    .modal-body {
        padding: 1rem;
    }

    .modal-footer {
        padding: 0.75rem 1rem;
        flex-direction: column;
    }

    .modal-footer .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .modal-footer .btn:last-child {
        margin-bottom: 0;
    }
}
</style>