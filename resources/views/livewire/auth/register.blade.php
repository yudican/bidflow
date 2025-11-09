<div class="login-form mb-8">
    <x-loading />
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="pl-2">
                <h2 class="text-left"><strong>Flimty Integration System</strong></h2>
                <p>
                    Form Pendaftaran Pengguna Flimty Integration System
                </p>
            </div>

            <form class='form-register row'>
                <div class="col-md-12">
                    <x-text-field type="text" name="name" label="Nama Lengkap" isreq="*" />
                </div>

                <div class="col-md-6">
                    <x-text-field type="text" name="email" label="Alamat Email" isreq="*" />
                    <x-text-field type="password" name="password" label="Kata Sandi" isreq="*" />
                </div>

                <div class="col-md-6">
                    <x-text-field type="text" name="telepon" label="No. Handphone" isreq="*" />
                    <x-text-field type="password" name="password_confirmation" label="Konfirmasi Kata Sandi" isreq="*" />
                </div>

                <div class="col-md-12">
                    <x-select name="brand_id" label="Brand" multiple ignore>
                        <option value="">Select Type</option>
                        @foreach ($brands as $brand)
                        <option value="{{$brand->id}}">{{$brand->name}}</option>
                        @endforeach
                    </x-select>

                    <x-textarea type="textarea" name="alamat" label="Alamat " placeholder="Masukkan alamat " isreq="*" />

                    <x-select name="provinsi_id" label="Provinsi" ignore isreq="*">
                        <option value="">Select Provinsi</option>
                        @foreach ($provinces as $provinsi)
                        <option value="{{$provinsi->pid}}">{{$provinsi->nama}}</option>
                        @endforeach
                    </x-select>

                    <x-select name="kabupaten_id" label="Kota/Kabupaten" ignore isreq="*">
                        <option value="">Select Kota/Kabupaten</option>
                        @foreach ($kabupatens as $kab)
                        <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
                        @endforeach
                    </x-select>

                    <x-select name="kecamatan_id" label="Kecamatan" ignore isreq="*">
                        <option value="">Select Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                        <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
                        @endforeach
                    </x-select>


                    <x-select name="kelurahan_id" label="Kelurahan" ignore isreq="*">
                        <option value="">Select Kelurahan</option>
                        @foreach ($kelurahans as $kelurahan)
                        <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="col-md-6">
                    <x-text-field type="text" name="instagram_url" label="URL Instagram" />
                    <x-text-field type="text" name="tokopedia_url" label="URL Tokopedia" />
                    <x-text-field type="text" name="bukalapak_url" label="URL Bukalapak" />
                </div>
                <div class="col-md-6">
                    <x-text-field type="text" name="shopee_url" label="URL Shopee" />
                    <x-text-field type="text" name="lazada_url" label="URL Lazada" />
                    <x-text-field type="text" name="other_url" label="Other" />
                </div>


                <div class="form-group">
                    <button type="submit" wire:click="store" class="btn btn-primary w-100 fw-bold" wire:loading.attr="disabled">Daftar</button>
                </div>
            </form>

            <div class="login-account text-center mt-3 mb-4">
                <span class="msg">Sudah memiliki akun ?</span>
                <a href="{{ route('login') }}" id="show-signup" class="link">Masuk</a>
                <br/>
                <p className="text-center text-[#D4D4D4] mt-4 text-sm font-light">Version 2.1.0</p>
            </div>

            
        </div>
    </div>

    @push('scripts')
    <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function(value) {
            // brand_id
            $('#brand_id').select2({
                theme: "bootstrap",
            });
            $('#brand_id').on('change', function (e) {
                let data = $(this).val();
                @this.set('brand_id', data);
            });

            $('#provinsi_id').select2({
                        theme: "bootstrap",
                        width:'auto'
            });
            $('#provinsi_id').on('change', function (e) {
                let data = $(this).val();
                @this.set('provinsi_id', data);
                @this.call('getKabupaten', data);
            });

            $('#kabupaten_id').select2({
                    theme: "bootstrap",
                    width:'auto'
            });
            $('#kabupaten_id').on('change', function (e) {
                let data = $(this).val();
                console.log(data)
                @this.set('kabupaten_id', data);
                @this.call('getKecamatan', data);
            });

            $('#kecamatan_id').select2({
                    theme: "bootstrap",
                    width:'auto'
            });
            $('#kecamatan_id').on('change', function (e) {
                let data = $(this).val();
                @this.set('kecamatan_id', data);
                @this.call('getKelurahan', data);
            });

            $('#kelurahan_id').select2({
                    theme: "bootstrap",
                    width:'auto'
            });
            $('#kelurahan_id').on('change', function (e) {
                let data = $(this).val();
                @this.set('kelurahan_id', data);
                @this.call('getKodepos', data);
            });

            window.livewire.on('loadKabupaten', (data) => {
                console.log(data)
                $('#kabupaten_id').select2({
                    theme: "bootstrap",
                    width:'auto',
                    data
                });
            });
            window.livewire.on('loadKecamatan', (data) => {
                console.log(data)
                $('#kecamatan_id').select2({
                    theme: "bootstrap",
                    width:'auto',
                    data
                });
            });
            window.livewire.on('loadKelurahan', (data) => {
                console.log(data)
                $('#kelurahan_id').select2({
                    theme: "bootstrap",
                    width:'auto',
                    data
                });
            });

            $(document).ready(() => {
            $('.form-register').on('submit', () => {
                return false;
            });
            });
            $('.form-register').keypress((e) => {
            if (e.which === 13) {
                $('.form-register').submit();
            }
        })

        })
    </script>
    @endpush
</div>