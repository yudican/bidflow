<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Warehouses':'List Data Warehouses' }}</span>
                        <div class="pull-right">
                            @if (!$detail)
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($detail)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Warehouse Detail
                    </h4>
                </div>

                {{-- card detail --}}
                <div class="card-body">
                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">

                            <div class="">
                                <div class="row">
                                    <div class="col-md-6 col-lg-6">
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Warehouse Name</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ @$warehouse->name }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Location</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ @$warehouse->location ? $warehouse->location : '-' }}</span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label class="col-lg-4 fw-bold text-muted">Address</label>
                                            <div class="col-lg-8">
                                                <span class="fw-bolder fs-6 text-gray-800">{{ @$warehouse->address ? $warehouse->address : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                            </div>
                        </div>
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                            <div>
                                <hr>
                                <br>
                                <b>Produk Terjual</b>
                                <br>
                                <div class="table-responsive">
                                    <table id="dtcomment" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Kode Produk</th>
                                                <th>Nama Produk</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i=1; @endphp
                                            @foreach ($product_sold as $key => $row)
                                            <tr>
                                                <td>{{$i}}</td>
                                                <td>{{$row->code}}</td>
                                                <td>{{$row->name}}</td>
                                                <td>{{$row->jumlah}}</td>
                                            </tr>
                                            @php $i++; @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @elseif ($form_active)
            <div class="card">
                <div class="card-body">
                    <x-text-field type="text" name="name" label="Name" isreq="*" />
                    <x-text-field type="text" name="telepon" label="Telepon" isreq="*" />
                    <!-- <x-text-field type="text" name="slug" label="Slug" /> -->
                    <x-text-field type="text" name="location" label="Location" isreq="*" />
                    <x-textarea type="textarea" name="address" label="Address" isreq="*" />
                    <x-select name="provinsi_id" label="Provinsi" ignore>
                        <option value="">Select Provinsi</option>
                        @foreach ($provinces as $provinsi)
                        <option value="{{$provinsi->pid}}">{{$provinsi->nama}}</option>
                        @endforeach
                    </x-select>

                    <x-select name="kabupaten_id" label="Kota/Kabupaten" ignore>
                        <option value="">Select Kota/Kabupaten</option>
                        @foreach ($kabupatens as $kab)
                        <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
                        @endforeach
                    </x-select>

                    <x-select name="kecamatan_id" label="Kecamatan" ignore>
                        <option value="">Select Kecamatan</option>
                        @foreach ($kecamatans as $kecamatan)
                        <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
                        @endforeach
                    </x-select>


                    <x-select name="kelurahan_id" label="Kelurahan" ignore>
                        <option value="">Select Kelurahan</option>
                        @foreach ($kelurahans as $kelurahan)
                        <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
                        @endforeach
                    </x-select>
                    <x-text-field type="text" name="kodepos" label="Kodepos" isreq="*" />
                    <x-select name="status" label="Status" ignore>
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Not Active</option>
                    </x-select>
                    <br>
                    <hr><br>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Users</label>
                                <table class="table table-lightss">
                                    <thead class="thead-lightss">
                                        <tr>
                                            <th width="80%" class="p-0">User <span style="color:red">*</span></th>
                                            <th width="20%" class="p-0"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for ($index = 0; $index < count($inputs); $index++) <tr>
                                            <td class="p-0">
                                                <x-select name="r_contact.{{$index}}" id="contact_{{$index}}">
                                                    <option value="">Select Contact</option>
                                                    {{-- @foreach ($contact_list as $con)
                                                    <option value="{{$con->id}}">{{$con->name}} - {{ $con->role_type }}</option>
                                                    @endforeach --}}
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
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.warehouse-table params="{{$route_name}}" />
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
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function(value) {
            
            
           
            
            

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
                    data,
                });
            });
            window.livewire.on('loadKelurahan', (data) => {
                $('#kelurahan_id').select2({
                    theme: "bootstrap",
                    width:'auto',
                    data
                });
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });

            window.livewire.on('contactAdd', (data) => {
                for (let index = 0; index < data.length; index++) {
                    $('#contact_'+index).select2({
                        theme: "bootstrap",
                        ajax: {
                            url: "/api/ajax/search/user",
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    query: params.term, // search term
                                    page: params.page,
                                    type: 'user'
                                };
                            },
                            processResults: function(data, params) {
                                console.log(data,params)
                                params.page = params.page || 1;
                                return {
                                    results: data,
                                    pagination: {
                                        more: (params.page * 30) < data.total
                                    }
                                };
                            },
                            cache: true
                        },
                    });
                }
            });

            window.livewire.on('loadForm', (data) => {
                $('#contact_0').select2({
                    theme: "bootstrap",
                    ajax: {
                        url: "/api/ajax/search/user",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                query: params.term, // search term
                                page: params.page,
                                type: 'user'
                            };
                        },
                        processResults: function(data, params) {
                            console.log(data,params)
                            params.page = params.page || 1;
                            return {
                                results: data,
                                pagination: {
                                    more: (params.page * 30) < data.total
                                }
                            };
                        },
                        cache: true
                    },
                });
                // status
                $('#status').select2({
                    theme: "bootstrap",
                });
                $('#status').select2({
                    theme: "bootstrap",
                });
                $('#status').on('change', function (e) {
                    let data = $(this).val();
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
        })
    </script>
    @endpush
</div>