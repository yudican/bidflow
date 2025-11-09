<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>Data Users</span>
                        <div class="pull-right">
                            @if (!$form && !$modal)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i>
                                Tambah Data</button>
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <livewire:table.user-table params="{{$route_name}}" />
        </div>

        {{-- Modal form --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">
                            {{$update_mode ? 'Update' : 'Tambah'}} users</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body w-100">
                        <x-text-field type="text" name="name" label="name" />
                        <x-text-field type="text" name="email" label="email" />
                        <x-text-field type="password" name="password" label="password" />
                        <x-select name="role_id" label="Pilih Role" ignore>
                            <option value="">Pilih Role</option>
                            @foreach ($roles as $role)
                            <option value="{{$role->id}}">{{$role->role_name}}</option>
                            @endforeach
                        </x-select>
                        <x-select name="brand_id" label="Pilih Brand" multiple ignore>
                            <option value="">Pilih Brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </x-select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update' : 'store' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                        <!-- <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a> -->
                    </div>
                </div>
            </div>
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
            window.livewire.on('showModal', (data) => {
                // brand_id
                 $('#brand_id').select2({
                    theme: "bootstrap",
                    width:'auto'
                });
                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('brand_id', data);
                });

                $('#role_id').select2({
                    theme: "bootstrap",
                    width:'auto'
                });
                $('#role_id').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('role_id', data);
                });
                $('#form-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>