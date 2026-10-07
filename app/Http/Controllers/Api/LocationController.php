<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\ServiceRequest;
use App\Events\LocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'service_request_id' => 'nullable|exists:service_requests,id',
        ]);

        $user = Auth::user();

        $location = Location::updateOrCreate(
            [
                'user_id' => $user->id,
                'service_request_id' => $request->service_request_id,
            ],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'status' => 'active',
                'last_updated_at' => now(),
            ]
        );

        // Obtener dirección usando geocoding reverso (opcional)
        if (!$location->address) {
            $address = $this->reverseGeocode($request->latitude, $request->longitude);
            $location->update(['address' => $address]);
        }

        broadcast(new LocationUpdated($location))->toOthers();

        return response()->json([
            'message' => 'Ubicación actualizada exitosamente',
            'location' => $location->load('user'),
        ]);
    }

    public function show($serviceRequestId)
    {
        $user = Auth::user();

        $serviceRequest = ServiceRequest::with(['client', 'handyman'])->findOrFail($serviceRequestId);

        // Verificar permisos - CORREGIDO
        $userRoles = $user->roles->pluck('name')->toArray();
        $hasAccess = in_array('superadmin', $userRoles) || 
                     in_array('admin', $userRoles) ||
                     $serviceRequest->client_id === $user->id ||
                     $serviceRequest->handyman_id === $user->id;

        if (!$hasAccess) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $locations = Location::where('service_request_id', $serviceRequestId)
            ->where('status', 'active')
            ->with('user')
            ->orderBy('last_updated_at', 'desc')
            ->get();

        return response()->json([
            'service_request' => $serviceRequest,
            'locations' => $locations,
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        // Verificar permisos - CORREGIDO
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!in_array('superadmin', $userRoles) && !in_array('admin', $userRoles)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $locations = Location::where('status', 'active')
            ->where('last_updated_at', '>=', now()->subHours(24))
            ->with(['user', 'serviceRequest'])
            ->orderBy('last_updated_at', 'desc')
            ->get();

        return response()->json(['locations' => $locations]);
    }

    public function myLocation()
    {
        $user = Auth::user();

        $location = Location::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest('last_updated_at')
            ->first();

        return response()->json(['location' => $location]);
    }

    public function deactivate(Request $request)
    {
        $user = Auth::user();

        Location::where('user_id', $user->id)
            ->update(['status' => 'inactive']);

        return response()->json(['message' => 'Ubicación desactivada']);
    }

    private function reverseGeocode($latitude, $longitude)
    {
        try {
            $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'HeavensHome/1.0');
            $response = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($response, true);
            
            return $data['display_name'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}