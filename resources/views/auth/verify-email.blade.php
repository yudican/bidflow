<x-guest-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="mb-4 text-sm text-gray-600">
                {{ __('Silahkan memverifikasi alamat email Anda dengan mengklik link yang
                        baru saja kami kirimkan kepada Anda? Jika Anda tidak menerima email tersebut, kami akan dengan senang hati mengirimkan
                        email lainnya kepada Anda. Silahkan klik tombol dibawah ini untuk mengirim ulang email verifikasi.') }}
            </div>
            <br>

            @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                {{ __('Email verifikasi telah kami kirim ke alamat email anda silahkan cek secara berkala atau pada spam.') }}
            </div>
            @endif
            <br>
            <div class="flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf

                    <div>
                        <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                            {{ __('Resend Verification Email') }}
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn btn-link">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>