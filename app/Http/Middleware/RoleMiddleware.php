<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Harap login terlebih dahulu.'
            ], 401);
        }

        $userRole = strtolower($request->user()->role);
        $allowedRoles = array_map('strtolower', $roles);

        $isAllowed = in_array($userRole, $allowedRoles);

        // Pengecekan alias role (misal 'owner' juga menerima 'pemilik hewan')
        if (!$isAllowed) {
            if (in_array('owner', $allowedRoles) && $userRole === 'pemilik hewan') {
                $isAllowed = true;
            }
            if (in_array('pemilik hewan', $allowedRoles) && $userRole === 'owner') {
                $isAllowed = true;
            }
            if (in_array('pharmacy', $allowedRoles) && $userRole === 'apoteker') {
                $isAllowed = true;
            }
            if (in_array('apoteker', $allowedRoles) && $userRole === 'pharmacy') {
                $isAllowed = true;
            }
        }

        if (!$isAllowed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. Anda tidak memiliki akses ke endpoint ini.'
            ], 403);
        }

        return $next($request);
    }
}
