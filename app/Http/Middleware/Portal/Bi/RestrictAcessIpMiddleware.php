<?php

namespace App\Http\Middleware\Portal\Bi;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictAcessIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIP = '206.204.248.71'; // IP permitido

        if ($request->ip() !== $allowedIP) {
            return response()->json(['error' => 'unauthorized'], 403);
        }

        return $next($request);
    }
}
