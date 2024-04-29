<?php

namespace App\Http\Middleware\Portal;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(auth('portal')->check()){
            return $next($request);
        }

        // Pega o header Authorization
        $header = $request->header('Authorization');

        // Remove o prefixo 'Basic ' para pegar apenas a parte codificada em base64
        $encodedCredentials = substr($header, 6);

        // Decodifica de base64
        $decodedCredentials = base64_decode($encodedCredentials);

        // Divide a string decodificada em username e password
        list($username, $password) = explode(':', $decodedCredentials);



        \Log::info('Middleware acessado e recusado.', [
            'username' => $username,
            'password' => $password
        ]);

        return response()->json(['error' => 'Unauthorized'], 401);

    }
}
