<?php

namespace App\Http\Middleware;

use App\Models\Administrador;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministratorIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $administrador = $request->user();

        if (! $administrador instanceof Administrador || ! $administrador->estado) {
            return response()->json([
                'message' => 'Acceso denegado. La cuenta administrativa está inactiva.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
