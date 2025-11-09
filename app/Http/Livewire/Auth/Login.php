<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Login extends Component
{
    public $email;
    public $password;
    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {

        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::whereEmail($this->email)->first();


        if (!$user) {
            return $this->emit('showAlertError', ['msg' => 'User tidak terdaftar']);
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            return $this->emit('showAlertError', ['msg' => 'Username atau password salah']);
        }

        if (!Hash::check($this->password, $user->password)) {
            return $this->emit('showAlertError', ['msg' => 'Unathorized, password yang kamu masukkan tidak sesuai']);
        }
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return $this->emit('loginSuccess', ['msg' => 'Login Berhasil', 'token' => $user->createToken('auth-token')->plainTextToken, 'redirect' => redirect('dashboard')]);
    }
}
