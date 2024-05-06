<?php

namespace App\Http\Middleware\Portal\AgeCommunicate;

use App\Http\Controllers\Portal\Auth\AuthController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InfoBipAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $allowedIP = '185.255.11.58';

        if ($request->ip() === $allowedIP) {
            return $next($request);
        }
        // Pega o header Authorization
        $header = $request->header('Authorization');

        // Verifica se o header Authorization está presente
        if (empty($header) || !str_starts_with($header, 'Basic ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Remove o prefixo 'Basic ' para pegar apenas a parte codificada em base64
        $encodedCredentials = substr($header, 6);

        // Decodifica de base64
        $decodedCredentials = base64_decode($encodedCredentials);

        // Divide a string decodificada em username e password
        list($username, $password) = explode(':', $decodedCredentials);

        // Cria um novo Request para passar para o ldapAdOld
        $newRequest = new Request();
        $newRequest->merge([
            'user' => $username,
            'password' => $password
        ]);

        // Chama ldapAdOld do AuthController com o novo Request
        $auth = (new AuthController())->ldapAdOld($newRequest);

        if(auth('portal')->check()){
            return $next($request);
        }


        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
