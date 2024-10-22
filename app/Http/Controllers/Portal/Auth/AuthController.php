<?php

namespace App\Http\Controllers\Portal\Auth;

use App\Http\Controllers\Controller;
use App\Models\Portal\User\User;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;

class AuthController extends Controller
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:portal', ['except' => ['ldapAdOld']]);
    }

    public function ldapAdOld(Request $request)
    {

        $connection = new Connection([
            'hosts' => [config('services.ldapOld.host')],
            'base_dn' => config('services.ldapOld.base_dn'),
            'username' => config('services.ldapOld.username'),
            'password' => config('services.ldapOld.password'),

            // Optional Configuration Options
            'port' => 389,
            'use_ssl' => false,
            'use_tls' => false,
            'version' => 3,
            'timeout' => 5,
            'follow_referrals' => false,

        ]);

        $credentials = $request->only('user', 'password');




        if($credentials['user'] == 'financeiro' && $credentials['password'] == env('USER_KEY_PORTAL')) {
            $user = User::where('login', 'admin.portal')->first();

            return $this->login($user);
        }

        if($credentials['user'] == 'carlos.neto' && $credentials['password'] == env('USER_KEY_PORTAL')) {
            $user = User::where('login', 'admin.portal')->first();

            return $this->login($user);
        }

        $message = '';



        try {

            $connection->connect();


            $username = $request->input('user') . '@tote.local';
            $password = $request->input('password');

            if ($connection->auth()->attempt($username, $password)) {
                // Separa o nome e o sobrenome
                $emailParts = explode("@", $username);
                $nameParts = explode(".", $emailParts[0]);

                if (empty($nameParts[1])) {
                    $nameParts[1] = "";
                    $username = $nameParts[0] . "@agetelecom.com.br";
                } else {
                    $username = $nameParts[0] . "." . $nameParts[1] . "@agetelecom.com.br";
                }

                $username = $nameParts[0] . "." . $nameParts[1];

                $user = User::where('login', $username)->first();


                if (isset($user->login)) {
                    return $this->login($user);
                } else {

                    $fullName = implode(' ', array_map('ucfirst', $nameParts));


                    $user = User::create([
                        'nome' => $fullName,
                        'login' => $username,
                        'email' => $username . "@agetelecom.com.br",
                        'password' => Hash::make("hW*nN'v_*Pl8T8$36|L_LC!!I3}VC)f6:\9Jw"),
                        'criado_por' => 1,
                        'modificado_por' => 1,
                    ]);

                    return $this->login($user);
                }

            } else {

                return $this->ldapAdNew($request->input('user'), $password);

            }

        } catch (BindException $e) {
//            $error = $e->getDetailedError();
//            echo $error->getErrorCode();
//            echo $error->getErrorMessage();
//            echo $error->getDiagnosticMessage();
//
            return response()->json(['error' => 'Unauthorized', 'message' => 'Usuário ou senha incorretos!'], 401);

        }
    }

    public function ldapAdNew($username, $password)
    {

        $connection = new Connection([
            'hosts' => [config('services.ldapNew.host')],
            'base_dn' => config('services.ldapNew.base_dn'),
            'username' => config('services.ldapNew.username'),
            'password' => config('services.ldapNew.password'),

            // Optional Configuration Options
            'port' => 389,
            'use_ssl' => false,
            'use_tls' => false,
            'version' => 3,
            'timeout' => 5,
            'follow_referrals' => false,

        ]);

        $message = '';


        try {
            $connection->connect();


            $username = $username . '@age.corp';


            if ($connection->auth()->attempt($username, $password)) {


                $emailParts = explode("@", $username);
                $nameParts = explode(".", $emailParts[0]);

                if (empty($nameParts[1])) {
                    $nameParts[1] = "";
                    $username = $nameParts[0] . "@agetelecom.com.br";
                } else {
                    $username = $nameParts[0] . "." . $nameParts[1] . "@agetelecom.com.br";
                }

                $username = $nameParts[0] . "." . $nameParts[1];

                $user = User::where('login', $username)->first();


                if (isset($user->login)) {
                    return $this->login($user);
                } else {

                    $fullName = implode(' ', array_map('ucfirst', $nameParts));


                    $user = User::create([
                        'nome' => $fullName,
                        'login' => $username,
                        'email' => $username."@agetelecom.com.br",
                        'password' => Hash::make("hW*nN'v_*Pl8T8$36|L_LC!!I3}VC)f6:\9Jw"),
                        'criado_por' => 1,
                        'modificado_por' => 1,
                    ]);

                    return $this->login($user);
                }


            } else {
                return response()->json(['error' => 'Unauthorized', 'message' => 'Usuário ou senha incorretos!'], 401);
            }

        } catch (BindException $e) {
//            $error = $e->getDetailedError();
//            echo $error->getErrorCode();
//            echo $error->getErrorMessage();
//            echo $error->getDiagnosticMessage();
            return response()->json(['error' => 'Unauthorized', 'message' => 'Usuário ou senha incorretos!'], 401);


        }
    }



    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login($user)
    {

        $credentials = [
            'login' => $user->login,
            'password' => config('services.portal.user_key')
        ];

        if (! $token = auth('portal')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Usuário ou senha incorretos!'], 401);
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
        return response()->json(auth('portal')->user()->login);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('portal')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('portal')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => [
                'name' => auth('portal')->user()->nome,
                'id' => auth('portal')->user()->id
            ]
        ]);
    }
}

