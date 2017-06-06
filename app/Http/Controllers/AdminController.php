<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{

    public function flushCache()
    {
        Cache::tags('paths')->flush();
        
        return view('message', ['title' => 'Flush cache', 'message' => 'Cache flushed']);
    }
}
