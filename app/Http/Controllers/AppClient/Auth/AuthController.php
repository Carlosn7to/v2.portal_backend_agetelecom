<?php

namespace App\Http\Controllers\AppClient\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AppClient\Auth\SendToken;
use App\Models\AppClient\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('appClient.auth', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['cpf', 'password']);


        $user = User::whereCpf($credentials['cpf'])->first();


        if($user) {
            if(Hash::check($credentials['password'], $user->password)) {

                if($user->email_verificado_em === null) {

                    $mail = Mail::mailer('portal')->to($user->email)
                            ->send(new SendToken($user->nome));

                 } else {

                    $token = auth('appClient')->login($user);

                }

            } else {
                return response()->json(['errou senha' => 'Unauthorized'], 401);
            }
        } else {

            $query = 'select p."name", p.email from erp.people p where p.tx_id = \''. $credentials['cpf'] .'\'
            and p.tx_id = \''.$credentials['password'].'\' limit 1';

            $clientVoalle = DB::connection('voalle')->select($query);

            if($clientVoalle) {

                $clientVoalle = $clientVoalle[0];

                $user = User::firstOrCreate([
                    'nome' => $clientVoalle->name,
                    'cpf' => $credentials['cpf'],
                    'email' => $clientVoalle->email,
                    'email_verificado_em' => null,
                    'password' => $credentials['password'],
                ]);

                return response()->json(['criou usuário baseado na voalle' => 'mail'], 201);


            } else {

                if($credentials['cpf'] !== $credentials['password']) {
                    return response()->json(['errou senha' => 'Unauthorized'], 401);
                }

                return response()->json(['nao existe cliente na voalle' => 'Unauthorized'], 401);

            }

            return response()->json(['errou senha' => 'Unauthorized'], 401);
        }


        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('appClient')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('appClient')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('appClient')->factory()->getTTL() * 60
        ]);
    }

}
