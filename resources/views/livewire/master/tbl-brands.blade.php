<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        @if ($form_active)
                        <span>
                            <!--<i class="fas fa-arrow-left mr-3"></i>-->Form Pengisian Data Brand
                        </span>
                        @else
                        <span>List Data Brand</span>
                        @endif
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($form_active)
            <div class="card">
                <div class="card-body row">
                    <div class="col-md-6">
                        <x-text-field type="text" name="code" label="Kode Brand" placeholder="Masukkan kode brand" isreq="*" />
                        <x-text-field type="text" name="email" label="Email" placeholder="Masukkan alamat email" isreq="*" />
                        <x-text-field type="number" name="phone" label="Phone" placeholder="Masukkan noor telepon aktif" isreq="*" />
                    </div>
                    <div class="col-md-6">
                        <x-text-field type="text" name="name" label="Nama Brand" placeholder="Masukkan nama brand baru" isreq="*" />
                        <x-text-field type="text" name="slug" label="Slug (otomatis)" readonly />
                        <x-select name="status" label="Status" ignore>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Non Active</option>
                        </x-select>
                    </div>
                    <div class="col-md-4">
                        <x-text-field type="text" name="twitter" label="Link Twitter" placeholder="https://www.twitter.com/flimty" />
                    </div>
                    <div class="col-md-4">
                        <x-text-field type="text" name="facebook" label="Link Facebook" placeholder="https://www.facebook.com/flimty" />
                    </div>
                    <div class="col-md-4">
                        <x-text-field type="text" name="instagram" label="Link Instagram" placeholder="https://www.instagram.com/flimty" />
                    </div>
                    <div class="col-md-12">
                        <x-textarea type="textarea" name="address" label="Alamat Brand" placeholder="Masukkan alamat brand" isreq="*" />
                    </div>
                    <div class="col-md-6">
                        <x-select name="provinsi_id" label="Provinsi" ignore>
                            <option value="">Select Provinsi</option>
                            @foreach ($provinces as $provinsi)
                            <option value="{{$provinsi->pid}}">{{$provinsi->nama}}</option>
                            @endforeach
                        </x-select>

                        <x-select name="kecamatan_id" label="Kecamatan" ignore>
                            <option value="">Select Kecamatan</option>
                            @foreach ($kecamatans as $kecamatan)
                            <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="col-md-6">
                        <x-select name="kabupaten_id" label="Kota/Kabupaten" ignore>
                            <option value="">Select Kota/Kabupaten</option>
                            @foreach ($kabupatens as $kab)
                            <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
                            @endforeach
                        </x-select>
                        <x-select name="kelurahan_id" label="Kelurahan" ignore>
                            <option value="">Select Kelurahan</option>
                            @foreach ($kelurahans as $kelurahan)
                            <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="col-md-12">
                        <x-input-photo foto="{{$logo}}" path="{{optional($logo_path)->temporaryUrl()}}" name="logo_path" label="Logo" />
                    </div>

                    <div class="col-md-12">
                        <x-textarea type="textarea" name="description" label="Deskripsi Brand" placeholder="Masukkan deskripsi brand" isreq="*" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Customer Support (Optional)</label>
                            <table class="table table-lightss">
                                <thead class="thead-lightss">
                                    <tr>
                                        <th width="40%" class="p-0">Type <span style="color:red">*</span></th>
                                        <th width="20%" class="p-0">Value <span style="color:red">*</span></th>
                                        <th width="20%" class="p-0">Status <span style="color:red">*</span></th>
                                        <th width="10%" class="p-0"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($index = 0; $index < count($inputs); $index++) <tr>
                                        <td class="p-0">
                                            <x-select name="cs_type.{{$index}}">
                                                <option value="">Select Type</option>
                                                <option value="whatsapp">Whatsapp</option>
                                                <option value="email">Email</option>
                                                <option value="phone">Telephone</option>
                                            </x-select>
                                        </td>
                                        <td class="p-0">
                                            <x-text-field type="text" name="cs_value.{{$index}}" />
                                        </td>
                                        <td class="p-0">
                                            <x-select name="cs_status.{{$index}}">
                                                <option value="">Select Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Non Active</option>
                                            </x-select>
                                        </td>
                                        <td class="p-0">
                                            @if ($index > 0)
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-times" wire:click="remove({{$index}})"></i></button>
                                            @else
                                            <button class="btn btn-success btn-sm"><i class="fas fa-plus" wire:click="add({{$index}})"></i></button>
                                            @endif
                                        </td>
                                        </tr>
                                        @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}" wire:target="logo_path" wire:loading.attr="disabled">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.brand-table params="{{$route_name}}" />
            @endif

        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin hapus data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                $('#description').summernote({
                    placeholder: 'description',
                    fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
                    tabsize: 2,
                    height: 300,
                    callbacks: {
                                onChange: function(contents, $editable) {
                                    @this.set('description', contents);
                                }
                            }
                });
                // status
                $('#status').select2({
                    theme: "bootstrap",
                });
                $('#status').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('status', data);
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

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>