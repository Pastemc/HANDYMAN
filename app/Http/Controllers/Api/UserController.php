<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles']);

        // Filtro por búsqueda
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtro por rol
        if ($request->has('role') && $request->role != '') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Filtro por estado
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Excluir usuarios eliminados
        $query->whereNull('deleted_at');

        // Ordenar por ID descendente
        $query->orderBy('id', 'desc');

        // Paginar resultados
        $users = $query->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:client,handyman,admin',
            'status' => 'nullable|in:active,inactive,suspended',
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
                'status' => $request->status ?? 'active',
            ]);

            // Asignar rol
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            // Si es handyman, crear perfil
            if ($request->role === 'handyman') {
                $user->handyman()->create([
                    'bio' => '',
                    'hourly_rate' => 0,
                    'years_experience' => 0,
                    'rating' => 0,
                    'total_jobs' => 0,
                    'approval_status' => 'pending',
                ]);
            }

            // Cargar relaciones
            $user->load('roles', 'handyman');

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $user = User::with(['roles', 'handyman.serviceCategories'])
            ->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive,suspended',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name', 
                'email', 
                'phone', 
                'status', 
                'address', 
                'city', 
                'state', 
                'zip_code'
            ]);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Cargar relaciones
            $user->load('roles', 'handyman');

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Verificar que no sea superadmin
            if ($user->hasRole('superadmin')) {
                return response()->json([
                    'message' => 'No se puede eliminar al Super Administrador'
                ], 403);
            }

            // Soft delete
            $user->delete();

            return response()->json([
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}