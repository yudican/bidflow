<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>Data Menus</span>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i>
                                Tambah Data</button>
                            <button class="btn btn-primary btn-sm ml-2" wire:click="export"><i class="fas fa-excel"></i>
                                Export</button>
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    @if ($form_active)
                    <div>
                        <x-select name="parent_id" label="Menu Parent">
                            <option value="">Select Menu Parent</option>
                            @foreach ($items as $item)
                            <option value="{{$item->id}}">{{$item->menu_label}}</option>
                            @endforeach
                        </x-select>
                        <x-text-field type="text" name="menu_label" label="Menu Name" />
                        <x-text-field type="text" name="menu_route" label="Route Name" />
                        <x-text-field type="text" name="menu_icon" label="Icon Name" />
                        <x-text-field type="text" name="badge" label="Badge Variable" placeholder="[total]" />
                        <x-select name="role_id" id="choices-multiple-remove-button" label="Role" multiple ignore>
                            @foreach ($roles as $role)
                            <option value="{{$role->id}}">{{$role->role_name}}</option>
                            @endforeach
                        </x-select>
                        <x-select name="show_menu" label="Show Menu">
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </x-select>
                        <div class="form-group">
                            <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Save</button>
                        </div>
                    </div>
                    @else
                    <div class="d-flex flex-row mb-3">
                        <div class="mr-3">
                            <span class="badge badge-warning"> </span>
                            <span>Singgle Menu</span>
                        </div>
                        <div class="mr-3">
                            <span class="badge badge-success"> </span>
                            <span>Parent Sub Menu</span>
                        </div>
                        <div class="mr-3">
                            <span class="badge badge-primary"> </span>
                            <span>Sub Menu</span>
                        </div>
                    </div>
                    <div class="dd">
                        <ol class="dd-list w-100">
                            @foreach ($items as $item)
                            @if ($item->children && $item->children->count() > 0)
                            <li class="dd-item " data-id="{{$item->id}}">
                                <div style="position: absolute;z-index:1; right:10px;top:10px;">
                                    <button class="btn btn-success btn-sm mr-2" wire:click="getDataById('{{ $item->id }}')" id="btn-edit-{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-modal" wire:click="getId('{{ $item->id }}')" id="btn-delete-{{ $item->id }}"><i class="fas fa-trash"></i></button>
                                </div>
                                <div class="dd-handle d-flex justify-between align-items-center p-4">
                                    <div>
                                        <span>{{$item->menu_label}}</span>
                                        @foreach ($item->roles as $role)
                                        <span class="badge badge-success">{{$role->role_name}}</span>
                                        @endforeach

                                    </div>

                                </div>
                                <ol class="dd-list">
                                    @foreach ($item->children()->orderBy('menu_order', 'ASC')->get() as $children)
                                    <li class="dd-item" data-id="{{$children->id}}">
                                        <div style="position: absolute;z-index:1; right:10px;top:10px;">
                                            <button class="btn btn-success btn-sm mr-2" wire:click="getDataById('{{ $children->id }}')" id="btn-edit-{{ $children->id }}"><i class="fas fa-edit"></i></button>
                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-modal" wire:click="getId('{{ $children->id }}')" id="btn-delete-{{ $children->id }}"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <div class="dd-handle d-flex justify-between align-items-center p-4">
                                            <div>
                                                <span>{{$children->menu_label}}</span>
                                                @foreach ($children->roles as $role)
                                                <span class="badge badge-primary">{{$role->role_name}}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </li>
                                    @endforeach
                                </ol>
                            </li>
                            @else
                            <li class="dd-item" data-id="{{$item->id}}" style="min-height: 0;border-radius:5px;">
                                <div style="position: absolute;z-index:1; right:10px;top:10px;">
                                    <button class="btn btn-success btn-sm mr-2" wire:click="getDataById('{{ $item->id }}')" id="btn-edit-{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-modal" wire:click="getId('{{ $item->id }}')" id="btn-delete-{{ $item->id }}"><i class="fas fa-trash"></i></button>
                                </div>
                                <div class="dd-handle d-flex justify-between align-items-center p-4">
                                    <div>
                                        <span>{{$item->menu_label}}</span>
                                        @foreach ($item->roles as $role)
                                        <span class="badge badge-warning">{{$role->role_name}}</span>
                                        @endforeach

                                    </div>
                                </div>

                            </li>
                            @endif

                            @endforeach
                        </ol>
                    </div>
                    @endif
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
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.css">
    <style>
        .dd {
            position: relative;
            display: block;
            margin: 0;
            padding: 0;
            max-width: 100%;
            list-style: none;
            font-size: 13px;
            line-height: 20px;
        }

        .dd-handle {
            display: block;
            margin: 5px 0;
            padding: 5px 10px;
            color: #333;
            text-decoration: none;
            font-weight: 700;
            background: #fff;
            border-radius: 3px;
            box-sizing: border-box;
            border: 1px solid #ccc;
        }
    </style>
    @endpush
    @push('scripts')
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nestable2/1.6.0/jquery.nestable.min.js"></script>

    <script>
        $(document).ready(function(value) {
        $('.dd').nestable({
            maxDepth:2,
            callback: function(l,e){
                // l is the main container
                // e is the element that was moved
                let menu = $('.dd').nestable('serialize')
                @this.call('changeMenu', getMenu(menu));
            }
        });

        

        

        window.livewire.on('loadForm', (data) => {
            $('#choices-multiple-remove-button').select2({
                theme: "bootstrap",
            });
            $('#choices-multiple-remove-button').on('change', function (e) {
                let data = $(this).val();
                console.log(data)
                @this.set('role_id', data);
            });
        });
        window.livewire.on('closeModal', (data) => {
            $('#confirm-modal').modal('hide')
        });
    })
    const getMenu = (menu) => {
        let final_menu = [];
        //initial variable
        let i = 1;
        //process each element
        $.each(menu, function(index, value){
            //local variable
            let item = {};
            //type of validation
            if(typeof(value.children) !== 'undefined'){
                let j = 1;
                item['id'] = value.id;
                item['order'] = i;
                item['children'] = [];
                //process each children
                $.each(value.children, function(index1, value1){
                    let child = {};
                    child['id'] = value1.id;
                    child['order'] = j;
                    item['children'].push(child);
                    j++;
                });
            }
            else{
                item['id'] = value.id;
                item['order'] = i;
                item['children'] = null;
            }
            //create the final menu
            final_menu.push(item);
            i++;
        });
        return final_menu
    }
    </script>
    @endpush
</div>