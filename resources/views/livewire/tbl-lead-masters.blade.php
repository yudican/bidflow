<div class="page-inner" wire:init="init">
    <x-loading />
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>Data Lead Management</span>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create') && (!$detail))
                            <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button>
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        @if (!$form_active && !$detail)
        <div class="col-md-12">
            @if (in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']))
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <x-select name="filter_contact" label="Cari Contact" placeholder="Cari Contact" ignore>
                                <option value="all">Semua Contact</option>
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select name="filter_sales" label="Cari Sales" placeholder="Cari Sales" ignore>
                                <option value="all">Semua Sales</option>
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select name="filter_status" label="Pilih Status" handleChange="selectedStatus">
                                <option value="all">Semua Status</option>
                                <option value="0">Created</option>
                                <option value="1">Qualified</option>
                                <option value="2">Waiting Approval</option>
                                <option value="3">Unqualified</option>
                                <option value="6">Lead Rejected</option>
                            </x-select>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <div class="col-md-12">
            @if ($detail)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title" style="font-weight: bold;">Lead Management
                        <div class="pull-right" style="font-size:14px;">Status :
                            {!!leadStatusLabel($lead)!!}
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills nav-justified nav-secondary" id="pills-tab" role="tablist">
                        <li class="nav-item submenu">
                            <a class="nav-link @if ($active_tab == 1) active show @endif truncate" id="pills-info-tab" href="#" wire:click="_moveTab(1)">Stage 1 : Lead Info & Activity</a>
                        </li>
                        <li class="nav-item submenu">
                            <a class="nav-link @if ($active_tab == 2) active show @endif truncate" id="pills-transactive-tab" href="#" role="tab" wire:click="_moveTab(2)">Stage 2 : Product Needs</a>
                        </li>
                        <li class="nav-item submenu">
                            <a class="nav-link @if ($active_tab == 3) active show @endif truncate" id="pills-transhistory-tab" href="#" role="tab" wire:click="_moveTab(3)">Stage 3 : Summary Lead</a>
                        </li>
                    </ul>
                    {{-- dany --}}
                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                        @if ($active_tab == 1)
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                            <!--<div class="card" style="{{ ($lead->status == 1)?'background: #09d2949e':''}}">-->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" style="color: #13854E;font-weight: bold;">{{$lead->title}}</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Contact </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{@$lead->contactUser->name}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Company </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{@$lead->user->company->name}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer Need </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b><span style="color:#13854E;">{{@$lead->customer_need}}</b></span></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>PIC Sales</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{@$lead->salesUser->name}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created On</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{ date('l, d F Y', strtotime($lead->created_at)) }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created By</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{@$lead->createUser->name}}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Lead Activity
                                        <div class="pull-right">
                                            @if ((auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh') && ($lead->status != 1))
                                            <button class="btn btn-primary btn-sm" wire:click="showModal"><i class="fas fa-plus"></i> Tambah Data Activity</button>
                                            @endif
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <table class="display table table-striped table-hover" id="lead-activity">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Attachment</th>
                                                @if (auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh')
                                                <th>Action</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($activity as $act)
                                            <tr id="key-{{$act->id}}" key="{{$act->id}}">
                                                <td>{{ $act->title }}</td>
                                                <td>{{ date_format($act->start_date, 'd F Y, H:i') }}</td>
                                                <td>{{ date_format($act->end_date, 'd F Y, H:i') }}</td>
                                                <td>{{ $act->description }}</td>
                                                <td>{{ getStatusActivity($act->status) }}</td>
                                                <td>@if (!empty($act->attachment)) <a target="_blank" href="{{getImage( $act->attachment)}}" style="color:blue">Show File</a> @endif</td>
                                                @if (auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh')
                                                <td>
                                                    @if ($lead->status != 1)
                                                    <button class="btn btn-primary btn-sm" wire:click="getDataLeadActivityById({{$act->id}})"><i class="fa fa-pencil"></i>
                                                    </button>
                                                    @endif
                                                    <button class="btn btn-success btn-sm" wire:click="getDataLeadActivityById({{$act->id}},true)"><i class="fa fa-eye"></i>
                                                    </button>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @elseif($active_tab == 2)
                        <div class="tab-pane fade  active show" id="pills-transactive" role="tabpanel" aria-labelledby="pills-transactive-tab">
                            <div class="card">
                                <!-- test -->
                                <div class="card-header">
                                    <h4 class="card-title">Product Needs</h4>
                                </div>
                                <div class="card-body pl-2 pr-2 overflow-x-scroll">
                                    <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                                    <table width="100%">
                                        <thead class="thead-lightss">
                                            <tr>
                                                <th style="padding-left: 10px;" width="30%">Product Variant *</th>
                                                <th style="padding-left: 10px;" width="20%">Price</th>
                                                <th width="3%">Qty *</th>
                                                <th style="padding-left: 10px;" width="20%">Total Price</th>
                                                <th width="20%">Total Dpp + PPN</th>
                                                @if (auth()->user()->role->role_type != 'adminsales' || auth()->user()->role->role_type != 'leadwh' && auth()->user()->role->role_type != 'superadmin' && auth()->user()->role->role_type != 'leadsales' || $lead->status == '2')
                                                <th class="p-0" width="5%"></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                <td class="p-0">
                                                    <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                        <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin',
                                                            'leadsales']) || $lead->status == '2')?'disabled':''}}>
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

                                                            <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'min')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']) || $lead->status == '2'
                                                                )?'disabled':''}}> <i class="fas fa-minus"></i> </button>

                                                            <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>

                                                            <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'plus')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']) || $lead->status == '2'
                                                                )?'disabled':''}}> <i class="fas fa-plus"></i> </button>
                                                        </div> <!-- input-group.// -->
                                                    </div> <!-- input-group.// -->
                                                </td>
                                                <td class="p-0">
                                                    <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                </td>
                                                <td class="p-0">
                                                    <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control" {{(auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type != 'leadwh' || auth()->user()->role->role_type == 'superadmin' || $lead->status ==
                                                    '2')?'readonly':''}}>
                                                </td>
                                                @if ((auth()->user()->role->role_type != 'adminsales' && auth()->user()->role->role_type != 'leadwh' && auth()->user()->role->role_type != 'superadmin' && auth()->user()->role->role_type != 'leadsales') && ($lead->status != 1 && $lead->status != 2) && count($activity) > 0 )
                                                <td class="p-0">
                                                    @if ($index > 0)
                                                    <button class="btn btn-danger btn-sm"><i class="fas fa-times" wire:click="remove({{$index}})"></i></button>
                                                    @else
                                                    <button class="btn btn-success btn-sm"><i class="fas fa-plus" wire:click="add({{$index}})"></i></button>
                                                    @endif
                                                </td>
                                                @endif
                                                </tr>
                                                <tr>
                                                    <td colspan="4"></td>
                                                    <td>
                                                        @php $mm = $margin_bottom[$index]; if (!empty($mm)){ echo 'Margin Bottom : '.number_format($mm,0,'.','.'); } @endphp
                                                    </td>
                                                </tr>
                                                @endfor
                                        </tbody>
                                    </table>
                                    @if ((auth()->user()->role->role_type != 'adminsales' && auth()->user()->role->role_type != 'leadwh' && auth()->user()->role->role_type != 'superadmin' && auth()->user()->role->role_type != 'leadsales') && ($lead->status != 1 && $lead->status != 2) && count($activity) > 0)
                                    <div class="form-group">
                                        <button class="btn btn-primary pull-right mr-2" wire:click="store_product">Save</button>

                                        @if (count($productneeds) > 0 && count($activity) > 0 && ($lead->status != 1 && $lead->status != 2))
                                        <button class="btn btn-success pull-left mr-2" wire:click="assign_admin">Assign To Negotiation</button>
                                        @endif
                                    </div>
                                    @else
                                    @if ($lead->status == 2 && auth()->user()->role->role_type != 'sales')
                                    <hr>
                                    <div class="form-group">
                                        <label>Approval Negotiation</label>
                                        <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                                        <x-textarea name="approval_notes" label="Notes" />
                                        <div class="form-group">
                                            <button type="button" wire:click='approve(1)' class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Approve</button>
                                            <button type="button" wire:click='approve(3)' class="btn btn-danger btn-sm"><i class="fa fa-times pr-2"></i>Reject</button>
                                        </div>
                                    </div>
                                    @endif
                                    @endif
                                    <br><br><br>
                                    @if (!in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'leadsales']) && count($productneeds) > 0 && count($activity) > 0 && !in_array($lead->status, ['1', '2', '6']))
                                    <p style="color:red;font-style: italic;">&emsp; * Harga negosiasi telah melewati harga terendah. Anda harus melakukan proses <b>Negotiation</b> ke bagian <b>Lead Sales.</b> <br>&emsp; Silahkan klik tombol <b>"Assign To Negotiation".</b></p>
                                    @endif
                                    @if (!in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'leadsales']) && count($productneeds) > 0 && count($activity) > 0 && $lead->status == 1 && $lead->status != 2)
                                    <p style="color:green;font-style: italic;">&emsp; * Harga negosiasi belum melewati harga terendah. Anda dapat melanjutkan proses lead menjadi <b>Qualified.</b></p>
                                    @endif
                                    @if (!in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'leadsales']) && $lead->status == 6)
                                    <p style="color:red;font-style: italic;">&emsp; * Data lead ini telah di reject oleh Lead Sales. Silahkan periksa catatan dan lakukan negosiasi kembali untuk melakukan assign to lead kembali.</p>
                                    @endif
                                </div>

                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Negotiation
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <table id="basic-datatables2" class="display table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Notes</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($negotiation as $neg)
                                            <tr>
                                                <td>{{ $neg->created_at }}</td>
                                                <td>{{ $neg->notes }}</td>
                                                <td>{{ ($neg->status == '1')?'Approve':'Reject' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @elseif($active_tab == 3)
                        <div class="tab-pane fade active show" id="pills-transhistory" role="tabpanel" aria-labelledby="pills-transhistory-tab">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Lead Activity
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <table class="display table table-striped table-hover" id="basic-datatables3">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Description</th>
                                                        <th>Result</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($activity as $act)
                                                    <tr id="key-{{$act->id}}" key="{{$act->id}}">
                                                        <td>{{ $act->title }}</td>
                                                        <td>{{ $act->start_date }}</td>
                                                        <td>{{ $act->end_date }}</td>
                                                        <td>{{ $act->description }}</td>
                                                        <td>{{ $act->result }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Product Need
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <table width="100%">
                                                <thead class="thead-lightss">
                                                    <tr>
                                                        <th class="p-0" width="30%">Product Variant *</th>
                                                        <th class="p-0" width="20%">Price</th>
                                                        <th class="p-0" width="2%">Qty *</th>
                                                        <th class="p-0" width="15%">Total Price</th>
                                                        <th class="p-0" width="15%">Total Dpp + PPN</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                        <td class="p-0">
                                                            <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                                <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control" disabled>

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
                                                                    <!--<button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'min')"> <i class="fas fa-minus"></i> </button>-->
                                                                    <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>
                                                                    <!--<button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'plus')"> <i class="fas fa-plus"></i> </button>-->
                                                                </div> <!-- input-group.// -->
                                                            </div> <!-- input-group.// -->
                                                        </td>
                                                        <td class="p-0">
                                                            <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                        </td>
                                                        <td class="p-0">
                                                            <x-text-field type="text" name="price.{{$index}}" readonly />
                                                        </td>
                                                        </tr>
                                                        @endfor
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Negotiation
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <b>Negotiation</b>
                                            <table id="basic-datatables2" class="display table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($negotiation as $neg)
                                                    <tr>
                                                        <td>{{ $neg->created_at }}</td>
                                                        <td>{{ $neg->notes }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @elseif ($form_active)
            <div class="card">
                <div class="card-body">
                    <div class="form-group ">
                        <!-- <label for="customer_need" class="placeholder"><b>Customer Need (optional)</b> <span style="color:red"></span></label><br> -->
                        <input type="radio" value="new" name="type_customer" wire:model="type_customer"> <b>New Customer</b> &emsp;
                        <input type="radio" value="existing" name="type_customer" wire:model="type_customer"> <b>Existing Customer</b>
                        <small id="helpId" class="text-danger"></small>
                    </div>

                    <!-- <x-text-field type="text" name="title" label="Title" /> -->
                    <x-select name="brand_id" label="Brand" required multiple isreq="*" ignore>
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                        <option value="{{$brand->id}}">{{$brand->name}}</option>
                        @endforeach
                    </x-select>

                    <x-select name="contact" label="Contact (ketik untuk cari)" isreq="*" ignore>
                        <option value="">Pilih Contact</option>
                        @if (isset($selectedContact['user_id']) && isset($selectedContact['name']))
                        <option value="{{$selectedContact['user_id']}}">{{$selectedContact['name']}}</option>
                        @endif

                    </x-select>
                    @if (auth()->user()->role->role_type != 'sales')
                    <x-select name="sales" label="Sales (ketik untuk cari)" id="select2-sales" isreq="*" ignore>
                        <option value="">Select Sales</option>
                        @if (isset($selectedSales['user_id']) && isset($selectedSales['name']))
                        <option value="{{$selectedSales['user_id']}}">{{$selectedSales['name']}}</option>
                        @endif
                    </x-select>
                    @else
                    <div class="form-group">
                        <label>Select Sales <span style="color:red">*</span></label>
                        <select name="sales" class="form-control" id="select2-sales" disabled ignore>
                            <option value="{{ auth()->user()->id }}">{{ auth()->user()->name }}</option>
                        </select>
                    </div>
                    @endif
                    <x-select name="warehouse_id" label="Pengiriman dari Warehouse" required ignore>
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $war)
                        <option value="{{$war->id}}">{{$war->name}}</option>
                        @endforeach
                    </x-select>
                    <x-select name="payment_term" label="Term of Payment" required ignore>
                        <option value="">Select TOP</option>
                        @foreach ($paymentterms as $pay)
                        <option value="{{$pay->id}}">{{$pay->name}}</option>
                        @endforeach
                    </x-select>
                    <!-- <x-text-field type="text" name="lead_type" label="Lead Type" /> -->
                    <x-text-field type="text" name="customer_need" label="Customer Need (optional)" />

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
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
                    <livewire:table.lead-master-table params="{{$route_name}}" />
                </div>
                @endif
            </div>


            @endif

        </div>

        {{-- Modal Activity --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} lead activity</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="activity_id" id="activity_id" />
                        <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                        <x-text-field type="text" name="title" label="Title" isreq="*" />
                        <x-textarea type="textarea" name="description" label="Description" />
                        <x-text-field type="date" name="start_date" label="Start Date" isreq="*" />
                        <x-text-field type="time" name="start_time" label="Start Time" />
                        <div class="form-group">
                            <p style="color:red">* Anda dapat menambahkan pengingat jika range activity melebihi waktu 7 hari</p>
                        </div>
                        <x-text-field type="date" name="end_date" label="End Date" isreq="*" />
                        <x-text-field type="time" name="end_time" label="End Time" />
                        <x-textarea type="text" name="result" label="Result" maxlength="300" />
                        <div class="form-group" style="margin-top: -15px;"><small>(Maks. 300 char)</small></div>
                        <x-input-file file="{{$attachment}}" path="{{optional($attachment_path)->getClientOriginalName()}}" name="attachment_path" label="Attachment" />
                        <x-select name="status" label="Status" ignore isreq="*">
                            <option value="1" {{$status==1 ?'selected':''}}>In Progress</option>
                            <option value="2" {{$status==2 ?'selected':''}}>Open</option>
                            <option value="3" {{$status==3 ?'selected':''}}>Complete</option>
                            <option value="4" {{$status==4 ?'selected':''}}>Cancel</option>
                        </x-select>
                        <br>
                        @php
                        $tgl1 = new DateTime($start_date);
                        $tgl2 = new DateTime($end_date);
                        $jarak = $tgl2->diff($tgl1);
                        @endphp

                        <div class="reminder" {{ ($jarak->d < 7)?'style=display:none':''}}>
                                <label class="switch">
                                    <input type="checkbox" wire:model="reminder" value="1">
                                    <span class="slider round"></span>
                                </label> &nbsp;Reminder

                                <div class="form-group">
                                    <p style="color:red">* User akan mendapatkan email pengingat 7 hari sekali secara otomatis</p>
                                </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" wire:click={{$update_mode ? 'update_activity' : 'store_activity' }} class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>

                    </div>
                </div>
            </div>
        </div>



        <div id="activity-detail-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <!-- <div class="modal-dialogl" permission="document" style="position: relative;margin: 0 auto;top: 25%;"> -->
            <div class="modal-dialog" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Detail Lead Activity
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>

                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">Title</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$title}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Description</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$description}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Start date</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$start_date}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">End date</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$end_date}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Result</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{$result}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Attachment</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">@if (!empty($attachment)) <a target="_blank" href="{{getImage( $attachment)}}" style="color:blue">Show File</a> @endif</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Status 1</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8">{{ getStatusActivity($status)}}</div>
                        </div>
                        
                    </div>
                    <!--<div class="modal-footer">
                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>-->
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

        {{-- Modal confirm approve --}}
        <div id="approve-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Approval</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                        <x-textarea name="approval_notes" label="Notes" />
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click='approve(1)' class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Approve</button>
                        <button type="button" wire:click='approve(3)' class="btn btn-danger btn-sm"><i class="fa fa-times pr-2"></i>Reject</button>
                    </div>
                </div>
            </div>
        </div>




    </div>
    <style type="text/css">
        .nav-pills.nav-secondary .nav-link.active {
            background: #13854E;
            border: 1px solid #13854E;
        }

        .nav-pills>li>.nav-link {
            color: #13854E;
            font-weight: bold;
        }
    </style>
    @push('styles')
    <style>
        /* Important part */
        .modal-dialogl {
            overflow-y: initial !important
        }

        .modal-body {
            /* height: 80vh; */
            overflow-y: auto;
        }
    </style>
    <!-- Custom css -->
    <link href="{{asset('css/ui.css?v=2.0')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('css/responsive.css?v=2.0')}}" rel="stylesheet" type="text/css" />
    @endpush
    @push('scripts')

    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>
    <script src="{{asset('assets/js/plugin/datatables/datatables.min.js')}}"></script>
    <script>
        $(document).ready(function(value) {
            $('#basic-datatables').DataTable({});
            $('#basic-datatables2').DataTable({});
            $('#basic-datatables3').DataTable({});

            window.livewire.on('initData', (data) => {
                $('#filter_contact').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('filter_contact', data);
                    @this.call('selectedContact', data);
                });

                $('#filter_sales').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('filter_sales', data);
                    @this.call('selectedSales', data);
                });


                $('#filter_contact').select2({
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
                                type: 'user'
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
                        cache: true
                    },
                });

                $('#filter_sales').select2({
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
                                type: 'user'
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
                        cache: true
                    },
                });
            });

            window.livewire.on('loadForm', (user_id) => {
                $('#basic-datatables').DataTable({});
                $('#basic-datatables2').DataTable({});
                $('#basic-datatables3').DataTable({});

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
                                user_id
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
                        cache: true
                    },
                });
                $('#contact').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('contact', data);
                });

                // sales
                $('#select2-sales').select2({
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
                                user_id
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
                        cache: true
                    },
                });
                $('#select2-sales').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('sales', data);
                });

                // brand_id
                $('#brand_id').select2({
                    theme: "bootstrap",
                });
                $('#brand_id').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('brand_id', data);
                });

                // warehouse_id
                $('#warehouse_id').select2({
                    theme: "bootstrap",
                });
                $('#warehouse_id').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('warehouse_id', data);
                });

                // payment_term
                $('#payment_term').select2({
                    theme: "bootstrap",
                });
                $('#payment_term').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('payment_term', data);
                });
            });

            window.livewire.on('showModal', (data) => {
                console.log({{$start_date}})
                // $('#start_date').val({{$start_date}});
                $('#uid_lead').val({{$uid_lead}});
                $('#activity_id').val({{$activity_id}});
                $('#form-modal').modal('show')
            });

            window.livewire.on('showModalDetail', (data) => {
                console.log({{$start_date}})
                $('#uid_lead').val({{$uid_lead}});
                $('#activity_id').val({{$activity_id}});
                $('#activity-detail-modal').modal('show')
            });

            window.livewire.on('showModalApproval', (data) => {
                $('#uid_lead').val({{$uid_lead}});
                $('#approve-modal').modal('show')
            });

            window.livewire.on('showModalProduct', (data) => {
                $('#uid_lead').val({{$uid_lead}});
                $('#form-modal-product').modal('show')
            });
      

            window.livewire.on('closeModal', (data) => {
                $('#contact-modal').modal('hide')
                $('#confirm-modal').modal('hide')
                $('#form-modal').modal('hide')
                $('#approve-modal').modal('hide')
                $('#activity-detail-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>