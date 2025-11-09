<div class="login-form mb-8">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <form class='form-auth'>
                <div class="pl-2">
                    <h2 class="text-left"><strong>Masuk</strong></h2>
                </div>
                <x-text-field type="text" name="email" label="Email" />
            <div class="form-group form-auth">
                <label for="password" class="placeholder"><b>Password</b></label>
                @if (Route::has('password.request'))
                <a class="link float-right" href="{{ route('password.request') }}">
                    {{ __('Lupa kata sandi?') }}
                </a>
                @endif
                <div class="position-relative">
                    <input id="password" wire:model="password" type="password" name="password" autocomplete="on" class="form-control" required>
                    <div class="show-password">
                        <i class="icon-eye"></i>
                    </div>
                </div>
                <small id="helpId" class="text-danger ml-2">{{ $errors->has('password') ? $errors->first('password') : '' }}</small>
            </div>

            <div class="form-group form-auth">
                <button type="submit" wire:click="login" class="btn btn-primary w-100 fw-bold" wire:loading.attr="disabled">Masuk</button>
            </div>
            </form>

            <div class="login-account text-center mt-3 mb-4">
                <span class="msg">Belum memiliki akun ?</span>
                <a href="{{ route('register') }}" id="show-signup" class="link">Daftar</a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function(value) {
            window.livewire.on('loginSuccess', (data) => {
                localStorage.setItem('token', data.token);
                localStorage.removeItem('menu_id');
                window.location.href = data.redirect;
            });
        })

        $(document).ready(() => {
            $('.form-auth').on('submit', () => {
                return false;
            });
        });
        $('.form-auth').keypress((e) => {
            if (e.which === 13) {
                $('.form-auth').submit();
            }
        })
    </script>
    @endpush
</div>

