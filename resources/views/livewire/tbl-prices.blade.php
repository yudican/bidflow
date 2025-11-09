<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Price':'List Data Price' }}</span>
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
                    <x-select name="level_id" label="Level" ignore>
                        <option value="">Select Level</option>
                    </x-select>
                    <x-select name="product_id" label="Product" ignore>
                        <option value="">Select Product</option>
                    </x-select>
                    <x-select name="product_variant_id" label="Product Variant" ignore>
                        <option value="">Select Product Variant</option>
                    </x-select>
                    <x-text-field type="text" name="basic_price" label="Basic Price" isreq="*" />
                    <x-text-field type="text" name="final_price" label="Final Price" isreq="*" />

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.price-table params="{{$route_name}}" />
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
                // level_id
                $('#level_id').select2({
                    theme: "bootstrap",
                });
                $('#level_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('level_id', data);
                });

                // product_id
                $('#product_id').select2({
                    theme: "bootstrap",
                });
                $('#product_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('product_id', data);
                });

                // product_variant_id
                $('#product_variant_id').select2({
                    theme: "bootstrap",
                });
                $('#product_variant_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('product_variant_id', data);
                });
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>