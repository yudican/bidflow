<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>Faq Contents</span>
                        </a>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : " toggleForm('edit')"}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        @if (!$form_active)
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <x-select name="filter_submenu_id" label="Pilih Sub Menu" handleChange="selectedSubmenu">
                        <option value="all">Semua Sub Menu</option>
                        @foreach ($submenus as $sub)
                        <option value="{{$sub->id}}">{{$sub->sub_menu}}</option>
                        @endforeach
                    </x-select>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12">
            @if ($form_active == 'edit')
            <div class="card">
                <div class="card-body">
                    <x-select name="submenu_id" label="Sub Menu" isreq="*">
                        <option value="">Select Sub Menu</option>
                        @foreach ($submenus as $sub)
                        <option value="{{$sub->id}}">{{$sub->sub_menu}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="category_id" label="Category " isreq="*">
                        <option value="">Select Category </option>
                        @foreach ($categories as $cat)
                        <option value="{{$cat->id}}">{{$cat->category}}</option>
                        @endforeach
                    </x-select>
                    <x-text-field type="text" name="title" label="Title" isreq="*" />
                    <div wire:ignore class="form-group @error('content')has-error has-feedback @enderror">
                        <label for="content" class="text-capitalize">Content</label>
                        <textarea wire:model="content" id="content" class="form-control"></textarea>
                        @error('content')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <x-text-field type="text" name="video" label="Video" placeholder="Ex: https://www.youtube.com/embed/tgbNymZ7vqY" />
                    <x-select name="status" label="Status" ignore>
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Not Active</option>
                    </x-select>

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @elseif($form_active == 'detail')
            <div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{$title}}</h4>
                    </div>
                    <div class="card-body">
                        <p>{!!$content!!}</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Likes Artikel <span class="pull-right">Total {{count($details)}}</span></h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-lightss">
                            <thead class="thead-lightss">
                                <tr>
                                    <td>No.</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $key => $item)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->email }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.faq-content-table params="{{$route_name}}" />
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
                        @if ($faq_content_id)
                        <button type="submit" wire:click='delete' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Hapus</button>
                        @endif

                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>


    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                $('#content').summernote({
            placeholder: 'content',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
            tabsize: 2,
            height: 300,
            callbacks: {
                        onChange: function(contents, $editable) {
                            @this.set('content', contents);
                        }
                    }
            });
                
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>