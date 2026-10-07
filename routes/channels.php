<?php

use Illuminate\Support\Facades\Broadcast;

// Canal público para solicitudes de servicio
Broadcast::channel('service-requests', function ($user) {
    return true; // Público para todos los handymen autenticados
});

// Canal de videollamadas
Broadcast::channel('video-call.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});