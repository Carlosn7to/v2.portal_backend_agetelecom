<?php

namespace App\Http\Controllers\Test\Portal;

use App\Helpers\Portal\Mail\Notification\Builder;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Portal\Management\User\UserController;
use App\Models\Portal\User\User;
use App\Routines\Portal\Users\UserSync;
use Illuminate\Support\Facades\Hash;

class Functions extends Controller
{

    private $user;

    public function __construct()
    {
//        $this->middleware('portal.master')->only('index');
    }

    public function index()
    {
        set_time_limit(20000000000);

        $userSync = new UserSync();
        $userSync->builder();

        return false;

        $this->user = auth('portal')->user()->getAuthenticatedUserStructure();

        $users = new UserController();

        return $users->getFullUsersInfo(1);




        $json =
            [
                'modules' => [
                    'portal' => [
                        'menu' => [
                            'allowed' => [
                                'user' => false,
                                'management' => false,
                                'master' => false,
                            ],
                        'management' => [
                            'user' => [
                                'allowed' => [
                                    'create' => false,
                                    'read' => false,
                                    'update' => false,
                                    'delete' => false,
                                ],
                            ],
                            'module' => [
                                'allowed' => [
                                    'create' => false,
                                    'read' => false,
                                    'update' => false,
                                    'delete' => false,
                                ],
                            ],
                            'category' => [
                                'allowed' => [
                                    'create' => false,
                                    'read' => false,
                                    'update' => false,
                                    'delete' => false,
                                ],
                            ],
                            'privilege' => [
                                'allowed' => [
                                    'create' => false,
                                    'read' => false,
                                    'update' => false,
                                    'delete' => false,
                                ],
                            ],
                        ]

                        ]
                    ]
                ]
            ];

    }


}
