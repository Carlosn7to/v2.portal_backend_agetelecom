<?php

namespace App\Http\Controllers\Portal\Management\User;

use App\Http\Controllers\Controller;
use App\Models\Portal\Structure\Privilege;
use App\Models\Portal\User\User;
use App\Policies\Portal\Users\UserPolicy;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {


    }

    public function getFullUsersInfo(Request $request)
    {

        if(auth('portal')->user()->cannot('viewAnyUsers', User::class)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if(! $request->has('page')) {
            return response()->json([
                'message' => '"page" parameter not specified.'], 400);
        }

        if($request->has('search')) {
            $users = User::select(['id', 'nome', 'email', 'login', 'privilegio_id', 'setor_id', 'criado_por', 'modificado_por', 'created_at', 'updated_at'])
                ->whereNot('login',  'admin.portal')
                ->where('nome', 'like', '%'.$request->search.'%')
                ->with(['privilege', 'sector', 'createdBy', 'updatedBy'])
                ->paginate(40, ['*'], 'page', $request->page);
            return response()->json(['users' => $users], 200);
        }


        $users = User::select(['id', 'nome', 'email', 'login', 'privilegio_id', 'setor_id', 'criado_por', 'modificado_por', 'created_at', 'updated_at'])
            ->whereNot('login',  'admin.portal')
            ->with(['privilege', 'sector', 'createdBy', 'updatedBy'])
            ->paginate(40, ['*'], 'page', $request->page);


        return response()->json(['users' => $users], 200);





    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

}
