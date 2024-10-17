<?php

namespace App\Http\Controllers\Portal\AgeReport\Management\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\AgeReport\UserRoleRequest;
use App\Models\Portal\User\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getUserByName($name)
    {
        $users = User::where('nome','like',  "$name".'%')
            ->with('ageReportRoles')
            ->limit(50)
            ->get(['id', 'nome as name', 'login']);


        return response()->json($users);
    }


}
