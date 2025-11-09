<x-guest-layout>
    <div class="container container-login container-transparent animated fadeIn">
        <h3 class="text-center">Konfirmasi kata sandi.</h3>
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf
            <div class="login-form">
                <x-text-field type="password" name="password" label="Password" />
                <div class="form-group form-action-d-flex mb-3">
                    <button type="submit"
                        class="btn btn-secondary col-md-5 float-right mt-3 mt-sm-0 fw-bold">Konfirmasi</button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>