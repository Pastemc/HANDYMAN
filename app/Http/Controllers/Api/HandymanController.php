<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Handyman;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HandymanController extends Controller
{
    public function index(Request $request)
    {
        $query = Handyman::with(['user', 'serviceCategories']);

        // Filtro por búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado de aprobación
        if ($request->has('approval_status') && $request->approval_status != '') {
            $query->where('approval_status', $request->approval_status);
        }

        // Ordenar por ID descendente
        $query->orderBy('id', 'desc');

        // Paginar resultados
        $handymen = $query->paginate(15);

        return response()->json($handymen);
    }

    public function show($id)
    {
        $handyman = Handyman::with(['user', 'serviceCategories'])
            ->findOrFail($id);

        return response()->json($handyman);
    }

    public function update(Request $request, $id)
    {
        $handyman = Handyman::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'certification' => 'nullable|string',
            'skills' => 'nullable|array',
            'service_categories' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'bio',
                'years_experience',
                'certification',
                'skills',
            ]);

            $handyman->update($data);

            // Actualizar categorías de servicio
            if ($request->has('service_categories')) {
                $handyman->serviceCategories()->sync($request->service_categories);
            }

            // Cargar relaciones
            $handyman->load('user', 'serviceCategories');

            return response()->json([
                'message' => 'Handyman actualizado exitosamente',
                'handyman' => $handyman
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar handyman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $handyman = Handyman::findOrFail($id);
            $handyman->update(['approval_status' => 'approved']);

            return response()->json([
                'message' => 'Handyman aprobado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al aprobar handyman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $handyman = Handyman::findOrFail($id);
            $handyman->update(['approval_status' => 'rejected']);

            return response()->json([
                'message' => 'Handyman rechazado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al rechazar handyman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_available' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $handyman = Handyman::findOrFail($id);
            $handyman->update(['is_available' => $request->is_available]);

            return response()->json([
                'message' => 'Disponibilidad actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar disponibilidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'years_experience' => 'nullable|integer|min:0',
            'certification' => 'nullable|string',
            'skills' => 'nullable|array',
            'service_categories' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'status' => 'active',
            ]);

            // Asignar rol handyman
            $role = Role::where('name', 'handyman')->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            // Crear perfil de handyman
            $handyman = Handyman::create([
                'user_id' => $user->id,
                'bio' => $request->bio ?? '',
                'years_experience' => $request->years_experience ?? 0,
                'certification' => $request->certification,
                'skills' => $request->skills ?? [],
                'rating' => 0,
                'total_jobs' => 0,
                'approval_status' => 'pending',
            ]);

            // Asociar categorías de servicio
            if ($request->has('service_categories')) {
                $handyman->serviceCategories()->sync($request->service_categories);
            }

            // Cargar relaciones
            $handyman->load('user', 'serviceCategories');

            return response()->json([
                'message' => 'Handyman creado exitosamente',
                'handyman' => $handyman
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear handyman',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}