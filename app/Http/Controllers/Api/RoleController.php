<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        
        // ✅ AGREGAR CONTEO DE USUARIOS
        foreach ($roles as $role) {
            $role->users_count = $role->users()->count();
        }
        
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:roles,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::create($request->only(['name', 'display_name', 'description']));

        if ($request->has('permissions')) {
            $role->permissions()->attach($request->permissions);
        }

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role->load('permissions'),
        ], 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        
        // ✅ AGREGAR CONTEO DE USUARIOS
        $role->users_count = $role->users()->count();
        
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|unique:roles,name,' . $id,
            'display_name' => 'sometimes|string',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role->update($request->only(['name', 'display_name', 'description']));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role->load('permissions'),
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['superadmin', 'admin', 'client', 'handyman'])) {
            return response()->json(['message' => 'No se puede eliminar roles del sistema'], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Rol eliminado exitosamente']);
    }
}