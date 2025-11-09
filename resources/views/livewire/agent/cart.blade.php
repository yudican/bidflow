<div class="page-inner">
    <x-loading />
    @if (count($carts) > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>List Cart</span>
                        </a>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-check">
                        <label class="form-check-label">
                            @if ($cart_selected_count == $cart_count)
                            <input class="form-check-input" type="checkbox" wire:click="selectAll('unselect')" value="0" checked>
                            @else
                            <input class="form-check-input" type="checkbox" wire:click="selectAll('select')" value="1">
                            @endif
                            <span class="form-check-sign">Pilih Semua</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-7 col-12">
            <div class="card">
                <div class="card-body">

                    @foreach ($carts as $cart)
                    <article class="mb-3 border-b-2 pb-3">
                        <div class="d-flex justify-content-between align-items-center flex-row">
                            <div>
                                <a href="#" class="itemside align-items-center">
                                    <div class="form-check p-0 m-0">
                                        <label class="form-check-label">
                                            @if ($cart->selected > 0)
                                            <input class="form-check-input" type="checkbox" wire:click="selectCart({{$cart->id}})" value="0" checked>
                                            @else
                                            <input class="form-check-input" type="checkbox" wire:click="selectCart({{$cart->id}})" value="1">
                                            @endif
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                    <div class="aside"> <img src="{{getImage($cart->product->image)}}" height="72" width="72" class="img-thumbnail img-sm"> </div>
                                    <div class="info">
                                        <span class="title" style="font-size: 12px;">{{$cart->product->name}} </span> <span class="text-muted">{{$cart->product->weight * $cart->qty}} gr</span> <br>
                                        <strong class="price" style="font-size: 12px;"> Rp. {{number_format($cart->product->price['final_price'])}} </strong>
                                    </div>
                                </a>
                            </div> <!-- col.// -->
                            <div class="text-right">
                                <img src="{{asset('assets/img/freeongkir.png')}}" style="height: 20px;" class="float-right" /><br>
                                <div class="d-flex  align-items-center flex-row mt-2">
                                    <div class="input-group input-spinner mr-3">
                                        <button class="btn btn-light btn-xs" type="button" wire:click="min_qty({{$cart->id}})"> <i class="fas fa-minus"></i> </button>

                                        <button class="btn btn-light btn-xs" type="button"> {{$cart->qty}} </button>
                                        <button class="btn btn-light btn-xs" type="button" wire:click="add_qty({{$cart->id}})"> <i class="fas fa-plus"></i> </button>
                                    </div> <!-- input-group.// -->
                                    <button class="btn btn-light btn-xs" wire:click="delete({{$cart->id}})"> <i class="fas fa-trash"></i> </button>
                                </div>
                            </div>
                        </div> <!-- row.// -->
                    </article>
                    @endforeach
                </div>
            </div>
            @if (auth()->user()->role->role_type == 'superadmin' || auth()->user()->role->role_type == 'admin')
            <x-select name="user_id" label="Agent">
                <option value="">Select Agent</option>
                @foreach ($agents as $agent)
                <option value="{{$agent->id}}">{{$agent->name}}</option>
                @endforeach
            </x-select>
            @endif
        </div>
        <div class="col-lg-4 col-md-4 col-sm-5 col-12">
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size: 16pt;font-weight: 500;margin-bottom: 12px;border-bottom-width: 1px; padding-bottom: 10px;">
                        Informasi Pengiriman
                        <h3>
                            @if ($shippingMethod == 1)
                            <h3 style="font-size: 12pt;font-weight: 400;">Kirim ke Alamat<h3>
                                    @else
                                    <h3 style="font-size: 12pt;font-weight: 400;">Ambil Sendiri<h3>
                                            @endif
                                            {{-- <div style="border: 1px solid #e5e5e5;padding: 18px;border-radius: 8px;">
                                                <button class="btn d-flex flex-row justify-content-between align-items-center" style="border: 1px solid #e0dbdb;color: #0478ae;border-radius: 8px;width: 100%;text-align: left;">
                                                    <i class="fas fa-truck"></i>
                                                    @if ($shippingMethod == 1)
                                                    <span>Kirim ke Alamat</span>
                                                    @else
                                                    <span>Ambil Sendiri</span>
                                                    @endif


                                                    <span></span>
                                                </button>
                                            </div> --}}
                                            <br>
                                            @if ($selectedAddress)
                                            <div class="row bg-alamat add-alamat">
                                                <div class="col-md-8">
                                                    <p style="font-size: 11px;">Nama Penerima</p>
                                                    <p style="font-size: 13px;">{{$selectedAddress->nama}}</p>
                                                </div>
                                                <div class="col-md-4">
                                                    <center>
                                                        <a href="#" class="btn btn-info btn-border btn-round btn-sm mr-2">
                                                            {{$selectedAddress->type}}
                                                        </a>
                                                    </center>
                                                </div>
                                                <div class="col-md-12">
                                                    <br>
                                                    <p style="font-size: 12px;">+{{$selectedAddress->telepon}}</p>
                                                    <p style="font-size: 12px;">{{$selectedAddress->alamat_detail}}</p>
                                                </div>

                                                <div class="col-md-12"><br>
                                                    <button class="btn btn-addr btn-sm" wire:click="pilihPengiriman()">Ubah Alamat</button>
                                                </div>
                                            </div>
                                            @else
                                            @if ($shippingMethod == 2)
                                            <div class="row bg-alamat ambil-sendiri">
                                                <div class="col-md-12">
                                                    <p style="font-size: 11px;">Alamat Gudang</p>
                                                    <p>{{auth()->user()->brand->name}}</p>
                                                    <br>
                                                    <p>+{{auth()->user()->brand->phone}}</p>
                                                    <p>{{auth()->user()->brand->alamat}}</p>

                                                </div>
                                            </div>
                                            @else
                                            {{-- <button class="btn btn-primary w-100 mb-3" wire:click="pilihPengiriman()">Pilih Alamat</button> --}}
                                            <div class="bg-tambah-alamat-pengiriman  " wire:click="pilihPengiriman()" style="cursor:pointer; color:#886200">
                                                <i class="fas fa-warehouse mr-4"></i>
                                                Pilih Alamat Pengiriman
                                            </div>
                                            @endif
                                            @endif
                                            <br>
                                            {{-- pengiriman dari --}}
                                            <h3 style="font-size: 12pt;font-weight: 400;margin-bottom: 10px;">Dikirim Dari<h3>
                                                    @if ($selectedWarehouse)
                                                    <div class="row bg-warehouse add-alamat my-2">
                                                        <div class="col-md-8">
                                                            <p style="font-size: 11px;">Flimty Warehouse</p>
                                                            <p style="font-size: 13px;">{{$selectedWarehouse->name}}</p>
                                                        </div>
                                                        <div class="col-md-4">

                                                        </div>
                                                        <div class="col-md-12">
                                                            <br>
                                                            <p style="font-size: 12px;">{{$selectedWarehouse->alamat}}</p>
                                                            <button class="btn btn-warehouse btn-sm mt-2" wire:click="pilihGudangPengiriman">Ubah Alamat</button>
                                                        </div>
                                                    </div>
                                                    @else
                                                    {{-- <button class="btn btn-addr btn-sm my-2" wire:click="pilihGudangPengiriman">Pilih Alamat Gudang</button> --}}
                                                    <div style="border: 1px solid #e5e5e5;padding: 18px;border-radius: 8px; margin-bottom: 10px;" wire:click="pilihGudangPengiriman">
                                                        <button class="btn d-flex flex-row justify-content-between align-items-center" style="border: 1px solid #e0dbdb;color: #7C9B3A;border-radius: 8px;width: 100%;text-align: left;">
                                                            <span><i class="fas fa-warehouse"></i>
                                                                <span class="ml-2">Pilih Gudang Pengiriman</span></span>

                                                            <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                    @endif

                                                    @if ($shippingInfo)
                                                    <div style="border: 1px solid #e5e5e5;padding: 18px;border-radius: 8px;" wire:click="toggleModalShipping">
                                                        <button class="btn d-flex flex-row justify-content-between align-items-center" style="border: 1px solid #e0dbdb;color: #0478ae;border-radius: 8px;width: 100%;text-align: left;">
                                                            <div class="d-flex flex-row justify-content-start align-items-center">
                                                                <img src="{{$shippingInfo['shipping_logo']}}" alt="" style="height: 20px;">
                                                                <span class="ml-2">{{$shippingInfo['shipping_type_name']}}</span>
                                                            </div>

                                                            <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                    @else
                                                    <div style="border: 1px solid #e5e5e5;padding: 18px;border-radius: 8px;" wire:click="toggleModalShipping">
                                                        <button class="btn d-flex flex-row justify-content-between align-items-center" style="border: 1px solid #e0dbdb;color: #0478ae;border-radius: 8px;width: 100%;text-align: left;">
                                                            <span><i class="fas fa-truck"></i>
                                                                <span class="ml-2">Pilih Kurir Pengiriman</span></span>

                                                            <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                    @endif

                                                    <br>
                                                    <div class="bg-voucher" wire:click="addVoucher()" style="cursor:pointer"> {{ ($selectedVoucher)?$voucher:'% Klik untuk Gunakan Voucher' }}</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h3 style="font-size: 14pt;font-weight: 600;margin-bottom: 10px;">Ringkasan Pembelian<h3>
                            <table style="font-size:12px" width="100%">
                                @if ($shippingInfo)
                                <tr>
                                    <td width="45%" height="40px">Pengiriman</td>
                                    <td align="right">{{$shippingInfo['shipping_type_name']}} ({{$shippingInfo['shipping_duration']}})</td>
                                </tr>
                                <tr height="40px" style="font-size: 14px;font-weight: 600;">
                                    <td>Biaya Pengiriman</td>
                                    <td class="text-green-600" align="right"><b>Rp {{number_format(getShippingPrice($shippingInfo))}}</b> </td>
                                </tr>
                                @else
                                <tr>
                                    <td width="45%" height="40px">Pengiriman</td>
                                    <td align="right">Belum Pilih Kurir </td>
                                </tr>
                                @endif
                                <tr>
                                    <td>Total ({{getTotalQty($carts)}} Barang)</td>
                                    <td align="right">Rp {{number_format(getSubtotal($carts))}} </td>
                                </tr>
                                @if ($selectedVoucher)
                                <tr height="40px" style="font-size: 14px;font-weight: 600;">
                                    <td>Voucher Diskon (<span class="text-danger">{{$selectedVoucher['voucher_code']}}</span>)</td>
                                    <td align="right"><s>Rp {{number_format($selectedVoucher['amount_discount'])}}</s> </td>
                                </tr>
                                @endif
                                @if (isset($shippingInfo['shipping_discount']) && $shippingInfo['shipping_discount'] > 0)
                                <tr height="40px" style="font-size: 14px;font-weight: 600;">
                                    <td>Diskon Pengiriman</td>
                                    @if (getShippingDiscount($shippingInfo) == 0)
                                    <td align="right"><s>Gartis Ongkir</s> </td>
                                    @else
                                    <td align="right"><s>Rp {{number_format(getShippingDiscount($shippingInfo))}}</s> </td>
                                    @endif
                                </tr>
                                @endif

                                @if ($selectedVoucher)
                                <tr>
                                    <td height="40px" style="font-size: 14px;font-weight: 600;">Total Harga</td>
                                    <td align="right" style="font-size: 14px;font-weight: 600;color: red;">Rp {{number_format(getSubtotal($carts) + $shippingInfo['shipping_price'] - getShippingDiscount($shippingInfo) - intval($selectedVoucher['amount_discount']))}} </td>
                                </tr>
                                @elseif($shippingInfo)
                                <tr>
                                    <td height="40px" style="font-size: 14px;font-weight: 600;">Total Harga</td>
                                    <td align="right" style="font-size: 14px;font-weight: 600;color: red;">Rp {{number_format(getSubtotal($carts) + $shippingInfo['shipping_price'] - getShippingDiscount($shippingInfo))}} </td>
                                </tr>
                                @else<tr>
                                    <td height="40px" style="font-size: 14px;font-weight: 600;">Total Harga</td>
                                    <td align="right" style="font-size: 14px;font-weight: 600;color: red;">Rp {{number_format(getSubtotal($carts))}} </td>
                                </tr>
                                @endif

                                <tr>

                                    <td colspan="2">
                                        <button class="btn btn-primary" wire:click="addPayment()" style="width:100%" @if ($cart_selected_count < 1) disabled @endif>Bayar ({{$cart_selected_count}})</button>
                                    </td>
                                </tr>
                            </table>
                </div>
            </div>
        </div>

        <!-- Modal Pengiriman -->
        <div id="form-pengiriman" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Pilih Alamat Pengiriman</h5>
                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i></button>
                    </div>
                    <div class="modal-body" style="background: #FFF5E4;color: #886200;">
                        <i class="fas fa-exclamation-circle"></i> &emsp;Silahkan pilih salah satu metode pengiriman
                    </div>
                    <div class="modal-body">
                        {{-- <button class="btn general-btn btn-alamat" wire:click="selectAddress(1)"><i class="fas fa-check-circle"></i> Kirim Ke Alamat</button>
                        <button class="btn general-btn btn-cod" wire:click="selectAddress(2)"><i class="fas fa-check-circle"></i> Ambil Sendiri</button>
                        <br><br> --}}
                        @if ($shippingMethod == 1)
                        @foreach (auth()->user()->addressUsers as $item)
                        <div class="row bg-alamat add-alamat mb-2">
                            <div class="col-md-10">
                                <div class="mb-2">
                                    <p style="font-size: 11px;">Nama Penerima</p>
                                    <p>{{$item->nama}}</p>
                                    <br>
                                    <p>+{{$item->telepon}}</p>
                                    <p>{{$item->alamat_detail}}</p>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <center>
                                    <a href="#" class="btn btn-info btn-border btn-round btn-sm mr-2">
                                        {{$item->type}}
                                    </a>
                                    <br><br>
                                    @if ($tempSelectedAddress)
                                    <label class="form-radio-label">
                                        <input class="form-radio-input" type="radio" name="optionsRadios" value="" @if ($item->id == $tempSelectedAddress->id) checked @endif wire:click="selectedAddress({{$item->id}},true)">

                                        <span class="form-radio-sign"></span>
                                    </label>
                                    @else
                                    <label class="form-radio-label">
                                        <input class="form-radio-input" type="radio" name="optionsRadios" value="" wire:click="selectedAddress({{$item->id}},true)">
                                        <span class="form-radio-sign"></span>
                                    </label>
                                    @endif
                                </center>
                            </div>



                            <div class="col-md-12">
                                <button class="btn btn-addr" wire:click="updateAlamat({{$item->id}})">Ubah Alamat</button>
                            </div>
                        </div>
                        @endforeach
                        <div class="bg-voucher add-alamat mt-2" wire:click="addAlamat()" style="cursor:pointer"> Klik untuk menambahkan alamat</div>
                        @else
                        <div class="row bg-alamat ambil-sendiri">
                            <div class="col-md-12">
                                <p style="font-size: 11px;">Alamat Gudang</p>
                                <p>{{auth()->user()->brand->name}}</p>
                                <br>
                                <p>+{{auth()->user()->brand->phone}}</p>
                                <p>{{auth()->user()->brand->alamat}}</p>

                            </div>
                        </div>
                        @endif


                    </div>
                    <div class="modal-footer">
                        @if ($tempSelectedAddress)
                        <button class="btn btn-success btn-sm" wire:click="selectedAddress({{$tempSelectedAddress->id}})"><i class="fa fa-check pr-2"></i>Gunakan Alamat</button>
                        @endif
                        {{-- <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i></button> --}}
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Alamat -->
        <div id="form-alamat" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Masukkan Alamat Pengiriman</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body" style="background: #FFF5E4;color: #886200;">
                        <i class="fas fa-exclamation-circle"></i> &emsp;Silahkan lengkapi alamat pengiriman beserta informasi penerima
                    </div>
                    <div class="modal-body bd-alamat">
                        @livewire('components.address-user', ['update_mode' => $updateAlamatMode, 'nama' => auth()->user()->name, 'telepon' => auth()->user()->telepon])
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary btn-sm" wire:click="$emit('saveAddress')"><i class="fa fa-check pr-2"></i>Simpan Alamat</button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Voucher -->
        <div id="form-voucher" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-sm" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Masukkan Voucher</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <x-text-field type="text" name="voucher" label="Kode Voucher *" placeholder="Contoh : ASF45TD" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-sm" wire:click="useVoucher('{{$voucher}}')"><i class="fa fa-check pr-2"></i>Gunakan Voucher</button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Gudang -->
        <div id="form-gudang" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog " permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Pilih Gudang</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        @foreach ($warehouses as $warehouse)
                        <div class="row bg-alamat add-alamat mb-3">
                            <div class="col-md-8">
                                <p style="font-size: 11px;">Flimty Warehouse</p>
                                <p style="font-size: 13px;">{{$warehouse->name}}</p>
                            </div>
                            <div class="col-md-4">

                            </div>
                            <div class="col-md-12">
                                <br>
                                <p style="font-size: 12px;">{{$warehouse->alamat}}</p>
                                <button class="btn btn-addr btn-sm mt-2" wire:click="applyGudang('{{$warehouse->id}}')">Pilih Alamat</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Payment -->
        <div id="form-payment" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog " permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Metode Pembayaran</h5>
                        <button type="button" class="close" wire:click='_reset' data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background: #FFF5E4;color: #886200;">
                        <i class="fas fa-exclamation-circle"></i> &emsp;Silahkan pilih salah satu metode pembayaran
                    </div>
                    <div class="modal-body">
                        <div style="border: 1px solid #e5e5e5;padding: 18px;border-radius: 8px;">
                            <p style="font-size:12px">Total Tagihan</p>
                            @if ($selectedVoucher)
                            <p style="color:red">Rp {{number_format(getSubtotal($carts) + $shippingInfo['shipping_price'] - getShippingDiscount($shippingInfo) - $selectedVoucher['amount_discount'])}}</p>
                            @elseif($shippingInfo)
                            <p style="color:red">Rp {{number_format(getSubtotal($carts) + $shippingInfo['shipping_price'] - getShippingDiscount($shippingInfo))}}</p>
                            @else
                            <p style="color:red">Rp {{number_format(getSubtotal($carts))}}</p>
                            @endif

                        </div>
                        <br>

                        @foreach ($payment_methods as $item)
                        <div class="mt-2">
                            <p>{{$item->nama_bank}}</p>
                            <ul class="list-group">
                                @foreach ($item->children as $children)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <img src="{{getImage($children->logo_bank)}}" style="float:left;height:20px;width:40px;object-fit:contain;">
                                        <span>{{$children->nama_bank}}</span>
                                    </div>
                                    @if ($selectedPayment)
                                    <label class="form-radio-label">
                                        <input class="form-radio-input" type="radio" name="optionsRadios" value="" @if ($children->id == $selectedPayment->id) checked @endif wire:click="selectPayment({{$children->id}})">

                                        <span class="form-radio-sign"></span>
                                    </label>
                                    @else
                                    <label class="form-radio-label">
                                        <input class="form-radio-input" type="radio" name="optionsRadios" value="" wire:click="selectPayment({{$children->id}})">
                                        <span class="form-radio-sign"></span>
                                    </label>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                        <br><br>
                        <button class="btn btn-primary w-100" wire:click="$emit('closeModalPayment')" @if (!$selectedPayment)disabled @endif>Bayar Sekarang</button>


                    </div>
                    <div class="modal-footer">
                        <!-- <button class="btn btn-success btn-sm" wire:click='saveResi'><i class="fa fa-check pr-2"></i>Pilih Pembayaran</button> -->

                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Shipping Info --}}
        <div id="modal-kurir" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog " permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Metode Pengiriman</h5>
                        <button type="button" class="close" wire:click='_reset' data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="background: #FFF5E4;color: #886200;">
                        <i class="fas fa-exclamation-circle"></i> &emsp;Silahkan pilih salah satu metode pengiriman
                    </div>
                    <div class="modal-body">
                        @foreach ($shippingServices as $item)
                        @if (count($shippingLists) > 0 && isset($shippingLists[$item]) && count($shippingLists[$item]) > 0)
                        <div class="mt-2">
                            <p class="capitalize">{{str_replace('_',' ',$item);}}</p>
                            <ul class="list-group">
                                @foreach ($shippingLists[$item] as $key => $children)
                                <li wire:click="selectKurir({{$key}},'{{$item}}')" class="list-group-item d-flex cursor-pointer justify-content-between align-items-center">
                                    <div>
                                        <img src="{{$children['shipping_logo']}}" style="float:left;height:20px;width:40px;object-fit:contain;">
                                        {{-- dany --}}
                                        <span class="ml-2">{{$children['shipping_type_name']}}</span>
                                        <span class="ml-2">(Rp, {{number_format($children['shipping_price'])}})</span>

                                    </div>
                                    @if ($selectedKurir)
                                    <label class="form-radio-label">
                                        <input class="form-radio-input" type="radio" name="optionsRadios" value="" @if ($key==$selectedKurir) checked @endif wire:click="selectKurir({{$key}},'{{$item}}')">

                                        <span class="form-radio-sign"></span>
                                    </label>
                                    @else
                                    <label class="form-radio-label">
                                        {{-- disable radio button --}}
                                        {{-- <input class="form-radio-input" type="radio" name="optionsRadios" value="" wire:click="selectKurir({{$key}},'{{$item}}')">
                                        <span class="form-radio-sign"></span> --}}
                                    </label>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <!-- <button class="btn btn-success btn-sm" wire:click='saveResi'><i class="fa fa-check pr-2"></i>Pilih Pembayaran</button> -->

                    </div>
                </div>
            </div>
        </div>

        <!-- Modal List Payment -->
        <div id="form-list-payment" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog " permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Metode Pembayaran</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <p>Virtual Number</p>
                        <ul>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px"> BCA Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">Mandiri Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">BRI Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">BNI Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">Danamon Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">BCA Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">Mandiri Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">BRI Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">BNI Virtual Account</li>
                            <li class="bnk"><img src="{{asset('assets/img/bca.png')}}" style="float:left;height:20px">Danamon Virtual Account</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @push('styles')
        <!-- Custom css -->
        <link href="{{asset('css/ui.css?v=2.0')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('css/responsive.css?v=2.0')}}" rel="stylesheet" type="text/css" />
        <style>
            .bnk {
                border-bottom: 1px solid #e6e5e5;
                padding: 10px;
            }

            .btn-addr {
                background: #BE8900;
                color: white;
                width: 100%;
            }

            .btn-warehouse {
                background: #7C9B3A;
                color: white;
                width: 100%;
            }

            .bg-alamat {
                background: #fef4e4;
                margin: 0px;
                padding: 10px;
                border: 2px solid #FFC120;
                border-radius: 5px;
            }

            .bg-warehouse {
                background: #FAFFF0;
                margin: 0px;
                padding: 10px;
                border: 2px solid #7C9B3A;
                border-radius: 5px;
            }

            .min-btn {
                width: 25px;
                height: 25px;
                border: none;
                border-radius: 100px;
                outline: none;
                background: #beb4b6;
                color: white;
                cursor: pointer;
                box-shadow: 0 5px 10px rgb(0 0 0 / 15%);
                margin-right: 10px;
                font-weight: bold;
                font-size: 17px;
                padding: 0px 9px;
            }

            .min-btn:hover {
                background: #eb2f06;
            }

            .add-btn {
                width: 25px;
                height: 25px;
                border: none;
                border-radius: 100px;
                outline: none;
                background: #003e8b;
                color: white;
                cursor: pointer;
                box-shadow: 0 5px 10px rgb(0 0 0 / 15%);
                margin-right: 10px;
                font-weight: bold;
                font-size: 17px;
                padding: 0px 7px;
            }

            .add-btn:hover {
                background: #eb2f06;
            }

            .general-btn {
                border: 1px solid #e0dbdb;
                border-radius: 8px;
                width: 49%;
                text-align: left;
            }

            .bg-voucher {
                border: 3px dashed #e5e5e5;
                padding: 18px;
                text-align: center;
                border-radius: 8px;
                background: #ECF5D9;
            }

            .bg-tambah-alamat-pengiriman {
                border: 3px dashed #BE8900;
                padding: 18px;
                text-align: center;
                border-radius: 8px;
                background: #FFF5E4;
            }



            /* Important part */
            .modal-dialog {
                overflow-y: initial !important
            }

            .bd-alamat {
                height: 65vh;
                overflow-y: auto;
            }
        </style>
        @endpush

        @push('scripts')
        <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>
        <script src="{{asset('assets/js/plugin/datatables/datatables.min.js')}}"></script>
        <script>
            $(document).ready(function(value) {
                $('.btn-alamat').on('click', function(){
                    $('.add-alamat').show();
                    $('.ambil-sendiri').hide();
                });
                $('.btn-cod').on('click', function(){
                    $('.ambil-sendiri').show();
                    $('.add-alamat').hide();
                });
                

                window.livewire.on('loadForm', (data) => {
                
                $('#basic-datatables').DataTable({});
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
                });
            
            window.livewire.on('showModalPengiriman', (data) => {
                $('#form-pengiriman').modal('show')
            });

            window.livewire.on('showModalAlamat', (data) => {
                $('#form-alamat').modal('show')
                $('#form-pengiriman').modal('hide')
            });

            window.livewire.on('showModalVoucher', (data) => {
                $('#form-voucher').modal('show')
            });

            window.livewire.on('showModalPayment', (data) => {
                $('#form-payment').modal('show')
            });
            window.livewire.on('closeModalPayment', (data) => {
                window.scrollTo(0, 0);
                $('#form-payment').modal('hide')
                @this.call('checkoutProduct');
            });

            window.livewire.on('showModalPilihPembayaran', (data) => {
                $('#form-list-payment').modal('show')
                $('#form-payment').modal('hide')
            });
            
            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-pengiriman').modal('hide')
                $('#form-alamat').modal('hide')
                $('#form-voucher').modal('hide')
            });
            window.livewire.on('closeModalAlamat', (data) => {
                $('#form-alamat').modal('hide')
            });
            window.livewire.on('showModalShipping', (data) => {
                $('#modal-kurir').modal(data)
            });
            window.livewire.on('modalGudang', (data) => {
                $('#form-gudang').modal(data)
            });
        })
        </script>

        @endpush
    </div>
    @else
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left"> </i> List Cart</span>
                        </a>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body d-flex justify-content-center align-items-center">
                    <img src="{{asset('assets/img/No Tags.svg')}}" alt="">
                </div>
            </div>
        </div>
    </div>
    @endif

</div>