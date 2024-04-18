<?php

namespace App\Http\Middleware\Integrator\Voalle;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessBilletsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth('portal')->user()->login == 'digitro'){
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 401);

    }
}
