<?php

namespace App\Http\Middleware\Portal\Magement;

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
        if(auth('portal')->user()->privilegio_id !== 2) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
