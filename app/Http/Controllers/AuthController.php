<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();


        $validate = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validate->fails()) {

            return redirect('login')->with(['error' => 'Silahkan isi semua form yang tersedia']);
        }

        if (!$user) {
            return redirect('login')->with(['error' => 'Username tidak terdaftar']);
        }

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return redirect('login')->with(['error' => 'Username atau password salah']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return redirect('login')->with(['error' => 'Unathorized, password yang kamu masukkan tidak sesuai']);
        }
        // dd($user->createToken('auth-token')->plainTextToken);
        return redirect('dashboard');
    }

    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $validate = Validator::make($request->all(), [
            'email'  => 'required|email:rfc,dns',
        ]);


        if ($validate->fails()) {

            return redirect()->back()->with(['message' => 'Silahkan isi semua form yang tersedia', 'type' => 'warning']);
        }

        if (!$user) {

            return redirect()->back()->with(['message' => 'Email tidak terdaftar', 'type' => 'danger']);
        }

        $token = Str::random(64);

        try {
            DB::beginTransaction();
            DB::table('password_resets')->insert(
                ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
            );

            $actionUrl = route('reset.password', ['token' => $token]);
            $notificationData = ['user' => $user->name, 'actionUrl' => $actionUrl];

            // sendNotificationEmail('RST200', $user->email, $attr, [], ['actionUrl' => $actionUrl, 'view' => 'forgot-password']);
            createNotification('RST200', ['user_id' => $user->id], $notificationData, ['brand_id' => $user->brand_id], 'forgot-password');
            DB::commit();


            return redirect()->back()->with(['message' => 'Link Email berhasil dikirim', 'type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()->with(['message' => 'Forgot password gagal', 'type' => 'danger']);
        }
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'alamat' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_id' => 'required',
            'kecamatan_id' => 'required',
            'kelurahan_id' => 'required',
            'kodepos' => 'required',
            'email' => 'required|email:rfc,dns',
            'brand_id' => 'required|number',
            'telepon' => 'required|number',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validate->fails()) {

            return redirect()->back()->with(['message' => 'Silahkan isi semua form yang tersedia', 'type' => 'warning']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            return redirect()->back()->with(['message' => 'Email sudah terdaftar', 'type' => 'danger']);
        }
        $role = Role::find('6ad8072f-a20a-4edb-87c5-dd29d71bc5e8');
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'brand_id' => $request->brand_id,
            'telepon' => formatPhone($request->telepon),
            'password' => Hash::make($request->password),
        ]);

        $detail = [
            'alamat'  => $request->address,
            'provinsi_id'  => $request->provinsi_id,
            'kabupaten_id'  => $request->kabupaten_id,
            'kecamatan_id'  => $request->kecamatan_id,
            'kelurahan_id'  => $request->kelurahan_id,
            'kodepos'  => $request->kodepos,
            'instagram_url'  => $request->instagram_url,
            'shopee_url'  => $request->shopee_url,
            'tokopedia_url'  => $request->tokopedia_url,
            'bukalapak_url'  => $request->bukalapak_url,
            'lazada_url'  => $request->lazada_url,
            'other_url'  => $request->other_url,
            'user_id' => $user->id,
        ];

        $user->roles()->attach();
        $user->teams()->attach(1, ['role' => $role->role_type]);

        UserDetail::create($detail);

        return redirect()->back()->with(['message' => 'Register berhasil', 'type' => 'success']);
    }
}
