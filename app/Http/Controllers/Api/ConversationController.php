<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Conversation::with(['client', 'handyman', 'serviceRequest']);

        // SuperAdmin y Admin pueden ver TODAS las conversaciones
        if ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            // Ver todas las conversaciones
        } 
        // Cliente ve sus conversaciones
        elseif ($user->hasRole('client')) {
            $query->where('client_id', $user->id);
        } 
        // Handyman ve sus conversaciones
        elseif ($user->hasRole('handyman')) {
            $query->where('handyman_id', $user->id);
        }

        $conversations = $query->orderBy('updated_at', 'desc')->get();

        // Agregar contador de mensajes no leídos
        foreach ($conversations as $conversation) {
            $conversation->unread_count = $conversation->messages()
                ->where('is_read', false)
                ->where('sender_id', '!=', $user->id)
                ->count();
        }

        return response()->json($conversations);
    }

    public function show($id)
    {
        $conversation = Conversation::with([
            'client',
            'handyman',
            'serviceRequest',
            'messages' => function ($query) {
                $query->with('sender')->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        return response()->json($conversation);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        $count = 0;

        if ($user->hasRole('client')) {
            $count = Conversation::where('client_id', $user->id)
                ->whereHas('messages', function ($query) use ($user) {
                    $query->where('is_read', false)
                          ->where('sender_id', '!=', $user->id);
                })
                ->count();
        } elseif ($user->hasRole('handyman')) {
            $count = Conversation::where('handyman_id', $user->id)
                ->whereHas('messages', function ($query) use ($user) {
                    $query->where('is_read', false)
                          ->where('sender_id', '!=', $user->id);
                })
                ->count();
        } elseif ($user->hasRole('superadmin') || $user->hasRole('admin')) {
            $count = Conversation::whereHas('messages', function ($query) use ($user) {
                $query->where('is_read', false)
                      ->where('sender_id', '!=', $user->id);
            })->count();
        }

        return response()->json(['count' => $count]);
    }

    /**
     * Crear nueva conversación (Cliente contacta admin)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Buscar un admin disponible
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first();

        if (!$admin) {
            $admin = User::whereHas('roles', function($q) {
                $q->where('name', 'superadmin');
            })->first();
        }

        if (!$admin) {
            return response()->json([
                'message' => 'No hay administradores disponibles'
            ], 404);
        }

        // Crear conversación general sin solicitud de servicio
        $conversation = Conversation::create([
            'client_id' => $user->id,
            'handyman_id' => $admin->id,
            'service_request_id' => null,
        ]);

        return response()->json([
            'message' => 'Conversación creada',
            'conversation' => $conversation->load(['client', 'handyman'])
        ], 201);
    }
}