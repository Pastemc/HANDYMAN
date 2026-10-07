<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\ClientRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ServiceRequestController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = ServiceRequest::with([
                'client:id,name,email,phone',
                'serviceCategory:id,name',
                'handyman:id,name,email,phone',
                'clientRegistration:id,name,email,phone'
            ]);

            // Role-based filtering
            if ($user->hasRole('client')) {
                $query->where('client_id', $user->id);
            } elseif ($user->hasRole('handyman')) {
                $query->where('handyman_id', $user->id);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('request_number', 'like', "%{$search}%")
                      ->orWhere('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('client', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('clientRegistration', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Category filter
            if ($request->filled('service_category_id')) {
                $query->where('service_category_id', $request->service_category_id);
            }

            // Priority filter
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $serviceRequests = $query->orderBy('created_at', 'desc')->paginate(15);

            // Add photos_urls to each request
            $serviceRequests->getCollection()->transform(function ($request) {
                if ($request->photos && is_array($request->photos)) {
                    $request->photos_urls = array_map(function($photo) {
                        return Storage::url($photo);
                    }, $request->photos);
                } else {
                    $request->photos_urls = [];
                }
                return $request;
            });

            Log::info('Service requests retrieved', [
                'total' => $serviceRequests->total(),
                'user_role' => $user->roles->pluck('name'),
                'sample_photos' => $serviceRequests->first() ? [
                    'id' => $serviceRequests->first()->id,
                    'request_number' => $serviceRequests->first()->request_number,
                    'photos' => $serviceRequests->first()->photos,
                    'photos_urls' => $serviceRequests->first()->photos_urls
                ] : null
            ]);

            return response()->json($serviceRequests);

        } catch (\Exception $e) {
            Log::error('Error retrieving service requests: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error retrieving service requests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:10',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();

            // Process photos
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    if ($photo && $photo->isValid()) {
                        $filename = 'req_' . time() . '_' . $index . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                        $path = $photo->storeAs('documents', $filename, 'public');
                        $photoPaths[] = $path;
                    }
                }
            }

            $serviceRequest = ServiceRequest::create([
                'request_number' => ServiceRequest::generateRequestNumber(),
                'client_id' => $user->id,
                'service_category_id' => $request->service_category_id,
                'title' => $request->title,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'status' => 'pending',
                'priority' => $request->priority ?? 'normal',
                'photos' => $photoPaths,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Service request created successfully',
                'service_request' => $serviceRequest->load(['client', 'serviceCategory'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating service request: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error creating service request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $serviceRequest = ServiceRequest::with([
                'client:id,name,email,phone,address,city,state,zip_code',
                'serviceCategory:id,name,description',
                'handyman:id,name,email,phone',
                'clientRegistration:id,name,email,phone,address,city,state,zip_code',
                'estimations'
            ])->findOrFail($id);

            // Add photos URLs
            if ($serviceRequest->photos && is_array($serviceRequest->photos)) {
                $serviceRequest->photos_urls = array_map(function($photo) {
                    return Storage::url($photo);
                }, $serviceRequest->photos);
            } else {
                $serviceRequest->photos_urls = [];
            }

            return response()->json($serviceRequest);

        } catch (\Exception $e) {
            Log::error('Error retrieving service request: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Service request not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'service_category_id' => 'nullable|exists:service_categories,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:10',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'handyman_id' => 'nullable|exists:users,id',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $serviceRequest = ServiceRequest::findOrFail($id);

            // Process new photos
            $photoPaths = $serviceRequest->photos ?? [];
            
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $index => $photo) {
                    if ($photo && $photo->isValid()) {
                        $filename = 'req_' . time() . '_' . $index . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                        $path = $photo->storeAs('documents', $filename, 'public');
                        $photoPaths[] = $path;
                    }
                }
            }

            // Build update data
            $updateData = array_filter([
                'service_category_id' => $request->service_category_id,
                'title' => $request->title,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'preferred_date' => $request->preferred_date,
                'preferred_time' => $request->preferred_time,
                'status' => $request->status,
                'priority' => $request->priority,
                'handyman_id' => $request->handyman_id,
            ], fn($value) => $value !== null);

            if ($request->hasFile('photos')) {
                $updateData['photos'] = $photoPaths;
            }

            // Update timestamps based on status
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'in_progress':
                        $updateData['started_at'] = now();
                        break;
                    case 'completed':
                        $updateData['completed_at'] = now();
                        break;
                    case 'cancelled':
                        $updateData['cancelled_at'] = now();
                        break;
                }
            }

            $serviceRequest->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Service request updated successfully',
                'service_request' => $serviceRequest->fresh()->load([
                    'client',
                    'serviceCategory',
                    'handyman',
                    'clientRegistration'
                ])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating service request: ' . $e->getMessage(), [
                'id' => $id,
                'request_data' => $request->except('photos')
            ]);
            
            return response()->json([
                'message' => 'Error updating service request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $serviceRequest = ServiceRequest::findOrFail($id);

            // Delete associated photos
            if ($serviceRequest->photos && is_array($serviceRequest->photos)) {
                foreach ($serviceRequest->photos as $photo) {
                    if (Storage::disk('public')->exists($photo)) {
                        Storage::disk('public')->delete($photo);
                    }
                }
            }

            $serviceRequest->delete();

            return response()->json([
                'message' => 'Service request deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting service request: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error deleting service request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignHandyman(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'handyman_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $serviceRequest = ServiceRequest::findOrFail($id);

            // Verify handyman has handyman role
            $handyman = User::findOrFail($request->handyman_id);
            if (!$handyman->hasRole('handyman')) {
                return response()->json([
                    'message' => 'User is not a handyman'
                ], 400);
            }

            $serviceRequest->update([
                'handyman_id' => $request->handyman_id,
                'status' => 'in_progress',
                'started_at' => now()
            ]);

            return response()->json([
                'message' => 'Handyman assigned successfully',
                'service_request' => $serviceRequest->fresh()->load([
                    'client',
                    'serviceCategory',
                    'handyman'
                ])
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning handyman: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error assigning handyman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getHandymen()
    {
        try {
            $handymen = User::whereHas('roles', function($query) {
                $query->where('name', 'handyman');
            })
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'phone')
            ->orderBy('name', 'asc')
            ->get();

            return response()->json($handymen);

        } catch (\Exception $e) {
            Log::error('Error retrieving handymen: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Error retrieving handymen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}