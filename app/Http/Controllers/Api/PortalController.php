<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ClientRegistration;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Mail\NewClientRegistrationMail;
use App\Mail\WelcomeClientMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class PortalController extends Controller
{
    public function getCategories()
    {
        try {
            $categories = ServiceCategory::where('is_active', true)
                ->select('id', 'name', 'description', 'icon', 'images')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function($category) {
                    $imageUrl = null;
                    if ($category->images && is_array($category->images) && count($category->images) > 0) {
                        $imageUrl = Storage::url($category->images[0]);
                    }
                    
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'icon' => $category->icon,
                        'image_url' => $imageUrl,
                        'images' => $category->images,
                        'images_urls' => $category->images_urls ?? []
                    ];
                });

            return response()->json($categories);
        } catch (\Exception $e) {
            Log::error('Error loading categories: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error loading categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid email',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $email = $request->email;

            $user = User::where('email', $email)->first();
            
            if ($user) {
                $hasClientRole = $user->roles()->where('name', 'client')->exists();
                
                if ($hasClientRole) {
                    return response()->json([
                        'status' => 'registered',
                        'message' => 'You are already registered.',
                        'user_data' => [
                            'name' => $user->name ?? '',
                            'email' => $user->email ?? '',
                            'phone' => $user->phone ?? '',
                            'document_type' => $user->document_type ?? '',
                            'document_number' => $user->document_number ?? '',
                            'address' => $user->address ?? '',
                            'city' => $user->city ?? '',
                            'state' => $user->state ?? '',
                            'zip_code' => $user->zip_code ?? '',
                        ]
                    ]);
                }
            }

            $registration = ClientRegistration::where('email', $email)
                ->where('status', 'pending')
                ->first();

            if ($registration) {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'You have a pending registration request.',
                    'user_data' => [
                        'name' => $registration->name ?? '',
                        'email' => $registration->email ?? '',
                        'phone' => $registration->phone ?? '',
                        'document_type' => $registration->document_type ?? '',
                        'document_number' => $registration->document_number ?? '',
                        'address' => $registration->address ?? '',
                        'city' => $registration->city ?? '',
                        'state' => $registration->state ?? '',
                        'zip_code' => $registration->zip_code ?? '',
                    ]
                ]);
            }

            $rejected = ClientRegistration::where('email', $email)
                ->where('status', 'rejected')
                ->first();

            if ($rejected) {
                return response()->json([
                    'status' => 'rejected',
                    'message' => 'Your previous request was rejected. You can submit a new request.',
                    'user_data' => [
                        'name' => $rejected->name ?? '',
                        'email' => $rejected->email ?? '',
                        'phone' => $rejected->phone ?? '',
                        'document_type' => $rejected->document_type ?? '',
                        'document_number' => $rejected->document_number ?? '',
                        'address' => $rejected->address ?? '',
                        'city' => $rejected->city ?? '',
                        'state' => $rejected->state ?? '',
                        'zip_code' => $rejected->zip_code ?? '',
                    ]
                ]);
            }

            return response()->json([
                'status' => 'new',
                'message' => 'You can proceed with registration.',
                'user_data' => null
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking registration: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error checking registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ ACTUALIZADO: Crea ClientRegistration + ServiceRequest para usuarios nuevos
     */
    public function submitRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'document_type' => 'required|in:dni,passport,other',
            'document_number' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:10',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'message' => 'nullable|string|max:1000',
            'document_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Process photos
            $photoPaths = [];
            
            if ($request->hasFile('document_photos')) {
                foreach ($request->file('document_photos') as $index => $photo) {
                    if ($photo && $photo->isValid()) {
                        $filename = 'doc_' . time() . '_' . $index . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                        $path = $photo->storeAs('documents', $filename, 'public');
                        $photoPaths[] = $path;
                    }
                }
            }

            // Check if registered user
            $user = User::where('email', $request->email)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'client');
                })
                ->first();

            if ($user) {
                // ✅ USUARIO REGISTRADO → Solo ServiceRequest
                $serviceRequest = ServiceRequest::create([
                    'request_number' => ServiceRequest::generateRequestNumber(),
                    'client_id' => $user->id,
                    'service_category_id' => $request->service_category_id,
                    'title' => 'Service Request - ' . $user->name,
                    'description' => $request->message ?: 'New service request',
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip_code' => $request->zip_code,
                    'preferred_date' => now()->addDays(1)->format('Y-m-d'),
                    'preferred_time' => '09:00:00',
                    'status' => 'pending',
                    'priority' => 'normal',
                    'photos' => $photoPaths,
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Service request submitted successfully!',
                    'service_request' => $serviceRequest->load(['client', 'serviceCategory']),
                ], 201);
            }

            // ✅ USUARIO NUEVO → ClientRegistration + ServiceRequest (AMBOS)
            
            // 1. Crear ClientRegistration
            $registration = ClientRegistration::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'service_category_id' => $request->service_category_id,
                'message' => $request->message,
                'document_photos' => $photoPaths,
                'status' => 'pending',
            ]);

            // 2. Crear ServiceRequest INMEDIATAMENTE
            $serviceRequest = ServiceRequest::create([
                'request_number' => ServiceRequest::generateRequestNumber(),
                'client_id' => null, // Se llenará cuando se apruebe
                'client_registration_id' => $registration->id,
                'service_category_id' => $request->service_category_id,
                'title' => 'Service Request - ' . $request->name,
                'description' => $request->message ?: 'New service request',
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'preferred_date' => now()->addDays(1)->format('Y-m-d'),
                'preferred_time' => '09:00:00',
                'status' => 'pending',
                'priority' => 'normal',
                'photos' => $photoPaths,
            ]);

            // Send email (non-blocking)
            try {
                Mail::to('claude271109@gmail.com')->send(new NewClientRegistrationMail($registration));
            } catch (\Exception $mailError) {
                Log::error('Email error: ' . $mailError->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Request submitted successfully. We will contact you soon.',
                'registration' => $registration->load('serviceCategory'),
                'service_request' => $serviceRequest->load('serviceCategory'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in submitRegistration', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            
            return response()->json([
                'message' => 'Error submitting request',
                'error' => $e->getMessage(),
                'debug' => [
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
    }

    public function getRegistrations(Request $request)
    {
        try {
            $query = ClientRegistration::with(['serviceCategory']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return response()->json($query->orderBy('created_at', 'desc')->paginate(15));

        } catch (\Exception $e) {
            Log::error('Error in getRegistrations: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error loading registrations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ ACTUALIZADO: Actualiza el ServiceRequest existente al aprobar
     */
    public function approveRegistration($id)
    {
        try {
            DB::beginTransaction();

            $registration = ClientRegistration::findOrFail($id);

            if ($registration->status !== 'pending') {
                return response()->json(['message' => 'Already processed'], 400);
            }

            $existingUser = User::where('email', $registration->email)->first();
            
            if ($existingUser) {
                return response()->json(['message' => 'User already exists'], 400);
            }

            $password = $registration->document_number;

            $user = User::create([
                'name' => $registration->name,
                'email' => $registration->email,
                'password' => Hash::make($password),
                'phone' => $registration->phone,
                'document_type' => $registration->document_type,
                'document_number' => $registration->document_number,
                'address' => $registration->address,
                'city' => $registration->city,
                'state' => $registration->state,
                'zip_code' => $registration->zip_code,
                'status' => 'active',
            ]);

            $clientRole = \App\Models\Role::where('name', 'client')->first();
            $user->roles()->attach($clientRole->id);

            // ✅ Buscar ServiceRequest existente
            $serviceRequest = ServiceRequest::where('client_registration_id', $registration->id)->first();
            
            if ($serviceRequest) {
                // Actualizar el existente
                $serviceRequest->update([
                    'client_id' => $user->id,
                ]);
            } else {
                // Crear si no existe
                $serviceRequest = ServiceRequest::create([
                    'client_registration_id' => $registration->id,
                    'request_number' => ServiceRequest::generateRequestNumber(),
                    'client_id' => $user->id,
                    'service_category_id' => $registration->service_category_id,
                    'title' => 'Service Request - ' . $registration->name,
                    'description' => $registration->message ?: 'New request',
                    'address' => $registration->address,
                    'city' => $registration->city,
                    'state' => $registration->state,
                    'zip_code' => $registration->zip_code,
                    'preferred_date' => now()->addDays(1)->format('Y-m-d'),
                    'preferred_time' => '09:00:00',
                    'status' => 'pending',
                    'priority' => 'normal',
                    'photos' => $registration->document_photos,
                ]);
            }

            $registration->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            try {
                Mail::to($user->email)->send(new WelcomeClientMail($user, $password));
            } catch (\Exception $mailError) {
                Log::error('Email error: ' . $mailError->getMessage());
            }

            DB::commit();

            return response()->json([
                'message' => 'Client approved successfully!',
                'user' => $user->load('roles'),
                'registration' => $registration,
                'service_request' => $serviceRequest->load(['client', 'serviceCategory']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error approving request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ ACTUALIZADO: Cancela el ServiceRequest al rechazar
     */
    public function rejectRegistration($id)
    {
        try {
            DB::beginTransaction();

            $registration = ClientRegistration::findOrFail($id);

            if ($registration->status !== 'pending') {
                return response()->json(['message' => 'Already processed'], 400);
            }

            // Buscar y cancelar ServiceRequest
            $serviceRequest = ServiceRequest::where('client_registration_id', $registration->id)->first();
            
            if ($serviceRequest) {
                $serviceRequest->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            }

            $registration->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Request rejected successfully',
                'registration' => $registration,
                'service_request' => $serviceRequest,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error rejecting request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function lookupZipCode($zipcode)
    {
        try {
            if (!preg_match('/^\d{5}$/', $zipcode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid ZIP code format'
                ], 400);
            }

            $response = Http::timeout(5)->get("https://api.zippopotam.us/us/{$zipcode}");

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ZIP code not found'
                ], 404);
            }

            $data = $response->json();
            $place = $data['places'][0] ?? null;

            if (!$place) {
                return response()->json([
                    'success' => false,
                    'message' => 'No location data available'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'zip_code' => $data['post code'],
                    'city' => $place['place name'],
                    'state' => $place['state'],
                    'state_abbreviation' => $place['state abbreviation'],
                    'latitude' => $place['latitude'],
                    'longitude' => $place['longitude'],
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('ZIP code error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error looking up ZIP code'
            ], 500);
        }
    }
}