<div class="login-form">
  <div class="row">
    <div class="col-md-12 mx-auto">
      <div class="pl-2">
        <h2 class="text-left"><strong>Ubah Kata Sandi</strong></h2>
      </div>
      <x-text-field type="password" name="password" label="Password" />
      <x-text-field type="password" name="password_confirmation" label="Confirm Password" />


      <div class="form-group">
        <button type="button" wire:click="resetPassword" class="btn btn-secondary w-100 fw-bold" wire:loading.attr="disabled">Ubah Kata Sandi</button>
      </div>
      <div class="login-account text-center mt-3">
        <span class="msg">Ingat Kata Sandi ?</span>
        <a href="{{ route('login') }}" id="show-signup" class="link">Masuk</a>
      </div>
    </div>
  </div>
</div>