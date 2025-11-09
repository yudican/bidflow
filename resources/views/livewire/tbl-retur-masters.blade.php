<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>retur masters</span>
                        </a>
                        <div class="pull-right">
                        @if ($form_active)
                        <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                        @else
                        @if (in_array(auth()->user()->role->role_type, ['cs', 'leadcs', 'superadmin', 'adminsales', 'leadwh']))
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
                    <div class="card-body">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="color: #13854E;font-weight: bold;">Informasi Pribadi
                                    <div class="pull-right">{{date('D, Y-m-d')}}</div>
                                </h4>
                            </div>
                            <div class="card-body">
                                @if ($update_mode)
                                <div class="row">
                                    <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                        <div class="pull-right">Status : {{getStatusRetur($status)}}</div>
                                    </div>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                        <x-text-field type="text" name="name" label="Name" isreq="*"/>
                                        <x-text-field type="text" name="handphone" label="Handphone" isreq="*" />
                                    </div>
                                    <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                        <x-text-field type="email" name="email" label="Email" isreq="*"/>
                                        <x-text-field type="text" name="phone" label="Phone (Optional)" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                        <x-textarea type="text" name="address" label="Address" isreq="*"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="color: #13854E;font-weight: bold;">Pengajuan Komplain</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                        <x-text-field type="text" name="type_case" label="Type Case" readonly/>
                                        <!-- <x-select name="type_case" label="Type Case" isreq="*">
                                            <option value="">Select Type</option>
                                                @foreach ($type_list as $tp)
                                                <option value="{{$tp->id}}">{{$tp->type_name}}</option>
                                                @endforeach
                                        </x-select> -->
                                        <x-textarea type="textarea" name="alasan" label="Alasan" isreq="*"/>
                                        <x-select name="transaction_from" label="Transaction From" isreq="*" >
                                            <option value="">Select Source</option>
                                            @foreach ($source_list as $sou)
                                            <option value="{{$sou->id}}">{{$sou->source_name}}</option>
                                            @endforeach
                                        </x-select>
                                        <x-text-field type="text" name="transaction_id" label="Transaction Id" />
                                        <x-input-photo foto="{{$transfer_photo}}" path="{{optional($transfer_photo_path)->temporaryUrl()}}"
                                                name="transfer_photo_path"  label="Transfer Photo " isreq="*"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="color: #13854E;font-weight: bold;">Item yang dikembalikan</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <table width="100%">
                                        <thead class="thead-lightss">
                                            <tr>
                                                <th style="padding-left: 10px;" width="50%">Product <span style="color:red">*</span></th>
                                                <th style="padding-left: 10px;" width="40%">Lampiran <span style="color:red">*</span></th>
                                                <th class="p-0" width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($index = 0; $index < count($inputs); $index++) 
                                            <tr>
                                                <td class="p-0">
                                                    <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                        <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" class="form-control">
                                                            <option value="">Pilih Produk</option>
                                                            @foreach ($products as $prod)
                                                            <option value="{{$prod->id}}">{{$prod->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <small id="helpId" class="text-danger">{{ $errors->has('product_id.'.$index) ? $errors->first('product_id.'.$index) : '' }}</small>
                                                    </div>
                                                </td>
                                                <td class="p-0">
                                                <x-input-file file="{{$product_photo[$index]}}" path="{{optional($product_photo_path[$index])->getClientOriginalName()}}" name="product_photo_path.{{$index}}" />
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
                        @if ($update_mode)
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="color: #13854E;font-weight: bold;">Resi Pengembalian Barang
                                    <div class="pull-right">
                                        <button class="btn btn-primary btn-sm" wire:click="showModalResi"><i class="fas fa-plus"></i> Tambah Resi</button>
                                    </div>  
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                        <table class="display table table-striped table-hover" id="resi-pengembalian">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Ekspedisi</th>
                                                    <th>No. Resi</th>
                                                    <th>Nama Pengirim</th>
                                                    <th>No. Hp Pengirim</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (count($returresi) > 0)
                                                    @foreach ($returresi as $key => $res)
                                                        <tr id="key-{{$res->id}}" key="{{$res->id}}">
                                                            <td>{{ $key + 1}}</td>
                                                            <td>{{ $res->expedition_name }}</td>
                                                            <td>{{ $res->resi}}</td>
                                                            <td>{{ $res->sender_name }}</td>
                                                            <td>{{ $res->sender_phone }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else 
                                                    <tr>
                                                        <td class="p-0" colspan="5">
                                                            <div style="height: 200px;">
                                                                <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                                                    <div class="flex flex-col justify-center items-center mt-8">
                                                                        <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                                        <span>Tidak Ada Data</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if (!$update_mode && in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'cs']))
                        <div class="form-group">
                            <input class="form-check-input" type="checkbox" value="0" checked>
                            Saya setuju untuk menanggung ongkos kirim jika harus mengembalikan barang.
                            <button class="btn btn-primary pull-right"
                                wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                        </div>
                        @elseif ($update_mode && $status == 0 && in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'warehouse', 'admindelivery']))
                        <div class="form-group">
                            <button class="btn btn-danger pull-right"
                                wire:click="approval('{{$uid_retur}}',2)">Reject</button> 
                            <button class="btn btn-primary pull-right" style="margin-right: 10px"
                                wire:click="approval('{{$uid_retur}}',1)">Terima Retur Barang</button>
                        </div> 
                        @elseif ($update_mode && $status == 1 && in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'warehouse', 'admindelivery']))
                        <div class="form-group">
                            <button class="btn btn-danger pull-right"
                                wire:click="approval('{{$uid_retur}}',3)">Konfirmasi Barang Diterima</button>
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <livewire:table.retur-master-table params="retur-master" />
            @endif

        </div>

        {{-- Modal Resi --}}
        <div id="resi-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} Resi</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="resi_id" id="resi_id" />
                        <input type="hidden" wire:model="uid_retur" id="uid_retur" />
                        <x-text-field type="text" name="expedition_name" label="Nama Ekspedisi" isreq="*" />
                        <x-text-field type="text" name="resi" label="Resi" isreq="*" />
                        <x-text-field type="text" name="sender_name" label="Sender Name" isreq="*" />
                        <x-text-field type="text" name="sender_phone" label="Sender Phone" isreq="*" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update_resi' : 'store_resi' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog"
            aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Hapus</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin hapus data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i
                                class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i
                                class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    
    

    <script>
         $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                
                
            });

            window.livewire.on('showModalResi', (data) => {
                $('#uid_retur').val({{$uid_retur}});
                $('#resi_id').val({{$resi_id}});
                $('#resi-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#resi-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>