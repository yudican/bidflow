@php
use App\Models\Role;
@endphp
<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>{{ ($form_active)?'Form Pengisian Data Product Margin Bottoms':'List Data Product Margin Bottoms' }}</span>
                        </a>
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
                <div class="card-body">
                    <!-- <x-select name="product_id" label="Product Id" ><option value="">Select Product Id</option></x-select> -->
                    <x-select name="role_id" label="Role" isreq="*">
                        <option value="">Select Role Id</option>
                        @foreach ($roles as $rol)
                        <option value="{{$rol->id}}">{{$rol->role_name}}</option>
                        @endforeach
                    </x-select>
                    {{-- <div class="form-group">
                        <label>Product</label>
                        <select name="product_id" wire:model="product_id" wire:change="getPrice($event.target.value)" class="form-control">
                            <option value="">Pilih Produk</option>
                            @foreach ($products as $prod)
                            <option value="{{$prod->id}}">{{$prod->name}}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="form-group">
                        <label>Product Variant</label>
                        <select name="product_variant_id" wire:model="product_variant_id" wire:change="getPriceVariant($event.target.value)" class="form-control">
                            <option value="">Pilih Produk Varian</option>
                            @foreach ($productvariants as $prod2)
                            <option value="{{$prod2->id}}">{{$prod2->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @php
                    if (!empty($role_id)) {
                    $role = Role::find($role_id);
                    $tag = $role->role_name;
                    }
                    @endphp
                    <x-text-field type="text" name="basic_price" label="Final Price ({{@$tag}})" isreq="*" />
                    <x-text-field type="text" name="margin" label="Margin" isreq="*" />
                    <x-textarea type="textarea" name="description" label="Description" isreq="*" />

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.margin-bottom-table params="{{$route_name}}" />
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



    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                
                
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>