<?php

namespace App\Http\Livewire\Auth;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PasswordReset extends Component
{
  use PasswordValidationRules;
  public $token, $email;
  public $password, $password_confirmation;
  public function mount($token = null)
  {
    $this->token = $token;
    $this->email = request()->get('email');
  }
  public function render()
  {
    return view('livewire.auth.password-reset')->layout('layouts.guest');
  }

  public function resetPassword()
  {
    $rules = [
      'token' => ['required'],
      'password' => 'required|min:8',
      'password_confirmation' => 'required|min:8|same:password',
    ];
    $this->validate($rules);

    $user = User::where('email', $this->email)->first();
    $user->update(['password' => Hash::make($this->password)]);
    $this->_reset();
    $this->emit('showAlert', [
      'msg' => 'Kata Sandi Berhasil Diubah Silahkan Login Ke Akun Anda',
      'redirect' => true,
      'path' => '/login'
    ]);
  }

  public function _reset()
  {
    $this->token = null;
    $this->email = null;
    $this->password = null;
    $this->password_confirmation = null;
  }
}
