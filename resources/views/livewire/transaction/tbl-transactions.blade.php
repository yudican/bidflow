<div class="page-inner">
    <div class="row">

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span>List Data Transaksi</span>
                        </a>
                        <div class="pull-right">
                            <button class="btn btn-success btn-sm" wire:click="export"><i class="fas fa-excel"></i> Export</button>
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <x-text-field type="text" name="reportrange" label="Tanggal" id="reportrange" />
                </div>
            </div>
        </div>
        <div class="col-md-12">

            {{--
            <x-loading /> --}}
            @livewire('table.transaction-table', ['params'=> ['route_name'=> $route_name, 'status' => $status_transaksi,'segment1' => $segment1, 'segment2' => $segment2,'segment' => $segment]])
        </div>

        {{-- Modal form --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Rincian Transaksi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($order)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            ID Transaksi
                            <span>#{{$order->id_transaksi}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Nama Pelanggan
                            <span>{{@$order->user->name}}</span>
                        </li>
                        @if ($order->brand)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Brand
                            <span>{{$order->brand->name}}</span>
                        </li>
                        @endif

                        @if ($order->product && $order->product->category)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Kategori
                            <span>{{$order->product->category->name}}</span>
                        </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Kode Voucher
                            <span>{{ @$order->voucher}}</span>
                        </li>



                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Tanggal Transaksi
                            <span>{{date('l, d F Y H:i', strtotime($order->created_at))}}</span>
                        </li>
                        @if ($order->shippingType)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Metode Pengiriman
                            <span>{{$order->shippingType->shipping_type_name}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Ongkos Kirim
                            <span>Rp. {{number_format($order->shippingType->shipping_price)}}</span>
                        </li>
                        @if ($order->shippingType->shipping_discount > 0)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Diskon Ongkos Kirim
                            <span class="text-green-600">Rp. -{{number_format($order->shippingType->shipping_discount)}}</span>
                        </li>
                        @endif
                        @endif
                        @if ($order->diskon > 0)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Diskon
                            <span>Rp. -{{number_format($order->diskon)}}</span>
                        </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Kode Unik
                            <span>{{@$order->payment_unique_code}}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Total Harga
                            <span>Rp. {{number_format($order->nominal)}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Hubungi Pembeli
                            <a href="http://wa.me/{{@$order->user->telepon}}" target="_blank"><span class="badge badge-success"><i class="fas fa-whatsapp"></i> Hubungi</span></a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Status
                            @switch($order->status)
                            @case(1)
                            <span class="badge badge-warning">Belum Bayar</span>
                            @break
                            @case(2)
                            <span class="badge badge-info">Sudah Upload Bukti Bayar</span>
                            @break
                            @case(3)
                            <span class="badge badge-success">Pembayaran Diterima</span>
                            @break
                            @case(4)
                            <span class="badge badge-danger">Pembayaran Ditolak</span>
                            @break
                            @case(5)
                            <span class="badge badge-danger">Transaksi Dibatalkan</span>
                            @break
                            @case(7)
                            @switch($order->status_delivery)
                            @case(1)
                            <span class="badge badge-info">Waiting Proses Packing</span>
                            @break
                            @case(2)
                            <span class="badge badge-info">Proses Packing</span>
                            @break
                            @case(3)
                            <span class="badge badge-warning">Proses Delivery</span>
                            @break
                            @case(4)
                            <span class="badge badge-success">Delivered</span>
                            @break
                            @case(21)
                            <span class="badge badge-success">Siap Dikirim</span>
                            @break
                            @default
                            @endswitch
                            @break
                            @default

                            @endswitch
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            By User
                            <span class="badge badge-info">
                                @if(!empty($logdata->name))
                                {{ @$logdata->name }}
                                @else
                                -
                                @endif
                            </span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Harga (satuan)</th>
                                    <th>Total Harga</th>
                                </tr>
                                @if (!empty($order->transactionDetail))
                                @foreach ($order->transactionDetail as $det)
                                <tr>
                                    <td>
                                        <span>{{ $det->product->name }}</span> <br>
                                        @if ($det->variant)
                                        <span>Variant: {{$det->variant->name}}</span>
                                        @endif
                                    </td>
                                    <td>{{ $det->qty }}</td>
                                    <td>Rp {{ number_format($det->price,0,',','.') }}</td>
                                    <td>Rp {{ number_format($det->price*$det->qty,0,',','.') }}</td>
                                </tr>
                                @endforeach
                                @endif
                            </table>
                        </li>
                    </div>
                    <div class="modal-footer">
                        {{-- <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</a> --}}

                    </div>
                </div>
            </div>
        </div>

        {{-- Modal form --}}
        <div id="form-modal-payment" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Detail Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($payment)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            ID Transaksi
                            <span>#{{@$payment->transaction->id_transaksi}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Nama Rekening
                            <span>{{@$payment->nama_rekening}}</span>
                        </li>


                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Jumlah Transfer
                            <span>Rp. {{@number_format($payment->jumlah_bayar)}}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Tanggal Transfer
                            <span>{{@date('l, d F Y', strtotime($payment->tanggal_bayar))}}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Ref ID
                            <span>{{@number_format($payment->ref_id)}}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Status
                            @switch(@$payment->status)
                            @case(0)
                            <span class="badge badge-warning">Diproses</span>
                            @break
                            @case(1)
                            <span class="badge badge-success">Diverifikasi</span>
                            @break
                            @case(2)
                            <span class="badge badge-danger">Ditolak</span>
                            @break
                            @default

                            @endswitch
                        </li>
                        @if (@$payment->foto_struk)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Foto
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            <img src="{{getImage(@$payment->foto_struk)}}" alt="" style="height: 200px;">
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            <button class="btn btn-primary btn-sm" wire:click="showPhoto({{@$trans->id}})">Show Image</button>
                        </li>
                        @endif
                        @if (!@$payment->ref_id)
                        <x-text-field type="text" name="ref_id" label="Ref Id" placeholder="Input Ref Id" />
                        @endif
                        @else
                        @if (!@$payment->ref_id)
                        <x-text-field type="text" name="jumlah_bayar" label="Nominal Transfer" placeholder="Nominal Transfer" />
                        <x-text-field type="text" name="bank_dari" label="Bank Transfer" placeholder="Bank Transfer" />
                        <x-text-field type="text" name="nama_rekening" label="Nama Akun Bank" placeholder="Nama Akun Bank" />
                        <x-text-field type="date" name="tanggal_bayar" label="Tanggal Transfer" placeholder="Tanggal Transfer" />
                        <x-text-field type="text" name="ref_id" label="Ref Id" placeholder="Input Ref Id" />
                        @endif
                        @endif

                    </div>
                    <div class="modal-footer">
                        @if (@$payment->status == 0)
                        <button class="btn btn-success btn-sm" wire:click='approvePayment'><i class="fa fa-check pr-2"></i>Terima</button>
                        <button class="btn btn-danger btn-sm" wire:click='declinePayment'><i class="fa fa-times pr-2"></i>Tolak</button>
                        @endif

                        <!--<button class="btn btn-warning btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</button>-->
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal form --}}
        <div id="form-modal-photo" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Bukti Pembayaran</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if (@$payment->foto_struk)
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            Foto
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0">
                            <img src="{{getImage(@$payment->foto_struk)}}" alt="" style="height: 800px;">
                        </li>
                        @endif


                    </div>
                </div>
            </div>
        </div>

        {{-- Modal form --}}
        <div id="form-modal-resi" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">No. Resi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <x-text-field type="text" name="resi" label="Resi" placeholder="Input No. Resi" readonly />
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-sm" wire:click='saveResi'><i class="fa fa-check pr-2"></i>Update Status</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal form --}}
        <div id="form-modal-log" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Log Proccess</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-lightss">
                            <thead class="thead-lightss">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Keterangan</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $key => $log)
                                <tr>
                                    <td>{{$key +1}}</td>
                                    <td>
                                        <span>{{$log->user->name}}</span>
                                        <span class="badge badge-success">{{$log->user->role->role_name}}</span>
                                    </td>
                                    <td>{{$log->keterangan}}</td>
                                    <td>{{$log->created_at}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Timeline --}}
        <div id="timeline-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Informasi Pengiriman</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if ($history_shipping)
                        <ul class="timeline">
                            @foreach ($history_shipping as $item)
                            <li>
                                <a target="_blank" href="https://www.totoprayogo.com/#" class="ml-4">New Web Design</a>
                                <a href="#" class="float-right">{{date('l, d F Y', strtotime($item['date']))}}</a>
                                <p class="ml-6">{{$item['description']}}</p>
                            </li>
                            @endforeach
                        </ul>
                        @else
                        <div style="height: 200px;">
                            <div class="table-row p-1 divide-x divide-gray-100 flex justify-center items-center" style="position: absolute;left: 0;right: 0;height: 200px;" id="row-">
                                <div class="flex flex-col justify-center items-center mt-8">
                                    <img src="{{asset('assets/img/empty.svg')}}" alt="">
                                    <span>Tidak Ada Data</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- Section: Timeline -->
                    </div>
                    <div class="modal-footer">

                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
    <style>
        ul.timeline {
            list-style-type: none;
            position: relative;
        }

        ul.timeline:before {
            content: ' ';
            background: #d4d9df;
            display: inline-block;
            position: absolute;
            left: 29px;
            width: 2px;
            height: 100%;
            z-index: 400;
        }

        ul.timeline>li {
            margin: 20px 0;
            padding-left: 20px;
        }

        ul.timeline>li:before {
            content: ' ';
            background: white;
            display: inline-block;
            position: absolute;
            border-radius: 50%;
            border: 3px solid #22c0e8;
            left: 20px;
            width: 20px;
            height: 20px;
            z-index: 400;
        }
    </style>
    @endpush
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

            
            window.livewire.on('showModal', (data) => {
                $('#form-modal').modal('show')
            });
            var key=0
            window.livewire.on('printInvoice', (url) => {
                window.open(url,'_blank')
            });
            window.livewire.on('downloadFile', (data) => {
                window.open(data?.url,'_blank')
            });
            window.livewire.on('showModalPayment', (data) => {
                $('#form-modal-payment').modal('show')
            });
            window.livewire.on('showModalPhoto', (data) => {
                $('#form-modal-photo').modal('show')
            });
            window.livewire.on('showModalResi', (data) => {
                $('#form-modal-resi').modal('show')
            });
            window.livewire.on('showModalLog', (data) => {
                $('#form-modal-log').modal('show')
            });
            window.livewire.on('timelineModal', (data) => {
                $('#timeline-modal').modal(data)
            });
            window.livewire.on('getSelected', (data) => {
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
            });
            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-modal').modal('hide')
                $('#form-modal-payment').modal('hide')
                $('#form-modal-resi').modal('hide')
                $('#form-modal-log').modal('hide')
                $('#timeline-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>