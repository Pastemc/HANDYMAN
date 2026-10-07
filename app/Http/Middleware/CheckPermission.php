<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $hasPermission = $request->user()->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })->exists();

        if (!$hasPermission) {
            return response()->json(['message' => 'No tiene permisos'], 403);
        }

        return $next($request);
    }
}