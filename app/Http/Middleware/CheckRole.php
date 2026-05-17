<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request by role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'errors' => null,
            ], 401);
        }

        if (! in_array($request->user()->role, $roles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission',
                'errors' => null,
            ], 403);
        }
        
        return $next($request);
    }
}
