<?php

namespace App\Http\Controllers\Spa\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Jobs\TestQueue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('layouts.client');
    }

    public function login(Request $request)
    {
        $user = User::whereEmail($request->email)->first();


        if (!$user) {
            return response()->json(['message' => 'User tidak terdaftar'], 401);
        }

        if ($user->status != 1) {
            return response()->json(['message' => 'Login gagal, akun kamu telah kami kunci'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unathorized, password yang kamu masukkan tidak sesuai'], 401);
        }



        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Username atau password salah'], 401);
        }
        // TestQueue::dispatch()->onQueue('queue-log');
        return response()->json(['message' => 'Login Berhasil', 'token' => $user->createToken('auth-token')->plainTextToken, 'user' => new UserResource($user), 'redirect' => route('dashboard')]);
    }
}
