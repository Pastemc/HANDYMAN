<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['client', 'handyman', 'serviceRequest']);

        // Si es handyman, solo ver sus reseñas
        $user = $request->user();
        if ($user && $user->hasRole('handyman')) {
            $query->where('handyman_id', $user->id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_request_id' => 'required|exists:service_requests,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $serviceRequest = ServiceRequest::findOrFail($request->service_request_id);

            // Verificar que el servicio esté completado
            if ($serviceRequest->status !== 'completed') {
                return response()->json([
                    'message' => 'Solo puedes calificar servicios completados'
                ], 400);
            }

            // Verificar que sea el cliente de la solicitud
            if ($serviceRequest->client_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para calificar este servicio'
                ], 403);
            }

            // Verificar que no haya calificado antes
            $existingReview = Review::where('service_request_id', $serviceRequest->id)
                ->where('client_id', $user->id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'message' => 'Ya has calificado este servicio'
                ], 400);
            }

            $review = Review::create([
                'service_request_id' => $serviceRequest->id,
                'client_id' => $user->id,
                'handyman_id' => $serviceRequest->handyman_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Notificar al handyman
            Notification::create([
                'user_id' => $serviceRequest->handyman_id,
                'type' => 'review',
                'title' => 'Nueva Calificación Recibida',
                'message' => "{$user->name} te ha calificado con {$request->rating} estrellas",
                'data' => json_encode([
                    'review_id' => $review->id,
                    'rating' => $request->rating,
                ]),
            ]);

            return response()->json([
                'message' => 'Calificación registrada exitosamente',
                'review' => $review->load(['client', 'handyman'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar calificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $review = Review::with(['client', 'handyman', 'serviceRequest'])
            ->findOrFail($id);

        return response()->json($review);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $review = Review::findOrFail($id);

            // Verificar que sea el cliente que hizo la reseña
            if ($review->client_id !== $user->id) {
                return response()->json([
                    'message' => 'No tienes permiso para editar esta calificación'
                ], 403);
            }

            $review->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json([
                'message' => 'Calificación actualizada exitosamente',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar calificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $review = Review::findOrFail($id);

            // Solo el cliente o admin pueden eliminar
            if ($review->client_id !== $user->id && !$user->hasAnyRole(['admin', 'superadmin'])) {
                return response()->json([
                    'message' => 'No tienes permiso para eliminar esta calificación'
                ], 403);
            }

            $review->delete();

            return response()->json([
                'message' => 'Calificación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar calificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener promedio de calificaciones de un handyman
     */
    public function handymanRating($handymanId)
    {
        $reviews = Review::where('handyman_id', $handymanId)->get();
        
        $average = $reviews->avg('rating') ?? 0;
        $count = $reviews->count();

        return response()->json([
            'handyman_id' => $handymanId,
            'average_rating' => round($average, 2),
            'total_reviews' => $count,
            'reviews' => $reviews->load(['client', 'serviceRequest'])
        ]);
    }
}