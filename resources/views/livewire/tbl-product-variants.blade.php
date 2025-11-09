<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                @if (!$stock_list)
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>{{ ($form_active)?'Form Pengisian Data Variant Product':'List Data Variant Product' }}</span>
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
                @endif
            </div>
        </div>
        <div class="col-md-12">
            @if ($form_active)
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="product_id" label="Pilih Produk" ignore>
                                <option value="">Pilih Produk</option>
                                @foreach ($products as $prod)
                                <option value="{{$prod->id}}">{{$prod->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="name" label="Nama" placeholder="Masukkan nama variant" isreq="*" />

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="package_id" label="Package" ignore>
                                <option value="">Select Package</option>
                                @foreach ($packages as $pack)
                                <option value="{{$pack->id}}">{{$pack->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-select name="variant_id" label="Variant" ignore>
                                <option value="">Select Variant</option>
                                @foreach ($variants as $vari)
                                <option value="{{$vari->id}}">{{$vari->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <x-select name="sku" label="SKU Master" ignore>
                                <option value="">Select SKU Master</option>
                                @foreach ($skumasters as $sku)
                                <option value="{{$sku->sku}}">{{$sku->sku}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="sku_variant" label="SKU Variant" placeholder="Masukkan nama sku" isreq="*" />
                        </div>
                        <div class="col-md-6">
                            <x-text-field type="text" name="qty_bundling" label="Bundling Qty" placeholder="Masukkan Bundling Qty" isreq="*" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <x-input-photo foto="{{$image}}" path="{{optional($image_path)->temporaryUrl()}}" name="image_path" label="Cover Image" />
                        </div>
                        <div class="col-md-12">
                            {{-- costum input file --}}
                            <div class="form-group">
                                <label><strong>Product Images</strong></label>
                                <div class="custom-file">
                                    <input type="file" name="files[]" multiple class="custom-file-input form-control" id="customFile" wire:model="images" accept="image/*">
                                    <label class="custom-file-label" for="customFile">Choose image (multiple)</label>
                                </div>
                                @error('image.*') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="d-flex flex-row">
                                @if (!empty($images))
                                @foreach ($images as $key => $item)
                                <div class="form-group mr-2">
                                    <img id="image-preview" class=" btn btn-light ratio-img img-fluid p-2 border image rounded border-dashed" width="150" style="width: 150px;height:150px;object-fit:contain;" src="{{optional($item)->temporaryUrl()}}" alt="your image" />
                                    <br>
                                    <button class="btn btn-danger btn-sm w-100" wire:click="unSelect({{$key}})"><i class="fas fa-times"></i> Hapus</button>
                                </div>
                                @endforeach
                                @endif
                                @if (!empty($image_lists))
                                @foreach ($image_lists as $item_list)
                                <div class="form-group mr-2">
                                    <img id="image-preview" class=" btn btn-light ratio-img img-fluid p-2 border image rounded border-dashed" width="150" style="width: 150px;height:150px;object-fit:contain;" src="{{getImage($item_list->name)}}" alt="{{$item_list->name}}" />
                                    <br>
                                    <button class="btn btn-danger btn-sm w-100" wire:click="deleteImage({{$item_list->id}})"><i class="fas fa-times"></i> Hapus</button>
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div wire:ignore class="form-group @error('description')has-error has-feedback @enderror">
                                <label for="description" class="text-capitalize">Description</label>
                                <textarea wire:model="description" id="description" class="form-control"></textarea>
                                @error('description')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <x-text-field type="text" name="stock" label="Stock" placeholder="Masukkan stok barang" readonly />
                        </div>
                        <div class="col-md-4">
                            <x-text-field type="text" name="weight" label="Berat Produk" placeholder="Masukkan berat barang" isreq="*" />
                        </div>
                        <div class="col-md-4">
                            <x-select name="status" label="Status" ignore>
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">Not Active</option>
                            </x-select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Detail Price</label>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Level</th>
                                        <th>Basic Price</th>
                                        <th>Final Price</th>
                                    </tr>
                                    @foreach ($levels as $key => $lev)
                                    <tr>
                                        <td>
                                            {{ $lev->name }}
                                        </td>
                                        <td>
                                            <x-text-field type="text" name="basic_price.{{$lev->id}}" placeholder="Basic Price" />
                                        </td>
                                        <td>
                                            <x-text-field type="text" name="final_price.{{$lev->id}}" placeholder="Final Price" />
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @elseif ($stock_list)
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Product Stock
                                <div class="pull-right">
                                    <button class="btn btn-primary btn-sm" wire:click="showModalStock('{{$product_id}}')"><i class="fas fa-plus"></i> Tambah Stock</button>
                                </div>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="dtcomment" class="display table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Warehouse</th>
                                            <th>Penambahan Stock</th>
                                            <th>Created On</th>
                                            <th>Modified On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($stocklist) > 0)
                                        @foreach ($stocklist as $key => $row)
                                        <tr>
                                            <td>{{$key+1}}</td>
                                            <td>{{$row->warehouse->name}}</td>
                                            <td>{{$row->stock}}</td>
                                            <td>{{$row->created_at}}</td>
                                            <td>{{$row->updated_at}}</td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <!-- Tidak ada data -->
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.product-variant-table params="{{$route_name}}" />
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

        <div id="form-modal-stock" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} product stock</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="tbl_products_id" id="tbl_products_id" />
                        <x-select name="warehouse_id" label="Warehouse" required>
                            <option value="">Select Warehouse</option>
                            @foreach ($warehouses as $war)
                            <option value="{{$war->id}}">{{$war->name}}</option>
                            @endforeach
                        </x-select>
                        <x-select name="product_variant_id" label="Product Variant" required>
                            <option value="">Select Variant</option>
                            @foreach ($product_variants as $pro)
                            <option value="{{$pro->id}}">{{$pro->name}}</option>
                            @endforeach
                        </x-select>
                        <x-text-field type="text" label="Stock" name="stock2" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click={{$update_mode ? 'update_stock' : 'store_stock' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
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
            $('input[type="file"]').on("change", function() {
                    let filenames = [];
                    let files = document.getElementById("customFile").files;
                    if (files.length > 1) {
                    filenames.push("Total Files (" + files.length + ")");
                    } else {
                    for (let i in files) {
                        if (files.hasOwnProperty(i)) {
                        filenames.push(files[i].name);
                        }
                    }
                    }
                    $(this)
                    .next(".custom-file-label")
                    .html(filenames.join(","));
                });
                
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

                // product_id
                $('#product_id').select2({
                    theme: "bootstrap",
                });
                $('#product_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('product_id', data);
                });

                // package_id
                $('#package_id').select2({
                    theme: "bootstrap",
                });
                $('#package_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('package_id', data);
                });

                // variant_id
                $('#variant_id').select2({
                    theme: "bootstrap",
                });
                $('#variant_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('variant_id', data);
                });
            });

            window.livewire.on('showModalStock', (data) => {
                $('#form-modal-stock').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-modal-stock').modal('hide')
            });
        })
    </script>
    @endpush
</div>