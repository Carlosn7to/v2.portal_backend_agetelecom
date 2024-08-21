<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use App\Models\HealthChecker\AppResource;
use Illuminate\Http\Request;

class TestController extends Controller
{

    public function index()
    {
        return (new ResourceServer())->response();
    }

}
