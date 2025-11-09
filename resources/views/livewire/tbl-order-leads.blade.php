<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>Order leads</span>
                        </a>
                        <div class="pull-right">
                            <button class="btn btn-primary btn-sm ml-2" wire:click="export"><i class="fas fa-excel"></i>
                                Export To Excel</button>
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>
                            @else
                            @if (auth()->user()->hasTeamPermission($curteam, $route_name.':create'))
                            <!-- <button class="btn btn-primary btn-sm" wire:click="{{$modal ? 'showModal' : 'toggleForm(true)'}}"><i class="fas fa-plus"></i> Tambah Data</button> -->
                            @endif
                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        @if (!$form_active && !$detail)
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if (in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']))
                    <div class="row">
                        <div class="col-md-3">
                            <x-select name="filter_contact" label="Pilih Contact" handleChange="selectedContact">
                                <option value="all">Semua Contact</option>
                                @foreach ($contact_list as $contact)
                                <option value="{{$contact->id}}">{{$contact->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-select name="filter_sales" label="Pilih Sales" handleChange="selectedSales">
                                <option value="all">Semua Sales</option>
                                @foreach ($sales_list as $sales)
                                <option value="{{$sales->id}}">{{$sales->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-select name="filter_status" label="Pilih Status" handleChange="selectedStatus">
                                <option value="all">Semua Status</option>
                                <option value="1">New</option>
                                <option value="2">Open</option>
                                <option value="3">Closed</option>
                                <option value="4">Canceled</option>
                            </x-select>
                        </div>
                        <div class="col-md-3">
                            <x-text-field type="text" name="reportrange" label="Tanggal" id="reportrange" />
                        </div>
                    </div>
                    @elseif (in_array(auth()->user()->role->role_type, ['collector']))
                    <div class="row">
                        <div class="col-md-4">
                            <x-select name="filter_status" label="Pilih Status Penagihan" handleChange="selectedStatus">
                                <option value="all">Semua Status</option>
                                <option value="0">Open</option>
                                <option value="1">Menunggu Verifikasi</option>
                                <option value="2">Diverifikasi</option>
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-select name="filter_contact" label="Pilih Contact" handleChange="selectedContact">
                                <option value="all">Semua Contact</option>
                                @foreach ($contact_list as $contact)
                                <option value="{{$contact->id}}">{{$contact->name}}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div class="col-md-4">
                            <x-text-field type="text" name="reportrange" label="Tanggal" id="reportrange" />
                        </div>

                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12">
            @if ($detail)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title" style="font-weight: bold;">Lead Management
                        <div class="pull-right" style="font-size:14px;">Status :
                            @if($lead->status == '0')
                            <button class="btn btn-warning btn-xs" style="font-size:13px;">
                                @elseif($lead->status == '1')
                                <button class="btn btn-warning btn-xs" style="font-size:13px;">
                                    @elseif($lead->status == '2')
                                    <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                        @elseif($lead->status == '3')
                                        <button class="btn btn-success btn-xs" style="font-size:13px;">
                                            @elseif($lead->status == '4')
                                            <button class="btn btn-danger btn-xs" style="font-size:13px;">
                                                @elseif($lead->status == '6')
                                                <button class="btn btn-xs" style="font-size:13px;background-color:#f74f1d;color:white">
                                                    @else
                                                    <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                                        @endif
                                                        {{ getStatusOrderLead($lead->status) }}</button>
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
                        <li class="nav-item submenu">
                            <!-- <a class="nav-link @if ($active_tab == 1) active show @endif truncate" id="pills-info-tab" href="#" wire:click="_moveTab(1)">Stage 1 : New</a> -->
                            <a class="nav-link @if ($lead->status == '0' || $lead->status == '1') active show @endif truncate" id="pills-info-tab" href="#" @if ($lead->status == '0' || $lead->status == '1') wire:click="_moveTab(1)" @endif>Stage 1 : New</a>
                        </li>
                        <li class="nav-item submenu">
                            <!-- <a class="nav-link @if ($active_tab == 2) active show @endif truncate" id="pills-transactive-tab" href="#" role="tab" wire:click="_moveTab(2)">Stage 2 : Open</a> -->
                            <a class="nav-link @if ($lead->status == 2) active show @endif truncate" id="pills-transactive-tab" href="#" role="tab" @if ($lead->status == 2) wire:click="_moveTab(2)" @endif >Stage 2 : Open</a>
                        </li>
                        <li class="nav-item submenu">
                            <!-- <a class="nav-link @if ($active_tab == 3) active show @endif truncate" id="pills-transhistory-tab" href="#" role="tab" wire:click="_moveTab(3)">Stage 3 : Closed</a> -->
                            <a class="nav-link @if ($lead->status == 3) active show @endif truncate" id="pills-transhistory-tab" href="#" role="tab" @if ($lead->status == 3) wire:click="_moveTab(3)" @endif >Stage 3 : Closed</a>
                        </li>
                    </ul>
                    {{-- dany --}}
                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                        @if ($lead->status == '0' || $lead->status == '1')
                        <div class="tab-pane fade active show" id="pills-info" role="tabpanel" aria-labelledby="pills-info-tab">
                            <!--<div class="card" style="{{ ($lead->status == 1)?'background: #09d2949e':''}}">-->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" style="color: #13854E;font-weight: bold;">{{$lead->title}}
                                        <div class="pull-right">
                                            <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;font-family: 'Nunito', sans-serif;" wire:click="getDataLeadActivityById('{{ $lead->uid_lead }}')"><i class="fa fa-eye"></i> Detail Activity</button>
                                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.so', $lead->uid_lead) }}" target="_blank"><i class="fa fa-print"></i> Print SO</a>
                                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.sj', $lead->uid_lead) }}" target="_blank"><i class="fa fa-print"></i> Print SJ</a>
                                            <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" wire:click="export_detail('{{ $lead->uid_lead }}')"><i class="fa fa-print"></i> Export Data</button>
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Contact </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->contactUser->name)?'-':$lead->contactUser->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Company </td>
                                                        <td>&nbsp; </td>
                                                        <td> : <b>{{(empty($lead->company_name)?'-':$lead->company_name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer Need </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b><span style="color:#13854E;">{{(empty($lead->customer_need)?'-':$lead->customer_need)}}</b></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>PIC Sales</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->salesUser->name)?'-':$lead->salesUser->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created On</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{ date('l, d F Y', strtotime($lead->created_at)) }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created By</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->createUser->name)?'Created By System':$lead->createUser->name)}}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Warehouse</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->warehouse->name)?'-':$lead->warehouse->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Order No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->order_number)?'-':$lead->order_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Invoice No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->invoice_number)?'-':$lead->invoice_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Reference No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->reference_number)?'-':$lead->reference_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Payment Term</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->paymentTerm->name)?'-':$lead->paymentTerm->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Due Date</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->due_date)?'-':$lead->due_date)}}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Address Information
                                        <div class="pull-right">
                                            <button class="btn btn-primary btn-sm" wire:click="showModalAddress('{{$lead->contact}}')"><i class="fas fa-plus"></i> Tambah Data</button>
                                            <!-- <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" wire:click="getDataLeadActivityById('{{ $lead->uid_lead }}')"><i class="fa fa-eye"> Detail Activity</i></button> -->
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                            <table width="100%" class="table table-stripped">
                                                <thead class="thead-lightss">
                                                    <tr>
                                                        <th class="p-0" width="10%">No</th>
                                                        <th class="p-0" width="10%">Type</th>
                                                        <th class="p-0" width="30%">Address</th>
                                                        <th class="p-0" width="15%">Phone 1</th>
                                                        <th class="p-0" width="15%">Phone 2</th>
                                                        <th class="p-0" width="15%">Kode Pos</th>
                                                        <th class="p-0" width="5%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (count($addresslists) > 0)
                                                    @foreach ($addresslists as $key => $add)
                                                    <tr>
                                                        <td class="p-0">{{$key+1}}</td>
                                                        <td class="p-0">{{$add->type}}</td>
                                                        <td class="p-0">{{@$add->alamat}}</td>
                                                        <td class="p-0">{{$add->telepon}}</td>
                                                        <td class="p-0">{{@$add->phone}}</td>
                                                        <td class="p-0">{{$add->kodepos}}</td>
                                                        <td class="p-0">
                                                            <div class="list-group-item-figure" id="addr">
                                                                <div class="dropdown">
                                                                    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fas fa-ellipsis-h"></i>
                                                                    </button>
                                                                    <div class="dropdown-arrow"></div>
                                                                    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                        <button wire:click="setDefault('{{ $add->id }}')" class="dropdown-item">Set Default</button>
                                                                        <button wire:click="getDetailAddress('{{ $add->id }}')" class="dropdown-item">Lihat</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @else
                                                    <tr>
                                                        <td class="p-0" colspan="7">
                                                            <div style="height: 200px;">
                                                                <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                                                    <div class="flex flex-col justify-center items-center mt-8">
                                                                        <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                                        <span>Tidak Ada Data</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Metode Pengiriman</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p>Order No : {{(empty($lead->order_number)?'-':$lead->order_number)}}</p>
                                            <br>
                                        </div>

                                        <div class="col-md-10">
                                            <b>Main Address</b>
                                            <input type="hidden" wire:model="address_id">
                                            <input type="hidden" wire:model="uid_lead">
                                            <p>{{ @$mainaddress->alamat }}</p>
                                        </div>
                                        <div class="col-md-2">
                                            Tipe Pengiriman<br>
                                            <input type="radio" value="1" checked> Normal
                                        </div>
                                    </div>
                                    <br><br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                                            <table width="100%">
                                                <thead class="thead-lightss">
                                                    <tr>
                                                        <th class="p-0" width="20%">Product *</th>
                                                        <th class="p-0" width="16%">Price</th>
                                                        <th class="p-0" width="9%">Qty *</th>
                                                        <th class="p-0" width="10%">Discount</th>
                                                        <th class="p-0" width="10%">Tax</th>
                                                        <th class="p-0" width="15%">Total Price</th>
                                                        <th class="p-0" width="15%">Final Price</th>
                                                        @if (auth()->user()->role->role_type != 'adminsales' && auth()->user()->role->role_type != 'leadwh' && auth()->user()->role->role_type != 'superadmin' && auth()->user()->role->role_type != 'leadsales')
                                                        <th class="p-0" width="5%"></th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $item = 0; $total = 0; $ppn = 0; @endphp
                                                    @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                        <td class="p-0">
                                                            <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                                <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin',
                                                                    'leadsales','sales']))?'disabled':''}}>
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

                                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'min')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'sales']))?'disabled':''}}> <i
                                                                            class="fas fa-minus"></i> </button>

                                                                    <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>

                                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'plus')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales', 'sales']))?'disabled':''}}> <i
                                                                            class="fas fa-plus"></i> </button>
                                                                </div> <!-- input-group.// -->
                                                            </div> <!-- input-group.// -->
                                                        </td>
                                                        <td class="p-2">
                                                            <select name="discount.{{$index}}" wire:model="discount.{{$index}}" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']))?'disabled':''}}>
                                                                @foreach ($discounts as $disc)
                                                                <option value="{{$disc->id}}">{{$disc->title}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="p-2">
                                                        @php $ppn += @$tax[$index]->tax_percentage; @endphp
                                                            <select name="tax.{{$index}}" wire:model="tax.{{$index}}" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales']))?'disabled':''}}>
                                                                @foreach ($taxes as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->tax_code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="p-0">
                                                            <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                        </td>
                                                        <td class="p-0">
                                                            @php $total += $price[$index]; $item += $index; @endphp
                                                            <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control" readonly>

                                                        </td>
                                                        @if ((auth()->user()->role->role_type != 'adminsales' && auth()->user()->role->role_type != 'leadwh' && auth()->user()->role->role_type != 'superadmin' && auth()->user()->role->role_type != 'leadsales') && ($lead->status != 1))
                                                        <td class="p-0">
                                                            @if ($index > 0)
                                                            <button class="btn btn-danger btn-sm"><i class="fas fa-times" wire:click="remove({{$index}})"></i></button>
                                                            @else
                                                            <button class="btn btn-success btn-sm"><i class="fas fa-plus" wire:click="add({{$index}})"></i></button>
                                                            @endif
                                                        </td>
                                                        @endif
                                                        </tr>
                                                        @endfor
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <hr><br>
                                    <p>Produk akan dikirim dengan kurir perusahaan. Silahkan assign nama pic kurir / bagian warehouse dibawah ini :</p>
                                    <div class="row" style="margin-bottom: 20px">
                                        <div class="col-md-12">
                                            <x-select name="courier" label="Pic Kurir / Bagian Warehouse" id="select2" isreq="*" ignore>
                                                <option value="">Select Courier</option>
                                                @foreach ($courier_list as $cou)
                                                <option value="{{$cou->id}}">{{$cou->name}}</option>
                                                @endforeach
                                            </x-select>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <i>Anda dapat melakukan perubahan saat data belum masuk ke dalam proses assign to warehouse</i>
                                        <button class="btn btn-primary pull-right mr-2" wire:click="store_shipping">Save</button>
                                    </div>
                                </div>

                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Detail Payment
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-lg-3" style="line-height: 36px;">
                                            <p>Sub Total</p>
                                            <p>Tax Total</p>
                                            <p>Diskon</p>
                                            <p><b>Total</b></p>
                                            <p>Notes</p>

                                        </div>
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <p>: Rp {{ number_format($total,0,',','.') }}</p>
                                            @if ($ppn != 0)
                                            <p>: Rp {{ number_format($ppn = (0.11 * $total),0,',','.') }}</p>
                                            @else
                                            <p>: Rp {{ number_format($ppn = 0,0,',','.') }}</p>
                                            @endif
                                            <p>: Rp {{ $biayakirim = 0 }}</p>
                                            <p>: <b>Rp {{ number_format($total + $ppn + $biayakirim,0,',','.') }}</b></p>
                                            <!-- <p><input id="notes" name="notes" wire:model="notes" type="text" class="form-control"></p> -->

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <i>Anda Dapat Melanjutkan Proses Order Dengan Menekan Tombol <br>Assign To Warehouse</i>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-success pull-right mr-2" wire:click="assign_warehouse('{{$uid_lead}}')">Assign To Warehouse</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @elseif($lead->status == 2)
                        <div class="tab-pane fade  active show" id="pills-transactive" role="tabpanel" aria-labelledby="pills-transactive-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title" style="color: #003e8b;font-weight: bold;">{{$lead->title}}
                                        <div class="pull-right">
                                            <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;font-family: 'Nunito', sans-serif;" wire:click="getDataLeadActivityById('{{ $lead->uid_lead }}')"><i class="fa fa-eye"></i> Detail Activity</button>
                                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.so', $lead->uid_lead) }}" target="_blank"><i class="fa fa-print"></i> Print SO</a>
                                            <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" wire:click="export_detail('{{ $lead->uid_lead }}')"><i class="fa fa-print"></i> Export Data</button>
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Contact </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->contactUser->name)?'-':$lead->contactUser->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Company </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->user->company->name)?'-':$lead->user->company->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Customer Need </td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b><span style="color:#13854E;">{{(empty($lead->customer_need)?'-':$lead->customer_need)}}</b></span></td>
                                                    </tr>
                                                    <tr>
                                                        <td>PIC Sales</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->salesUser->name)?'-':$lead->salesUser->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created On</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{ date('l, d F Y', strtotime($lead->created_at)) }}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Created By</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->createUser->name)?'Created By System':$lead->createUser->name)}}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <table>
                                                <tbody>
                                                    <tr>
                                                        <td>Warehouse</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->warehouse->name)?'-':$lead->warehouse->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Order No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->order_number)?'-':$lead->order_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Invoice No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->invoice_number)?'-':$lead->invoice_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Reference No.</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->reference_number)?'-':$lead->reference_number)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Payment Term</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->paymentTerm->name)?'-':$lead->paymentTerm->name)}}</b></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Due Date</td>
                                                        <td>&nbsp;</td>
                                                        <td> : <b>{{(empty($lead->due_date)?'-':$lead->due_date)}}</b></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Detail Order
                                        <div class="pull-right">
                                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.sj', $lead->uid_lead) }}" target="_blank"><i class="fa fa-print"></i> Bukti Pengiriman</a>
                                            <button class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;font-family: 'Nunito', sans-serif;" wire:click="getDataLeadNegotiationById('{{ $lead->uid_lead }}')"><i class="fa fa-eye"></i> Detail
                                                Negotiation</button>
                                            &nbsp;
                                            <div class="pull-right" style="font-size:14px;">Status Pengiriman :
                                                @if($lead->status_pengiriman == '0' || empty($lead->status_pengiriman))
                                                <button class="btn btn-warning btn-xs" style="font-size:13px;">
                                                    @elseif($lead->status_pengiriman == '1')
                                                    <button class="btn btn-warning btn-xs" style="font-size:13px;">
                                                        @elseif($lead->status_pengiriman == '2')
                                                        <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                                            @elseif($lead->status_pengiriman == '3')
                                                            <button class="btn btn-success btn-xs" style="font-size:13px;">
                                                                @elseif($lead->status_pengiriman == '6')
                                                                <button class="btn btn-xs" style="font-size:13px;background-color:#f74f1d;color:white">
                                                                    @else
                                                                    <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                                                        @endif
                                                                        {{ getStatusPengiriman($lead->status_pengiriman) }}</button>
                                            </div>
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p>Order No : {{(empty($lead->order_number)?'-':$lead->order_number)}}</p>
                                            <br>
                                        </div>

                                        <div class="col-md-10">
                                            <b>Main Address</b>
                                            <p>{{ @$mainaddress->alamat }}</p>
                                        </div>
                                        <div class="col-md-2">
                                            Tipe Pengiriman<br>
                                            <input type="radio" value="1" checked> Normal
                                        </div>
                                    </div>
                                    <br><br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="hidden" wire:model="uid_lead" id="uid_lead" />
                                            <table width="100%">
                                                <thead class="thead-lightss">
                                                    <tr>
                                                        <th class="p-0" width="30%">Product *</th>
                                                        <th class="p-0" width="20%">Price</th>
                                                        <th class="p-0" width="9%">Qty *</th>
                                                        <th class="p-0" width="15%">Total Price</th>
                                                        <th class="p-0" width="15%">Final Price</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $item = 0; $total = 0; @endphp
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

                                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'min')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales','collector']))?'disabled':''}}>
                                                                        <i class="fas fa-minus"></i> </button>

                                                                    <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>

                                                                    <button class="btn btn-light btn-xs" type="button" wire:click="getPrice({{$product_id[$index]}},{{$index}},'plus')" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin',
                                                                        'leadsales','collector']))?'disabled':''}}> <i class="fas fa-plus"></i> </button>
                                                                </div> <!-- input-group.// -->
                                                            </div> <!-- input-group.// -->
                                                        </td>
                                                        <td class="p-0">
                                                            <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                        </td>
                                                        <td class="p-0">
                                                            @php $total += $price[$index]; $item += $index; @endphp
                                                            <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control" {{in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin', 'leadsales',
                                                            'collector'])?'readonly':''}}>

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
                                <div class="card-header">
                                    <h4 class="card-title">Detail Payment
                                        <div class="pull-right">
                                            <a class="btn btn-primary btn-sm" style="background: white !important;color: #003E8B;font-size: 10pt;" href="{{ route('print.so', $lead->uid_lead) }}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                                            &nbsp;
                                            <div class="pull-right" style="font-size:14px;">Invoice :
                                                @if($lead->status_invoice == '0' || empty($lead->status_invoice))
                                                <button class="btn btn-primary btn-xs" style="font-size:13px;">Active
                                                    @elseif($lead->status_invoice == '4')
                                                    <button class="btn btn-warning btn-xs" style="font-size:13px;">Grace Period
                                                        @elseif($lead->status == '2')
                                                        <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                                            @elseif($lead->status == '3')
                                                            <button class="btn btn-success btn-xs" style="font-size:13px;">
                                                                @elseif($lead->status == '4')
                                                                <button class="btn btn-danger btn-xs" style="font-size:13px;">
                                                                    @elseif($lead->status == '6')
                                                                    <button class="btn btn-xs" style="font-size:13px;background-color:#f74f1d;color:white">
                                                                        @else
                                                                        <button class="btn btn-primary btn-xs" style="font-size:13px;">
                                                                            @endif
                                                                        </button>
                                            </div>
                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-lg-3" style="line-height: 36px;">
                                            <p>Sub Total</p>
                                            <p>Tax Total</p>
                                            <p>Biaya Pengiriman</p>
                                            <p><b>Total</b></p>

                                        </div>
                                        <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                            <p>: Rp {{ number_format($total,0,',','.') }}</p>
                                            <p>: Rp {{ number_format($ppn = (0.11 * $total),0,',','.') }}</p>
                                            <p>: Rp {{ $biayakirim = 0 }}</p>
                                            <p>: <b>Rp {{ number_format($total + $ppn + $biayakirim,0,',','.') }}</b></p>
                                            <!-- <p><input id="notes" name="notes" wire:model="notes" type="text" class="form-control"></p> -->

                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if (in_array(auth()->user()->role->role_type, ['collector','adminsales','leadwh','leadsales','superadmin']))
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Upload Informasi Penagihan
                                        <div class="pull-right">
                                            <button class="btn btn-primary btn-sm" wire:click="showModalPenagihan('{{$lead->uid_lead}}')"><i class="fas fa-plus"></i> Tambah Data</button>

                                        </div>
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12" style="line-height: 36px;">
                                            <table width="100%" class="table table-stripped">
                                                <thead class="thead-lightss">
                                                    <tr>
                                                        <th class="p-0" width="5%">No</th>
                                                        <th class="p-0" width="15%">Nama Akun</th>
                                                        <th class="p-0" width="15%">Nama Bank</th>
                                                        <th class="p-0" width="15%">Jumlah Transfer</th>
                                                        <th class="p-0" width="15%">Date</th>
                                                        <th class="p-0" width="15%">Attachment</th>
                                                        <th class="p-0" width="15%">Bukti Transfer</th>
                                                        <th class="p-0" width="5%">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if (count($billinglists) > 0)
                                                    @foreach ($billinglists as $key => $add)
                                                    <tr>
                                                        <td class="p-0">{{$key+1}}</td>
                                                        <td class="p-0">{{$add->account_name}}</td>
                                                        <td class="p-0">{{$add->account_bank}}</td>
                                                        <td class="p-0">{{$add->total_transfer}}</td>
                                                        <td class="p-0">{{@$add->transfer_date}}</td>
                                                        <td class="p-0">@if (!empty($add->upload_billing_photo)) <a target="_blank" href="{{getImage( $add->upload_billing_photo)}}">Show File</a> @endif</td>
                                                        <td class="p-0">@if (!empty($add->upload_transfer_photo)) <a target="_blank" href="{{getImage( $add->upload_transfer_photo)}}">Show File</a> @endif</td>
                                                        <td class="p-0">
                                                            @if (($add->status == 0 || $add->status == null) && !in_array(auth()->user()->role->role_type, ['collector']))
                                                            <div class="list-group-item-figure" id="addr">
                                                                <div class="dropdown">
                                                                    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
                                                                        <i class="fas fa-ellipsis-h"></i>
                                                                    </button>
                                                                    <div class="dropdown-arrow"></div>
                                                                    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                                        <button wire:click="verify_billing('{{ $add->id }}','1')" class="dropdown-item">Approve</button>
                                                                        <button wire:click="verify_billing('{{ $add->id }}','2')" class="dropdown-item">Reject</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @else
                                                            <span class="badge {{ ($add->status == 1)?'badge-success':'badge-danger' }}">{{ ($add->status == 1) ? 'Diverifikasi' : (($add->status == 0)? 'Waiting Approval' : 'Rejected') }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @else
                                                    <tr>
                                                        <td class="p-0" colspan="7">
                                                            <div style="height: 200px;">
                                                                <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                                                    <div class="flex flex-col justify-center items-center mt-8">
                                                                        <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                                                        <span>Tidak Ada Data</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if (in_array(auth()->user()->role->role_type, ['superadmin','adminsales','leadwh','leadsales']))
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Set Reminder
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Anda dapat mengaktifkan fitur reminder kepada User terakit dibawah ini :</label>
                                                <table class="table table-lightss">
                                                    <thead class="thead-lightss">
                                                        <tr>
                                                            <th width="30%" class="p-0">Contact</th>
                                                            <th width="15%" class="p-0">Before 7 Day <i class="fa fa-info fa-tooltip" title="Before 7 Day"></th>
                                                            <th width="15%" class="p-0">Before 3 Day <i class="fa fa-info fa-tooltip" title="Before 3 Day"></th>
                                                            <th width="15%" class="p-0">Before 1 Day <i class="fa fa-info fa-tooltip" title="Before 1 Day"></th>
                                                            <th width="15%" class="p-0">After 7 Day <i class="fa fa-info fa-tooltip" title="After 7 Day"></th>
                                                            <th width="10%" class="p-0"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for ($index = 0; $index < count($inputs2); $index++) <tr>
                                                            <td class="p-0">
                                                                <x-select name="r_contact.{{$index}}">
                                                                    <option value="">Select Contact</option>
                                                                    @foreach ($contact_list as $con)
                                                                    <option value="{{$con->id}}">{{$con->name}} - {{ $con->role_type }}</option>
                                                                    @endforeach
                                                                </x-select>
                                                            </td>
                                                            <td class="p-0">
                                                                <label class="switch">
                                                                    <input type="checkbox" wire:model="r_before_7_day.{{$index}}" value="1">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </td>
                                                            <td class="p-0">
                                                                <label class="switch">
                                                                    <input type="checkbox" wire:model="r_before_3_day.{{$index}}" value="1">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </td>
                                                            <td class="p-0">
                                                                <label class="switch">
                                                                    <input type="checkbox" wire:model="r_before_1_day.{{$index}}" value="1">
                                                                    <span class="slider round"></span>
                                                                </label>
                                                            </td>
                                                            <td class="p-0">
                                                                <label class="switch">
                                                                    <input type="checkbox" wire:model="r_after_7_day.{{$index}}" value="1">
                                                                    <span class="slider round"></span>
                                                                </label>
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
                                                <div class="form-group">
                                                    <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <i>Pastikan Anda telah mendownload surat jalan dan melakukan pengemasan terlebih dahulu untuk melanjutkan ke proses Pengiriman Product </i>
                                        </div>
                                        <div class="col-md-5">
                                            @if ($lead->status_pengiriman == '0' || empty($lead->status_pengiriman))
                                            <button class="btn btn-success pull-right mr-2" wire:click="set_pengiriman('1',{{ $lead->paymentTerm->days_of }})">Dikirim</button>
                                            @elseif ($lead->status_pengiriman == '1' && (auth()->user()->role->role_type == 'warehouse' || auth()->user()->role->role_type == 'admindelivery'))
                                            <div class="row">
                                                <div class="col-md-5" style="margin-top: -10px;">
                                                    <x-input-file file="{{$upload_billing_photo}}" path="{{optional($upload_billing_photo_path)->getClientOriginalName()}}" name="upload_billing_photo" />
                                                </div>
                                                <div class="col-md-1"></div>
                                                <div class="col-md-6">
                                                    <button class="btn btn-success pull-right mr-2" wire:click="set_pengiriman('2')">Submit Bukti Pengiriman</button>
                                                </div>
                                            </div>
                                            @elseif ($lead->status == '2' && $lead->status_invoice != '4')
                                            <button class="btn btn-success pull-right mr-2" href="#approve-modal" data-toggle="modal">Payment</button>
                                            @elseif ($lead->status == '2' && $lead->status_invoice == '4')
                                            <button class="btn btn-warning pull-right mr-2" wire:click="showModalPenarikan('{{$lead->uid_lead}}')">Penarikan Barang</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @elseif($lead->status == 3)
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
                                                        <th class="p-0" width="30%">Product *</th>
                                                        <th class="p-0" width="20%">Price</th>
                                                        <th class="p-0" width="15%">Qty *</th>
                                                        <th class="p-0" width="15%">Total Price</th>
                                                        <th class="p-0" width="15%">Total Dpp + PPN</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                        <td class="p-0">
                                                            <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                                <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control ">
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
                        <input type="radio" value="new" name="type_customer" wire:model="type_customer"> <b>New Customer</b> &emsp;
                        <input type="radio" value="existing" name="type_customer" wire:model="type_customer"> <b>Existing Customer</b>
                        <small id="helpId" class="text-danger"></small>
                    </div>
                    <x-select name="warehouse_id" label="Warehouse" required ignore>
                        <option value="">Select Warehouse</option>
                        @foreach ($warehouses as $war)
                        <option value="{{$war->id}}">{{$war->name}}</option>
                        @endforeach
                    </x-select>
                    <!-- <x-select name="brand_id" label="Brand" disabled ignore>
                            <option value="">Select Brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </x-select> -->
                    <div class="form-group">
                        <label>Brand<span style="color:red">*</span></label>
                        <select name="brand_id" wire:model="brand_id" class="form-control" disabled>
                            <option value="">Select Brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- <x-select name="contact" label="Contact" id="select2" isreq="*" disabled ignore>
                            <option value="">Select Contact</option>
                            @foreach ($contact_list as $con)
                            <option value="{{$con->id}}">{{$con->name}} - @if ($con->com_name) {{ $con->com_name }} @else <i>Perusahaan belum diisi</i> @endif - {{ $con->role_type }}</option>
                            @endforeach
                        </x-select> -->
                    <div class="form-group">
                        <label>Contact<span style="color:red">*</span></label>
                        <select name="contact" wire:model="contact" class="form-control" disabled>
                            <option value="">Select Contact</option>
                            @foreach ($contact_list as $con)
                            <option value="{{$con->id}}">{{$con->name}} - @if ($con->com_name) {{ $con->com_name }} @else <i>Perusahaan belum diisi</i> @endif - {{ $con->role_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (auth()->user()->role->role_type != 'sales')
                    <!-- <x-select name="sales" label="Sales" id="select2-sales" isreq="*" disabled ignore>
                            <option value="">Select Sales</option>
                            @foreach ($sales_list as $sal)
                            <option value="{{$sal->id}}">{{$sal->name}}</option>
                            @endforeach
                        </x-select> -->
                    <div class="form-group">
                        <label>Sales<span style="color:red">*</span></label>
                        <select name="sales" wire:model="sales" class="form-control" disabled>
                            <option value="">Select Sales</option>
                            @foreach ($sales_list as $sal)
                            <option value="{{$sal->id}}">{{$sal->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="form-group">
                        <label>Select Sales <span style="color:red">*</span></label>
                        <select name="sales" class="form-control" disabled>
                            <option value="{{ auth()->user()->id }}">{{ auth()->user()->name }}</option>
                        </select>
                    </div>
                    @endif
                    <!-- <x-text-field type="text" name="lead_type" label="Lead Type" /> -->
                    <x-text-field type="text" name="customer_need" label="Customer Need (optional)" readonly />

                    <div class="form-group">
                        <button class="btn btn-primary pull-right" wire:click="{{$update_mode ? 'update' : 'store'}}">Simpan</button>
                    </div>
                </div>
            </div>
            @else
            <livewire:table.order-lead-table params="{{$route_name}}" />
            @endif

        </div>

        <div id="activity-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Lead Activity
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <table class="display table table-striped table-hover" id="lead-activity">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Description</th>
                                    <th>Result</th>
                                    <th>Attachment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lead_activity as $act)
                                <tr id="key-{{$act->id}}" key="{{$act->id}}">
                                    <td>{{ $act->title }}</td>
                                    <td>{{ date_format($act->start_date, 'd F Y, H:i') }}</td>
                                    <td>{{ date_format($act->end_date, 'd F Y, H:i') }}</td>
                                    <td>{{ $act->description }}</td>
                                    <td>{{ $act->result }}</td>
                                    <td>@if (!empty($act->attachment)) <a target="_blank" href="{{getImage( $act->attachment)}}" style="color:blue">Show File</a> @endif</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="negotiation-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document" style="position: relative;margin: 0 auto;top: 25%;">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        Lead Negotiation
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <table id="basic-datatables2" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lead_negotiation as $neg)
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
        </div>

        {{-- Modal confirm --}}
        <div id="confirm-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi Batal</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin membatalkan data ini.?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" wire:click='cancel' class="btn btn-danger btn-sm"><i class="fa fa-check pr-2"></i>Ya, Batal</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} address</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" wire:model="user_id" id="user_id" />
                        <x-text-field type="text" name="type" label="Type" />
                        <x-textarea type="text" name="alamat" label="Alamat" />

                        <x-select name="provinsi_id" label="Provinsi" handleChange="getKabupaten">
                            <option value="">Select Provinsi</option>
                            @foreach ($provinces as $provinsi)
                            <option value="{{@$provinsi->pid}}">{{@$provinsi->nama}}</option>
                            @endforeach
                        </x-select>

                        <x-select name="kabupaten_id" label="Kota/Kabupaten" handleChange="getKecamatan">
                            <option value="">Select Kota/Kabupaten</option>
                            @foreach ($kabupatens as $kab)
                            <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
                            @endforeach
                        </x-select>

                        <x-select name="kecamatan_id" label="Kecamatan" handleChange="getKelurahan">
                            <option value="">Select Kecamatan</option>
                            @foreach ($kecamatans as $kecamatan)
                            <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
                            @endforeach
                        </x-select>

                        <x-select name="kelurahan_id" label="Kelurahan" handleChange="getKodepos">
                            <option value="">Select Kelurahan</option>
                            @foreach ($kelurahans as $kelurahan)
                            <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
                            @endforeach
                        </x-select>

                        <x-text-field type="text" name="kodepos" label="Kodepos" />
                        <x-text-field type="text" name="telepon" label="Telepon" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="store_address" wire:loading.attr="disabled" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="form-penagihan-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Tambah Penagihan</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="font-weight: bold;">{{@$lead->order_number}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 col-lg-6" style="line-height: 36px;">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>Contact </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{(empty($lead->contactUser->name)?'-':$lead->contactUser->name)}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Company </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{(empty($lead->user->company->name)?'-':$lead->user->company->name)}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Customer Need </td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b><span style="color:#13854E;">{{(empty($lead->customer_need)?'-':$lead->customer_need)}}</b></span></td>
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
                                                    <td> : <b>{{(empty($lead->salesUser->name)?'-':$lead->salesUser->name)}}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Created On</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{ (!empty($lead->created_at)?date('l, d F Y', strtotime($lead->created_at)):'-') }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td>Created By</td>
                                                    <td>&nbsp;</td>
                                                    <td> : <b>{{(empty($lead->createUser->name)?'Created By System':$lead->createUser->name)}}</b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <br><br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <p>Detail Product</p>
                                        <table width="100%">
                                            <thead class="thead-lightss">
                                                <tr>
                                                    <th class="p-0" width="30%">Product *</th>
                                                    <th class="p-0" width="20%">Price</th>
                                                    <th class="p-0" width="9%">Qty *</th>
                                                    <th class="p-0" width="15%">Total Price</th>
                                                    <th class="p-0" width="15%">Final Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $item = 0; $total = 0; @endphp
                                                @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                    <td class="p-0">
                                                        <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                            <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin',
                                                                'leadsales','sales']))?'disabled':''}}>
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

                                                                <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>
                                                            </div> <!-- input-group.// -->
                                                        </div> <!-- input-group.// -->
                                                    </td>
                                                    <td class="p-0">
                                                        <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                    </td>
                                                    <td class="p-0">
                                                        @php $total += $price[$index]; $item += $index; @endphp
                                                        <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control" {{(auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type != 'leadwh' || auth()->user()->role->role_type == 'superadmin')?'readonly':''}}>

                                                    </td>
                                                    </tr>
                                                    @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="uid_lead" wire:model="uid_lead" id="uid_lead" />
                        <div class="row">
                            <div class="col-md-4">
                                <x-text-field type="text" name="account_name" label="Nama Akun" />
                            </div>
                            <div class="col-md-4">
                                <x-text-field type="text" name="account_bank" label="Nama Bank" />
                            </div>
                            <div class="col-md-4">
                                <x-text-field type="text" name="total_transfer" label="Jumlah Transfer" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <x-text-field type="date" name="transfer_date" label="Date" />
                            </div>
                            <div class="col-md-4">
                                <x-input-file file="{{$upload_billing_photo}}" path="{{optional($upload_billing_photo_path)->getClientOriginalName()}}" name="upload_billing_photo" label="Upload Billing Photo" />
                            </div>
                            <div class="col-md-4">
                                <x-input-file file="{{$upload_transfer_photo}}" path="{{optional($upload_transfer_photo)->getClientOriginalName()}}" name="upload_transfer_photo" label="Upload Transfer Photo" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <x-textarea name="notes" label="Notes" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" wire:click="store_penagihan" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>

                    </div>
                </div>
            </div>
        </div>

        <div id="form-penarikan-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Penarikan Data (Grace Periode)</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title" style="font-weight: bold;">Order No : {{@$lead->order_number}}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <p>Product yang Ditarik</p>
                                        <table width="100%">
                                            <thead class="thead-lightss">
                                                <tr>
                                                    <th class="p-0" width="30%">Product *</th>
                                                    <th class="p-0" width="20%">Price</th>
                                                    <th class="p-0" width="9%">Qty *</th>
                                                    <th class="p-0" width="15%">Total Price</th>
                                                    <th class="p-0" width="15%">Final Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $item = 0; $total = 0; @endphp
                                                @for ($index = 0; $index < count($inputs); $index++) <tr>
                                                    <td class="p-0">
                                                        <div class="form-group {{$errors->has('product_id.'.$index) ? 'has-error has-feedback' : '' }} w-100">
                                                            <select name="product_id.{{$index}}" wire:model="product_id.{{$index}}" wire:change="getPrice($event.target.value,{{$index}})" class="form-control" {{(in_array(auth()->user()->role->role_type, ['adminsales', 'leadwh', 'superadmin',
                                                                'leadsales']))?'disabled':''}}>
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

                                                                <button class="btn btn-light btn-xs" type="button"> {{$qty[$index]}} </button>
                                                            </div> <!-- input-group.// -->
                                                        </div> <!-- input-group.// -->
                                                    </td>
                                                    <td class="p-0">
                                                        <x-text-field type="text" name="harga_total.{{$index}}" readonly />
                                                    </td>
                                                    <td class="p-0">
                                                        @php $total += $price[$index]; $item += $index; @endphp
                                                        <input id="price.{{$index}}" value="" name="price.{{$index}}" wire:model="price.{{$index}}" type="text" class="form-control" {{(auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type != 'leadwh' || auth()->user()->role->role_type == 'superadmin')?'readonly':''}}>

                                                    </td>
                                                    </tr>
                                                    @endfor
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="uid_lead" wire:model="uid_lead" id="uid_lead" />
                        <div class="row">
                            <div class="col-md-6">
                                <x-select name="warehouse_id" label="Warehouse" required ignore>
                                    <option value="">Select Warehouse</option>
                                    @foreach ($warehouses as $war)
                                    <option value="{{$war->id}}">{{$war->name}}</option>
                                    @endforeach
                                </x-select>
                            </div>
                            <div class="col-md-6">
                                <x-select name="courier" label="Pic Kurir / Bagian Warehouse" id="select2" isreq="*" ignore>
                                    <option value="">Select Courier</option>
                                    @foreach ($courier_list as $cou)
                                    <option value="{{$cou->id}}">{{$cou->name}}</option>
                                    @endforeach
                                </x-select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">

                        <button type="button" wire:click="store_penarikan" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>

                    </div>
                </div>
            </div>
        </div>

        <div id="approve-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title" id="my-modal-title">Konfirmasi</h5>
                    </div>
                    <div class="modal-body">
                        <p>Apakah anda yakin akan melakukan payment ?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" wire:click='set_closed' class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Ya</button>
                        <button class="btn btn-primary btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Keluar</a>
                    </div>
                </div>
            </div>
        </div>

        <div id="address-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Detail address</h5>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" wire:model="user_id" id="user_id" />
                        <div class="row">
                            <div class="col-md-3">Type</div>
                            <div class="col-md-9">: {{$type}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Address</div>
                            <div class="col-md-9">: {{@$alamat}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Kelurahan</div>
                            <div class="col-md-9">: {{@$kelurahan_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Kecamatan</div>
                            <div class="col-md-9">: {{@$kecamatan_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Kabupaten/Kota</div>
                            <div class="col-md-9">: {{@$kabupaten_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Provinsi</div>
                            <div class="col-md-9">: {{@$provinsi_id}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Kode Pos</div>
                            <div class="col-md-9">: {{@$kode_pos}}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Is Default</div>
                            <div class="col-md-9">: {{($is_default)?'Yes':'No'}}</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')



    <script>
        $(document).ready(function(value) {
            $('#reportrange').daterangepicker({
                startDate: moment().subtract(29, 'days'),
                endDate: moment(),
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }).on('change',e=>{
                // @this.set('reportrange', e.target.value)
                @this.call('applyFilterDate', e.target.value)
            });

            window.livewire.on('loadForm', (data) => {

            });

            window.livewire.on('showModalDetail', (data) => {
                $('#uid_lead').val({{$uid_lead}});
                $('#activity_id').val({{$activity_id}});
                $('#activity-modal').modal('show')
            });

            window.livewire.on('showModalNego', (data) => {
                $('#negotiation-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#activity-modal').modal('hide')
                $('#negotiation-modal').modal('hide')
                $('#form-modal').modal('hide')
                $('#form-penagihan-modal').modal('hide')
                $('#form-penarikan-modal').modal('hide')
                $('#address-modal').modal('hide')
                $('#approve-modal').modal('hide')
            });

            window.livewire.on('showModal', (data) => {
                $('#form-modal').modal('show')
            });

            window.livewire.on('showModalPenagihan', (data) => {
                $('#form-penagihan-modal').modal('show')
            });

            window.livewire.on('showModalPenarikan', (data) => {
                $('#form-penarikan-modal').modal('show')
            });

            window.livewire.on('showModalAddress', (data) => {
                $('#address-modal').modal('show')
            });
        })
    </script>
    @endpush
</div>

<style>
    .fa-tooltip {
        display: inline-block;
        border-radius: 50%;
        box-shadow: 0 0 2px #888;
        padding: 0.3em 0.6em;
        font-size: 10px;
        color: white;
        background: #003e8b;
    }
</style>