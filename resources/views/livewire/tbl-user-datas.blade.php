<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Customer':'List Data Customers' }}</span>
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
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="name" label="Full Name" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="username" label="Username" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="email" label="Email" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="password" name="password" label="Password" />
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="level_id" label="Level" isreq="*">
                                <option value="">Select Level</option>
                                @foreach ($levels as $lev)
                                <option value="{{$lev->id}}">{{$lev->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="class_id" label="Class Id" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="customer_id" label="Customer Id" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="short_name" label="Short Name" isreq="*" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-textarea type="textarea" name="address" label="Address" isreq="*" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="contact_person" label="Contact Person" isreq="*" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="city" label="City" isreq="*" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="state" label="State" isreq="*" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="country" label="Country" isreq="*" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="zip_code" label="Zip Code" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="phone1" label="Phone1" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="phone2" label="Phone2" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="phone3" label="Phone3" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="fax" label="Fax" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-text-field type="text" name="npwp" label="Npwp" isreq="*" />
                        </div>
                        <div class="col-md-6">
                            <x-select name="status" label="Status" isreq="*">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Not Active</option>
                            </x-select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <x-select name="brand_id" id="choices-multiple-remove-button" label="Brand" multiple ignore>
                                @foreach ($brands as $brand)
                                <option value="{{$brand->id}}">{{$brand->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.user-data-table params="{{$route_name}}" />
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
            window.livewire.on('loadForm', (data) => {
                $('#choices-multiple-remove-button').select2({
                    theme: "bootstrap",
                });
                $('#choices-multiple-remove-button').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data)
                    @this.set('brand_id', data);
                });
                
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>