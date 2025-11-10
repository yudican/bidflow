<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    /**
     * Login API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validasi input
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => true,
                'status_code' => 422,
                'message' => 'Validasi gagal',
                'data' => $validate->errors()
            ], 422);
        }

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => true,
                'status_code' => 404,
                'message' => 'Email tidak terdaftar',
                'data' => null
            ], 404);
        }

        // Cek password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => true,
                'status_code' => 401,
                'message' => 'Password yang Anda masukkan salah',
                'data' => null
            ], 401);
        }

        // Attempt login
        $credentials = $request->only(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'error' => true,
                'status_code' => 401,
                'message' => 'Email atau password salah',
                'data' => null
            ], 401);
        }

        // Generate token untuk API
        $tokenResult = $user->createToken('auth-token')->plainTextToken;
        
        // Get fresh user data with relations
        $userData = User::with('role')->find($user->id);

        $response = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Login berhasil',
            'data' => [
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => new UserResource($userData)
            ]
        ];

        return response()->json($response, 200);
    }

    /**
     * Logout API
     * Revoke current user token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoke current user token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => 'Logout berhasil',
            'data' => null
        ], 200);
    }

    /**
     * Get authenticated user data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = User::with('role')->find($request->user()->id);

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => 'Data user berhasil diambil',
            'data' => new UserResource($user)
        ], 200);
    }

    /**
     * Refresh Token
     * Delete old token and create new one
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $tokenResult = $request->user()->createToken('auth-token')->plainTextToken;
        
        // Get user data
        $userData = User::with('role')->find($request->user()->id);

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => 'Token berhasil di-refresh',
            'data' => [
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => new UserResource($userData)
            ]
        ], 200);
    }

    /**
     * Revoke all tokens for authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'error' => false,
            'status_code' => 200,
            'message' => 'Semua token berhasil dihapus',
            'data' => null
        ], 200);
    }
}
