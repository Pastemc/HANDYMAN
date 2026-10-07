<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index($conversationId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);
            
            $messages = Message::where('conversation_id', $conversationId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($messages);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener mensajes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $conversationId)
    {
        try {
            $user = $request->user();
            $conversation = Conversation::findOrFail($conversationId);

            // Determinar tipo de mensaje
            $messageType = $request->input('message_type', 'text');

            // Validación dinámica según el tipo de mensaje
            if ($messageType === 'location') {
                $validator = Validator::make($request->all(), [
                    'message_type' => 'required|in:text,location',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                    'location_address' => 'nullable|string|max:500',
                ]);
            } else {
                $validator = Validator::make($request->all(), [
                    'message' => 'required|string',
                    'message_type' => 'nullable|in:text,location',
                ]);
            }

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Preparar datos del mensaje
            $messageData = [
                'conversation_id' => $conversationId,
                'sender_id' => $user->id,
                'message_type' => $messageType,
                'is_read' => false,
            ];

            if ($messageType === 'location') {
                $messageData['message'] = '📍 Ubicación compartida';
                $messageData['latitude'] = $request->latitude;
                $messageData['longitude'] = $request->longitude;
                $messageData['location_address'] = $request->location_address;
            } else {
                $messageData['message'] = $request->message;
            }

            // Crear mensaje
            $message = Message::create($messageData);

            // Actualizar timestamp de conversación
            $conversation->touch();

            // Determinar receptor
            $recipientId = null;
            if ($user->id === $conversation->client_id) {
                $recipientId = $conversation->handyman_id;
            } else {
                $recipientId = $conversation->client_id;
            }

            // Notificar al receptor
            if ($recipientId) {
                $notificationMessage = $messageType === 'location' 
                    ? "{$user->name} compartió su ubicación" 
                    : "{$user->name}: {$request->message}";

                Notification::create([
                    'user_id' => $recipientId,
                    'type' => 'message',
                    'title' => 'Nuevo Mensaje',
                    'message' => $notificationMessage,
                    'data' => json_encode([
                        'conversation_id' => $conversationId,
                        'message_id' => $message->id,
                        'message_type' => $messageType,
                    ]),
                ]);
            }

            return response()->json($message->load('sender'), 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request, $conversationId)
    {
        try {
            $user = $request->user();

            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'message' => 'Mensajes marcados como leídos'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al marcar mensajes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}