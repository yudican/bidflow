<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>sales return masters</span>
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
                @if ($update_mode)
                <div class="card-header">
                    <h4 class="card-title" style="color: #13854E;font-weight: bold;">
                        @if (!empty($uid_retur))
                        <div class="pull-right">
                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.sr', $uid_retur) }}" target="_blank"><i class="fa fa-print"></i> Print SR</a>
                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.invoice', $uid_retur) }}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                        </div>
                        @endif
                    </h4>
                </div>
                @endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="brand_id" label="Brand" required isreq="*" ignore>
                                <option value="">Select Brand</option>
                                @foreach ($brands as $brand)
                                <option value="{{$brand->id}}">{{$brand->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="order_number" label="Order Number" isreq="*" ignore>
                                <option value="">Select Order</option>
                                @foreach ($order_list as $ord)
                                <option value="{{$ord->id}}">{{$ord->order_number}} - {{$ord->contactUser->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="contact" label="Contact" isreq="*" ignore>
                                <option value="">Select Contact</option>

                            </x-select>

                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            @if (auth()->user()->role->role_type != 'sales')
                            <x-select name="sales" label="Sales" id="sales" isreq="*" ignore>
                                <option value="">Select Sales</option>

                            </x-select>
                            @else
                            <div class="form-group">
                                <label>Select Sales <span style="color:red">*</span></label>
                                <select name="sales" id="sales" class="form-control" disabled>
                                    <option value="{{ auth()->user()->id }}">{{ auth()->user()->name }}</option>
                                </select>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="payment_terms" label="Term Of Payments" isreq="*" ignore>
                                <option value="">Pilih Payment Term</option>
                                @foreach ($paymentterms as $pay)
                                <option value="{{$pay->id}}">{{$pay->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-text-field type="text" name="due_date" label="Due Date" readonly />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="shipping_id" label="Shipping" isreq="*" ignore>
                                <option value="">Select Shipping</option>
                            </x-select>

                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-select name="warehouse_id" label="Warehouse" isreq="*" ignore>
                                <option value="">Select Warehouse</option>
                                @foreach ($warehouses as $war)
                                <option value="{{$war->id}}">{{$war->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-textarea type="textarea" name="shipping_address" label="Shipping Address" />
                        </div>
                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                            <x-textarea type="textarea" name="warehouse_address" label="Warehouse Address" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" style="line-height: 36px;">
                            <x-textarea type="textarea" name="notes" label="Notes" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Product</h4>
                </div>
                <div class="card-body">
                    <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                    <div class="row">
                        <div class="col-md-12">
                            <table width="100%">
                                <thead class="thead-lightss">
                                    <tr>
                                        <th class="p-0" width="20%">Product *</th>
                                        <th class="p-0" width="15%">Price</th>
                                        <th class="p-0" width="10%">Qty *</th>
                                        <th class="p-0" width="15%">Tax *</th>
                                        <th class="p-0" width="15%">Discount *</th>
                                        <th class="p-0" width="20%">Total Price</th>
                                        <!-- <th class="p-0" width="15%">Total Dpp + PPN</th> -->
                                        <th class="p-0" width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($index = 0; $index < count($inputs); $index++) <tr>
                                        <td class="p-0">
                                            <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control">
                                                    <option value="">Pilih Produk</option>
                                                    @foreach ($products as $prod)
                                                    <option value="{{$prod->id}}">{{$prod->name}}</option>
                                                    @endforeach
                                                </select>
                                                <small id="helpId" class="text-danger">{{ $errors->has('product_id.'.$index) ? $errors->first('product_id.'.$index) : '' }}</small>
                                            </div>
                                        </td>
                                        <td class="p-0">
                                            <x-text-field type="text" name="harga_satuan.{{$index}}" readonly class="pl-0 pr-0 w-100" />
                                        </td>
                                        <td class="p-0">
                                            <div class="d-flex  align-items-center flex-row mt-2">
                                                <div class="input-group input-spinner mr-3">

                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'min')"> <i class="fas fa-minus"></i> </button>

                                                    <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>

                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'plus')"> <i class="fas fa-plus"></i> </button>
                                                </div> <!-- input-group.// -->
                                            </div> <!-- input-group.// -->
                                        </td>
                                        <td class="p-0">
                                            <div class="form-group {{$errors->has('tax_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                <select name="tax_id.{{$index}}" wire:model="tax_id.{{$index}}" class="form-control">
                                                    <option value="">Pilih Tax</option>
                                                    @foreach ($tax as $tx)
                                                    <option value="{{$tx->id}}">{{$tx->tax_code}}</option>
                                                    @endforeach
                                                </select>
                                                <small id="helpId" class="text-danger">{{ $errors->has('tax_id.'.$index) ? $errors->first('tax_id.'.$index) : '' }}</small>
                                            </div>
                                        </td>
                                        <td class="p-0">
                                            <div class="form-group {{$errors->has('discount_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                <select name="discount_id.{{$index}}" wire:model="discount_id.{{$index}}" class="form-control">
                                                    <option value="">Pilih Discount</option>
                                                    @foreach ($discounts as $dis)
                                                    <option value="{{$dis->id}}">{{$dis->title}}</option>
                                                    @endforeach
                                                </select>
                                                <small id="helpId" class="text-danger">{{ $errors->has('discount_id.'.$index) ? $errors->first('discount_id.'.$index) : '' }}</small>
                                            </div>
                                        </td>
                                        <td class="p-0">
                                            <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                        </td>
                                        <td class="p-0">
                                            <!-- <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control"> -->
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
            </div>
            <div class="card">
                <div class="form-group">
                    <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Save Order</button>
                </div>
            </div>
            @else
            <livewire:table.sales-return-table params="{{$route_name}}" />
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
        document.addEventListener('livewire:load', function () {
            window.livewire.on('loadForm', (data) => {
                $('#brand_id').select2({
                    theme: "bootstrap",
                });

                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('brand_id', data);
                });

                $('#order_number').select2({
                    theme: "bootstrap",
                });

                $('#order_number').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('order_number', data);
                });

                $('#payment_terms').select2({
                    theme: "bootstrap",
                });

                $('#payment_terms').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('payment_terms', data);
                    @this.call('getDueDate', data);
                });


                $('#shipping_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('shipping_id', data);
                    @this.call('getShippingAddress',data)
                });

                $('#warehouse_id').select2({
                    theme: "bootstrap",
                });

                $('#warehouse_id').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('warehouse_id', data);
                    @this.call('getWarehouseAddress',data)
                });

                
                // contact 
                $('#contact').select2({
                    theme: "bootstrap",
                    placeholder: "Cari Contact",
                    ajax: {
                        url: "/api/ajax/search/lead/contact",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                query: params.term, // search term
                                page: params.page,
                                type: 'user',
                                role: true,
                                user_id:document.querySelector('[name=user_id]').content
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data,
                                pagination: {
                                    more: (params.page * 30) < data.total
                                }
                            };
                        },
                        cache: false
                    },
                });
                $('#contact').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('contact', data);
                    @this.call('getShipping',data)
                });

                // sales
                $('#sales').select2({
                    theme: "bootstrap",
                    placeholder: "Cari Sales",
                    ajax: {
                        url: "/api/ajax/search/lead/sales",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                query: params.term, // search term
                                page: params.page,
                                type: 'user',
                                user_id:document.querySelector('[name=user_id]').content
                            };
                        },
                        processResults: function(data, params) {
                            console.log(data,params)
                            params.page = params.page || 1;
                            return {
                                results: data,
                                pagination: {
                                    more: (params.page * 30) < data.total
                                }
                            };
                        },
                        cache: false
                    },
                });

                $('#sales').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('sales', data);
                });
            });

            window.livewire.on('loadShipping', (data) => {
                $('#shipping_id').select2({
                    theme: "bootstrap",
                    width:'auto',
                    data
                });
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>