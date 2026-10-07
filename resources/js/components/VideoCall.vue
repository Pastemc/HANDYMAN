<template>
    <!-- Modal de Llamada Entrante -->
    <div v-if="incomingCall" class="video-call-modal incoming-call" @click.self="rejectCall">
        <div class="call-modal-content animate-pulse">
            <div class="caller-info">
                <div class="caller-avatar">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                <h3 class="mt-3 text-white">{{ incomingCall.caller.name }}</h3>
                <p class="text-light">Videollamada entrante...</p>
                
                <!-- Indicador de llamada -->
                <div class="ringing-indicator mt-3">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring delay-1"></div>
                    <div class="pulse-ring delay-2"></div>
                </div>
            </div>
            
            <div class="call-actions">
                <button @click="acceptCall" class="btn-call btn-accept" title="Aceptar">
                    <i class="fas fa-video"></i>
                </button>
                <button @click="rejectCall" class="btn-call btn-reject" title="Rechazar">
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Videollamada Activa -->
    <div v-if="isCallActive" class="video-call-modal active-call">
        <div class="video-container">
            <!-- Video remoto (pantalla completa) -->
            <video ref="remoteVideo" autoplay playsinline class="remote-video"></video>
            
            <!-- Video local (pequeño en esquina) -->
            <video ref="localVideo" autoplay playsinline muted class="local-video"></video>

            <!-- Placeholder si no hay video remoto -->
            <div v-if="!hasRemoteStream" class="remote-placeholder">
                <div class="placeholder-content">
                    <i class="fas fa-user-circle fa-5x text-white mb-3"></i>
                    <h4 class="text-white">{{ otherParticipantName || 'Conectando...' }}</h4>
                    <div class="spinner-border text-white mt-3" role="status"></div>
                </div>
            </div>

            <!-- Información de llamada -->
            <div class="call-info">
                <span class="call-status">
                    <i class="fas fa-circle text-success"></i>
                    {{ callDuration }}
                </span>
                <span class="participant-name">{{ otherParticipantName }}</span>
            </div>

            <!-- Controles -->
            <div class="call-controls">
                <button 
                    @click="toggleAudio" 
                    class="btn-control"
                    :class="{ 'active': isAudioEnabled, 'muted': !isAudioEnabled }"
                    :title="isAudioEnabled ? 'Silenciar' : 'Activar audio'"
                >
                    <i class="fas" :class="isAudioEnabled ? 'fa-microphone' : 'fa-microphone-slash'"></i>
                </button>
                
                <button 
                    @click="toggleVideo" 
                    class="btn-control"
                    :class="{ 'active': isVideoEnabled, 'muted': !isVideoEnabled }"
                    :title="isVideoEnabled ? 'Apagar cámara' : 'Encender cámara'"
                >
                    <i class="fas" :class="isVideoEnabled ? 'fa-video' : 'fa-video-slash'"></i>
                </button>
                
                <button 
                    @click="endCall" 
                    class="btn-control btn-end-call"
                    title="Finalizar llamada"
                >
                    <i class="fas fa-phone-slash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import SimplePeer from 'simple-peer';
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
    name: 'VideoCall',
    props: {
        currentUserId: {
            type: Number,
            required: true,
        },
    },
    setup(props) {
        const incomingCall = ref(null);
        const isCallActive = ref(false);
        const localVideo = ref(null);
        const remoteVideo = ref(null);
        const localStream = ref(null);
        const peer = ref(null);
        const currentCallId = ref(null);
        const isAudioEnabled = ref(true);
        const isVideoEnabled = ref(true);
        const callStartTime = ref(null);
        const callDuration = ref('00:00');
        const otherParticipantName = ref('');
        const hasRemoteStream = ref(false);
        const ringtone = ref(null);
        const otherUserId = ref(null);
        let durationInterval = null;

        // Crear tono de llamada
        const createRingtone = () => {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 440;
                gainNode.gain.value = 0.3;
                oscillator.type = 'sine';
                
                return { oscillator, audioContext };
            } catch (error) {
                console.error('Error creating ringtone:', error);
                return null;
            }
        };

        const playRingtone = () => {
            try {
                ringtone.value = createRingtone();
                if (ringtone.value) {
                    ringtone.value.oscillator.start();
                }
            } catch (error) {
                console.error('Error playing ringtone:', error);
            }
        };

        const stopRingtone = () => {
            if (ringtone.value) {
                try {
                    ringtone.value.oscillator.stop();
                    ringtone.value.audioContext.close();
                    ringtone.value = null;
                } catch (error) {
                    console.error('Error stopping ringtone:', error);
                }
            }
        };

        const setupMediaStream = async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user',
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                    },
                });

                localStream.value = stream;
                
                if (localVideo.value) {
                    localVideo.value.srcObject = stream;
                }

                console.log('✅ Stream configurado');
                return stream;
            } catch (error) {
                console.error('❌ Error con medios:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo acceder a la cámara o micrófono',
                });
                throw error;
            }
        };

        const initiateCall = async (receiverId, conversationId = null) => {
            try {
                console.log('📞 Iniciando llamada a:', receiverId);
                otherUserId.value = receiverId;
                
                const stream = await setupMediaStream();

                const response = await axios.post('/video-calls/initiate', {
                    receiver_id: receiverId,
                    conversation_id: conversationId,
                });

                currentCallId.value = response.data.video_call.call_id;
                console.log('✅ Llamada iniciada:', currentCallId.value);

                peer.value = new SimplePeer({
                    initiator: true,
                    trickle: false,
                    stream: stream,
                    config: {
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' },
                            { urls: 'stun:stun1.l.google.com:19302' },
                        ],
                    },
                });

                peer.value.on('signal', async (signal) => {
                    console.log('📡 Enviando señal');
                    try {
                        await axios.post(`/video-calls/${currentCallId.value}/signal`, {
                            signal: signal,
                            to: receiverId,
                        });
                    } catch (error) {
                        console.error('Error enviando señal:', error);
                    }
                });

                peer.value.on('stream', (remoteStream) => {
                    console.log('📺 Stream remoto recibido');
                    if (remoteVideo.value) {
                        remoteVideo.value.srcObject = remoteStream;
                        hasRemoteStream.value = true;
                    }
                });

                peer.value.on('error', (err) => {
                    console.error('❌ Peer error:', err);
                });

                peer.value.on('close', () => {
                    console.log('🔌 Conexión cerrada');
                    cleanup();
                });

                isCallActive.value = true;
                startCallTimer();

                Swal.fire({
                    icon: 'info',
                    title: 'Llamando...',
                    text: 'Esperando respuesta',
                    timer: 3000,
                    showConfirmButton: false,
                });

            } catch (error) {
                console.error('❌ Error iniciando llamada:', error);
            }
        };

        const acceptCall = async () => {
            if (!incomingCall.value) return;

            stopRingtone();

            try {
                console.log('✅ Aceptando llamada');
                
                const stream = await setupMediaStream();

                await axios.post(`/video-calls/${incomingCall.value.call_id}/answer`, {
                    status: 'accepted',
                });

                currentCallId.value = incomingCall.value.call_id;
                otherParticipantName.value = incomingCall.value.caller.name;
                otherUserId.value = incomingCall.value.caller.id;

                peer.value = new SimplePeer({
                    initiator: false,
                    trickle: false,
                    stream: stream,
                    config: {
                        iceServers: [
                            { urls: 'stun:stun.l.google.com:19302' },
                            { urls: 'stun:stun1.l.google.com:19302' },
                        ],
                    },
                });

                peer.value.on('signal', async (signal) => {
                    console.log('📡 Enviando señal');
                    try {
                        await axios.post(`/video-calls/${currentCallId.value}/signal`, {
                            signal: signal,
                            to: incomingCall.value.caller.id,
                        });
                    } catch (error) {
                        console.error('Error enviando señal:', error);
                    }
                });

                peer.value.on('stream', (remoteStream) => {
                    console.log('📺 Stream remoto recibido');
                    if (remoteVideo.value) {
                        remoteVideo.value.srcObject = remoteStream;
                        hasRemoteStream.value = true;
                    }
                });

                peer.value.on('error', (err) => {
                    console.error('❌ Peer error:', err);
                });

                peer.value.on('close', () => {
                    console.log('🔌 Conexión cerrada');
                    cleanup();
                });

                isCallActive.value = true;
                incomingCall.value = null;
                startCallTimer();

            } catch (error) {
                console.error('❌ Error aceptando llamada:', error);
            }
        };

        const rejectCall = async () => {
            if (!incomingCall.value) return;

            stopRingtone();

            try {
                await axios.post(`/video-calls/${incomingCall.value.call_id}/answer`, {
                    status: 'rejected',
                });
                incomingCall.value = null;
            } catch (error) {
                console.error('Error rechazando llamada:', error);
            }
        };

        const endCall = async () => {
            try {
                if (currentCallId.value) {
                    await axios.post(`/video-calls/${currentCallId.value}/end`);
                }
                cleanup();
            } catch (error) {
                console.error('Error finalizando llamada:', error);
                cleanup();
            }
        };

        const cleanup = () => {
            if (peer.value) {
                peer.value.destroy();
                peer.value = null;
            }

            if (localStream.value) {
                localStream.value.getTracks().forEach(track => track.stop());
                localStream.value = null;
            }

            if (durationInterval) {
                clearInterval(durationInterval);
                durationInterval = null;
            }

            stopRingtone();

            isCallActive.value = false;
            incomingCall.value = null;
            currentCallId.value = null;
            callStartTime.value = null;
            callDuration.value = '00:00';
            hasRemoteStream.value = false;
            otherUserId.value = null;
        };

        const toggleAudio = () => {
            if (localStream.value) {
                const audioTrack = localStream.value.getAudioTracks()[0];
                if (audioTrack) {
                    audioTrack.enabled = !audioTrack.enabled;
                    isAudioEnabled.value = audioTrack.enabled;
                }
            }
        };

        const toggleVideo = () => {
            if (localStream.value) {
                const videoTrack = localStream.value.getVideoTracks()[0];
                if (videoTrack) {
                    videoTrack.enabled = !videoTrack.enabled;
                    isVideoEnabled.value = videoTrack.enabled;
                }
            }
        };

        const startCallTimer = () => {
            callStartTime.value = Date.now();
            durationInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - callStartTime.value) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                callDuration.value = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        };

        onMounted(() => {
            console.log('🎧 VideoCall montado, usuario:', props.currentUserId);
            
            window.Echo.channel(`video-call.${props.currentUserId}`)
                .listen('.video.call', (event) => {
                    console.log('📨 Evento:', event.type, event);

                    switch (event.type) {
                        case 'incoming':
                            incomingCall.value = event.data;
                            playRingtone();
                            break;
                        case 'accepted':
                            otherParticipantName.value = event.data.receiver.name;
                            Swal.close();
                            break;
                        case 'rejected':
                            Swal.fire({
                                icon: 'info',
                                title: 'Llamada rechazada',
                                timer: 3000,
                            });
                            cleanup();
                            break;
                        case 'ended':
                            Swal.fire({
                                icon: 'info',
                                title: 'Llamada finalizada',
                                timer: 2000,
                            });
                            cleanup();
                            break;
                        case 'signal':
                            if (peer.value && event.data.signal) {
                                peer.value.signal(event.data.signal);
                            }
                            break;
                    }
                });
        });

        onUnmounted(() => {
            cleanup();
        });

        return {
            incomingCall,
            isCallActive,
            localVideo,
            remoteVideo,
            isAudioEnabled,
            isVideoEnabled,
            callDuration,
            otherParticipantName,
            hasRemoteStream,
            initiateCall,
            acceptCall,
            rejectCall,
            endCall,
            toggleAudio,
            toggleVideo,
        };
    },
};
</script>

<style scoped>
.video-call-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.incoming-call {
    backdrop-filter: blur(10px);
}

.call-modal-content {
    text-align: center;
    padding: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.caller-avatar {
    margin-bottom: 20px;
}

.ringing-indicator {
    position: relative;
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.pulse-ring {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    border: 3px solid #28a745;
    border-radius: 50%;
    animation: pulsate 2s ease-out infinite;
}

.pulse-ring.delay-1 {
    animation-delay: 0.5s;
}

.pulse-ring.delay-2 {
    animation-delay: 1s;
}

@keyframes pulsate {
    0% {
        transform: translate(-50%, -50%) scale(0.5);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.5);
        opacity: 0;
    }
}

.call-actions {
    display: flex;
    gap: 30px;
    justify-content: center;
    margin-top: 40px;
}

.btn-call {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: none;
    font-size: 28px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.btn-accept {
    background: #28a745;
    color: white;
}

.btn-accept:hover {
    background: #218838;
    transform: scale(1.1);
}

.btn-reject {
    background: #dc3545;
    color: white;
}

.btn-reject:hover {
    background: #c82333;
    transform: scale(1.1);
}

.video-container {
    position: relative;
    width: 100%;
    height: 100%;
    background: #000;
}

.remote-video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remote-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.local-video {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 200px;
    height: 150px;
    border-radius: 10px;
    border: 3px solid white;
    object-fit: cover;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5);
    z-index: 10;
}

@media (max-width: 768px) {
    .local-video {
        width: 120px;
        height: 90px;
    }
}

.call-info {
    position: absolute;
    top: 20px;
    left: 20px;
    color: white;
    background: rgba(0, 0, 0, 0.6);
    padding: 10px 20px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.call-controls {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 20px;
}

.btn-control {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.3);
    color: white;
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-control.active {
    background: rgba(255, 255, 255, 0.5);
}

.btn-control.muted {
    background: rgba(220, 53, 69, 0.8);
}

.btn-end-call {
    background: #dc3545;
}

@media (max-width: 768px) {
    .btn-control {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}
</style>