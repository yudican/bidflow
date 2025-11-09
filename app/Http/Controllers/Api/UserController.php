<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function updateProfilePhoto(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'foto' => 'required|image',
            'user_id' => 'required',
        ]);

        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 400,
                'message' => 'Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 400);
        }

        if (!$request->hasFile('foto')) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file('foto');
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }

        $user = User::find($request->user_id);

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => 'User Tidak Ditemukan',
                'status_code' => 400,
            ], 400);
        }

        try {
            DB::beginTransaction();
            // $file = $request->foto->store('profile-user', 'public');
            $file = Storage::disk('s3')->put('upload/user', $request->foto, 'public');
            if ($user->profile_photo_path) {
                if (Storage::disk('s3')->exists($user->profile_photo_path)) {
                    Storage::disk('s3')->delete($user->profile_photo_path);
                }
            }

            $user->update([
                'profile_photo_path' => $file,
            ]);

            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'Berhasil, foto profil berhasil diubah',
                'status_code' => 200,
                'data' => new UserResource($user),
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => true,
                'message' => 'Terjadi Kesalahan, foto profil gagal diubah',
                'dev_message' => $th->getMessage(),
                'status_code' => 400,
            ], 400);
        }
    }
}
