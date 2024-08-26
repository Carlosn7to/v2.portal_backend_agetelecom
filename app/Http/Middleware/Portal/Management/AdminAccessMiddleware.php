<?php

namespace App\Http\Middleware\Portal\Management;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('portal')->user();

        if (!$user || $user->privilegio_id !== 36) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
