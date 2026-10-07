<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoCall;
use App\Models\Notification;
use App\Events\VideoCallEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class VideoCallController extends Controller
{
    public function initiate(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        $user = $request->user();

        Log::info('Iniciando videollamada', [
            'caller_id' => $user->id,
            'receiver_id' => $request->receiver_id,
        ]);

        // Crear videollamada
        $videoCall = VideoCall::create([
            'call_id' => 'CALL-' . strtoupper(Str::random(12)),
            'caller_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'conversation_id' => $request->conversation_id,
            'status' => 'pending',
        ]);

        // Notificar al receptor
        Notification::create([
            'user_id' => $request->receiver_id,
            'type' => 'video_call',
            'title' => 'Llamada Entrante',
            'message' => "{$user->name} te está llamando",
            'data' => json_encode([
                'call_id' => $videoCall->call_id,
                'caller_id' => $user->id,
                'caller_name' => $user->name,
            ]),
        ]);

        // Broadcast evento
        $eventData = [
            'call_id' => $videoCall->call_id,
            'caller' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        Log::info('Enviando evento de videollamada', [
            'channel' => 'video-call.' . $request->receiver_id,
            'event' => 'video.call',
            'data' => $eventData,
        ]);

        broadcast(new VideoCallEvent('incoming', $eventData, $request->receiver_id))->toOthers();

        return response()->json([
            'message' => 'Llamada iniciada',
            'video_call' => $videoCall,
        ]);
    }

    public function answer(Request $request, $callId)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();
        $user = $request->user();

        if ($videoCall->receiver_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $videoCall->update([
            'status' => $request->status,
            'started_at' => $request->status === 'accepted' ? now() : null,
        ]);

        Log::info('Respondiendo videollamada', [
            'call_id' => $callId,
            'status' => $request->status,
            'caller_id' => $videoCall->caller_id,
        ]);

        // Broadcast respuesta
        broadcast(new VideoCallEvent($request->status, [
            'call_id' => $videoCall->call_id,
            'receiver' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ], $videoCall->caller_id))->toOthers();

        return response()->json([
            'message' => $request->status === 'accepted' ? 'Llamada aceptada' : 'Llamada rechazada',
            'video_call' => $videoCall,
        ]);
    }

    public function end(Request $request, $callId)
    {
        $videoCall = VideoCall::where('call_id', $callId)->firstOrFail();
        $user = $request->user();

        if ($videoCall->caller_id !== $user->id && $videoCall->receiver_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $duration = null;
        if ($videoCall->started_at) {
            $duration = now()->diffInSeconds($videoCall->started_at);
        }

        $videoCall->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration' => $duration,
        ]);

        // Notificar al otro usuario
        $otherUserId = $videoCall->caller_id === $user->id 
            ? $videoCall->receiver_id 
            : $videoCall->caller_id;

        Log::info('Finalizando videollamada', [
            'call_id' => $callId,
            'other_user_id' => $otherUserId,
        ]);

        broadcast(new VideoCallEvent('ended', [
            'call_id' => $videoCall->call_id,
        ], $otherUserId))->toOthers();

        return response()->json([
            'message' => 'Llamada finalizada',
            'video_call' => $videoCall,
        ]);
    }

    public function signal(Request $request, $callId)
    {
        $request->validate([
            'signal' => 'required',
            'to' => 'required|exists:users,id',
        ]);

        Log::info('Enviando señal WebRTC', [
            'call_id' => $callId,
            'from' => $request->user()->id,
            'to' => $request->to,
        ]);

        // Reenviar señal WebRTC
        broadcast(new VideoCallEvent('signal', [
            'call_id' => $callId,
            'signal' => $request->signal,
            'from' => $request->user()->id,
        ], $request->to))->toOthers();

        return response()->json(['message' => 'Señal enviada']);
    }
}