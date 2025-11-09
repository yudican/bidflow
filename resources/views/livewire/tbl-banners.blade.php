<div class="page-inner" wire:init="init">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Banner':'List Data Banner' }}</span>
                        <div class="pull-right">
                            @if ($form_active)
                            {{-- <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button> --}}
                            <x-button onClick="toggleForm(false)" label="Batal" icon="fas fa-times" variant="danger" />
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            {{-- <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button> --}}
                            <x-button onClick="toggleForm(true)" label="Tambah Data" icon="fas fa-plus" />
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
                    <x-text-field type="text" name="title" label="Judul Banner" placeholder="Masukkan judul banner" isreq="*" />
                    <x-text-field type="text" name="url" label="Url Banner" placeholder="Masukkan url banner" />
                    <x-input-photo foto="{{$image}}" path="{{optional($image_path)->temporaryUrl()}}" name="image_path" label="Image" />
                    <x-text-field type="text" name="slug" label="Slug (otomatis)" readonly />
                    <div>
                        <div wire:ignore class="form-group @error('description')has-error has-feedback @enderror">
                            <label for="description" class="text-capitalize">Description</label>
                            <textarea wire:model="description" id="description" class="form-control"></textarea>
                        </div>
                        @error('description')
                        <small class="text-danger ml-2">{{ $message }}</small>
                        @enderror
                    </div>
                    <x-select name="brand_id" label="Brand" multiple ignore>
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                        <option value="{{$brand->id}}">{{$brand->name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="status" label="Status" ignore>
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Not Active</option>
                    </x-select>

                    <div class="form-group">
                        <x-button onClick="{{$update_mode ? 'update' : 'store'}}" label="Simpan" icon="fas fa-floppy-o" />
                        {{-- <button class="btn btn-primary pull-right" wire:click="" wire:target="image_path" wire:loading.attr="disabled">Simpan</button> --}}
                    </div>
                </div>
            </div>
            @else
            <div>
                @if ($loading)
                <div class="card flex justify-content-center align-items-center">
                    <img src="{{asset('assets/img/loader.gif')}}" alt="loader">
                </div>
                @else
                <div>
                    {{--
                    <livewire:table.banner-table params="{{$route_name}}" key="admin" /> --}}
                    @livewire('table.banner-table', ['params' => $route_name ?? 'banner'], key('banner'))
                </div>
                @endif
            </div>
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
                    {{-- old button bug loading  --}}
                    {{-- <div class="modal-footer">
                        <x-button onClick="delete" label="Ya, Hapus" icon="fa fa-check pr-2" variant="danger" />
                        <x-button onClick="_reset" label="Batal" icon="fa fa-times pr-2" />
                    </div> --}}

                    {{-- new button fixed bug loading --}}
                    <div class="modal-footer">
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>          
                </div>
            </div>
        </div>

        {{-- import excel --}}
        <div id="import-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Import Data</h5>
                    </div>
                    <div class="modal-body">
                        <x-input-file file="{{$file}}" path="{{optional($file_path)->getClientOriginalName()}}" name="file_path" label="Data Banner" />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='saveImport' class="btn btn-success btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
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
        document.addEventListener('livewire:load', function () {
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
                
                // brand_id
                $('#brand_id').select2({
                    theme: "bootstrap",
                });
                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('brand_id', data);
                });

                // status
                $('#status').select2({
                    theme: "bootstrap",
                });
                $('#status').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('status', data);
                });
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });

            window.livewire.on('showModalImport', (data) => {
                $('#import-modal').modal(data)
            });
        })
    </script>
    @endpush
</div>