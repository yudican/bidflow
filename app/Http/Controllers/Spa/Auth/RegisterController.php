<?php

namespace App\Http\Controllers\Spa\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function index()
    {
        return view('layouts.client');
    }

    public function register(Request $request)
    {
        # code...
        // register data
    }
}
