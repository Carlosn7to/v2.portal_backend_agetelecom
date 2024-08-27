<?php

namespace App\Http\Controllers\HealthChecker;

use App\Http\Controllers\Controller;
use App\Models\HealthChecker\AppResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{

    public function index(Request $request)
    {
        $events = new ApplicationEvents();

        return $events->watchEvents();

    }



}
