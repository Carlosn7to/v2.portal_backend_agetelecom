<?php

namespace App\Http\Middleware\Portal\AgeRv\B2b\Commission;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinancialAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userPrivilege = auth('portal')->user()->privilegio_id;

        if($userPrivilege === 4) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 401);

    }
}
