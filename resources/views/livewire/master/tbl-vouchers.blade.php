<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Voucher':'List Data Voucher' }}</span>
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
                        {{-- form form-group text input --}}
                        <div class="form-group {{$errors->has('voucher_code') ? 'has-error has-feedback' : '' }}">
                            <label for="voucher_code" class="placeholder">
                                <div class="flex flex-row items-center">
                                    <b>Kode Voucer <span style="color:red">*</span></b>
                                    <div class="flex flex-row ml-3">
                                        <p wire:click="generateVoucher" class="placeholder text-primary cursor-pointer">Generate Random</p>
                                        @if ($random)
                                        <p wire:click="resetVoucher" class="ml-3 text-danger cursor-pointer">Reset</p>
                                        @endif
                                    </div>
                                </div>
                            </label>

                            <input id="voucher_code" name="voucher_code" wire:model="voucher_code" type="text" class="form-control" placeholder="">
                            <small id="helpId" class="text-danger">{{ $errors->has('voucher_code') ? $errors->first('voucher_code') : '' }}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <x-text-field type="text" name="title" label="Title Voucer" placeholder="Masukkan title voucer" isreq="*" />
                    </div>
                    <div class="col-md-2">
                        <x-text-field type="number" name="total" label="Total Voucher (Qty)" placeholder="Masukkan jumlah voucer (qty)" isreq="*" />
                    </div>
                    <div class="col-md-4">
                        <x-text-field type="number" name="min" label="Minimum Transaction (Rp)" placeholder="Masukkan minimum transaksi (Rp)" isreq="*" />
                    </div>
                    <div class="col-md-2">
                        <x-text-field type="number" name="percentage" label="Percentage (1-100)" placeholder="Masukkan jumlah persetansi yang akan diberikan" isreq="*" />
                    </div>
                    <div class="col-md-4">
                        <x-text-field type="number" name="nominal" label="Max Nominal (Rp)" placeholder="Masukkan nominal yang akan diberikan" isreq="*" />
                    </div>

                    <div class="col-md-6">
                        <x-text-field type="text" name="usage_for" label="Usage For" placeholder="Usage For" />
                        <!-- <x-select name="brand_id" label="Brand" multiple ignore>
                            <option value="">Select Brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </x-select> -->
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-sm-6">
                                <x-text-field type="date" name="start_date" label="Start Date" isreq="*" />
                            </div>
                            <div class="col-sm-6">
                                <x-text-field type="date" name="end_date" label="End Date" isreq="*" />
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <!-- <x-text-field type="text" name="usage_for" label="Usage For" placeholder="Usage For" /> -->
                        <x-select name="brand_id" label="Brand" multiple ignore>
                            <option value="">Select Brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </x-select>
                    </div>

                    <div class="col-md-12">
                        <div wire:ignore class="form-group @error('description')has-error has-feedback @enderror">
                            <label for="description" class="text-capitalize">Description</label>
                            <textarea wire:model="description" id="description" class="form-control"></textarea>
                            @error('description')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <x-select name="type" label="Type" class="type" ignore>
                            <option value="">Select Type</option>
                            <option value="general">Voucher General</option>
                            <option value="point">Voucher Point</option>
                        </x-select>
                    </div>

                    @if ($type == 'point')
                    <div class="col-md-12">
                        <x-text-field type="text" name="total_point" label="Total Point" />
                    </div>
                    @endif

                    <div class="col-md-12">
                        <x-select name="status" label="Status" ignore>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Non Active</option>
                        </x-select>
                    </div>
                    <div class="col-md-12">
                        <x-input-photo foto="{{$image}}" path="{{optional($image_path)->temporaryUrl()}}" name="image_path" label="Image" />
                        <div class="form-group">
                            <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                        </div>
                    </div>

                </div>
            </div>
            @else
            <livewire:table.voucher-table params="{{$route_name}}" />
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
                    @this.set('status', data);
                });

                // brand_id
                $('#brand_id').select2({
                    theme: "bootstrap",
                });
                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('brand_id', data);
                });

                // type
                $('#type').select2({
                    theme: "bootstrap",
                });
                $('#type').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('type', data);
                });

                // brand_id
                $('#brand_id').select2({
                    theme: "bootstrap",
                });
                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
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