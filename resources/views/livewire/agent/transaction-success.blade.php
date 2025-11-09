<div class="page-inner">
    @if ($transaction)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span> <i class="fas fa-arrow-left"></i> Detail Transaksi</span>
                        </a>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mx-auto">
                            <div class="d-flex flex-column justify-content-center align-items-center">
                                <img src="{{asset('assets/img/OnlineShopeGirl.svg')}}" alt="">
                                <h2 class="text-center text-bold"><b>Transaksi Berhasil</b></h2>
                                <p class="text-center">Terimakasih sudah berbelanja di {{auth()->user()->brand->name}}
                                    Segera lakukan pembayaran pesananmu agar dapat kami proses ke langkah selanjutnya. </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <table>
                                <tr>
                                    <td width="50%">ID Transaksi</td>
                                    <td>: {{$transaction->id_transaksi}}</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    @if ($transaction->status == 1)
                                    <td>: Menunggu Pembayaran</td>
                                    @elseif(in_array($transaction->status,[2]))
                                    <td>: Pengecekan Pembayaran</td>
                                    @elseif(in_array($transaction->status,[4,5,6]))
                                    <td>: Transaksi Dibatalkan</td>
                                    @else
                                    <td>: Pembayaran Diterima</td>
                                    @endif

                                </tr>
                                <tr>
                                    <td>Batas Pembayaran</td>
                                    <td>: {{Carbon\Carbon::parse($transaction->created_at)->addDays(1)->diffForHumans()}}</td>
                                </tr>
                            </table>
                            <br>
                            <div class="card">
                                <div class="card-header">
                                    {{$transaction->paymentMethod->nama_bank}}
                                    <img src="{{getImage($transaction->paymentMethod->logo_bank)}}" style="float:right;height:20px">
                                </div>
                                <div class="card-body">
                                    @if (in_array($transaction->paymentMethod->payment_channel, ['bank_transfer','echannel']) && $transaction->paymentMethod->payment_type == 'Otomatis')

                                    <div>
                                        @if ($transaction->paymentMethod->payment_channel == 'echannel')

                                        <p style="font-size:10px">Kode Perusahaan</p>
                                        <p style="font-size:14px;font-weight:600">{{$transaction->paymentMethod->payment_va_number}}</p>
                                        <br>
                                        @endif
                                        <p style="font-size:10px">Nomor Virtual Account</p>
                                        <p style="font-size:14px;font-weight:600">{{$transaction->payment_va_number}}</p>
                                    </div>

                                    @elseif (in_array($transaction->paymentMethod->payment_channel,['gopay','qris']))
                                    <div>
                                        <img src="{{$transaction->payment_qr_url}}" alt="">
                                        <p class="text-center">Scan kode QR diatas untuk melakukan pembayaran</p>
                                    </div>
                                    @else
                                    <div>
                                        <p style="font-size:10px">Nama Rekening Bank</p>
                                        <p style="font-size:14px;font-weight:600">{{$transaction->paymentMethod->nama_rekening_bank}}</p> <br>
                                        <p style="font-size:10px">Nomor Rekening Bank</p>
                                        <p style="font-size:14px;font-weight:600">{{$transaction->paymentMethod->nomor_rekening_bank}}</p>
                                    </div>
                                    @endif

                                    <br>
                                    <p style="font-size:10px">Total Pembayaran</p>
                                    <p style="font-size:14px;font-weight:600;color:red">Rp {{number_format($transaction->nominal)}}</p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    Rincian Produk
                                </div>
                                <div class="card-body">
                                    @foreach ($transaction->transactionDetail as $detail)
                                    <article class="mb-3 pb-3">
                                        <div class="d-flex justify-content-start align-items-center flex-row">
                                            <div class="aside"> <img src="{{getImage($detail->product->image)}}" height="85" width="85" class="img-thumbnail img-sm"> </div>
                                            <div class="info ml-2">
                                                <span class="title" style="font-size: 12px;">{{$detail->product->name}} </span> <br>
                                                @if ($detail->variant)
                                                <span class="title" style="font-size: 12px;">Variant: {{$detail->variant->name}} </span>
                                                @endif
                                                <br>
                                                <span class="text-muted" style="font-size: 10px;">{{$detail->product->weight}} gr</span> <br>
                                                <strong class="price" style="font-size: 12px;color:red;"> Rp. {{number_format($detail->product->price['final_price'])}} x {{$detail->qty}} </strong>
                                            </div>
                                        </div> <!-- row.// -->
                                    </article>
                                    @endforeach
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <span>Metode Pengiriman - {{$transaction->shippingType->shipping_type_name}}</span>
                                    <img src="{{$transaction->shippingType->shipping_logo}}" style="float:right;height:20px">
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-row justify-between">
                                        <div>
                                            <p style="font-size:10px">Nama Penerima</p>
                                            <p style="font-size:14px;font-weight:600">{{$transaction->addressUser->name}}</p>
                                        </div>
                                        <div>
                                            <span class="badge badge-success">{{$transaction->addressUser->type}}</span>
                                        </div>
                                    </div>
                                    <p style="font-size:14px" class="pt-2">{{$transaction->addressUser->telepon}}</p>
                                    <p style="font-size:14px">{{$transaction->addressUser->alamat_detail}}</p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body row">
                                    <div class="col-6">
                                        <a href="{{route('invoice.print', $transaction->id)}}" target="_blank"><button class="btn btn-outline-primary w-100">Lihat Invoice</button></a>
                                    </div>
                                    @if ($transaction->paymentMethod->payment_type == 'Manual')

                                    <div class="col-6">
                                        @if ($hasConfirm)
                                        <button class="btn btn-primary w-100" disabled>Upload Bukti Bayar</button>
                                        @else
                                        <button class="btn btn-primary w-100" wire:click="confirmPayment">Upload Bukti Bayar</button>
                                        @endif

                                    </div>
                                    @else
                                    <div class="col-6">
                                        <a href="{{route('product-agent')}}">
                                            <button class="btn btn-outline-primary w-100">Transaksi Lainnya</button>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @if (is_array($transaction->payment_proof) && count($transaction->payment_proof) > 0)
                            <p><b>Cara Pembayaran</b></p>
                            @endif
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade active show" id="v-pills-home-icons" role="tabpanel" aria-labelledby="v-pills-home-tab-icons">
                                    <div class="accordion accordion-primary">
                                        @foreach (paymentguide($transaction->paymentMethod->payment_code) as $item)
                                        <div class="card mb-0">
                                            <div class="card-header" id="headingFour-{{$item['id']}}" data-toggle="collapse" data-target="#collapseFour-{{$item['id']}}" aria-controls="collapseFour-{{$item['id']}}" role="button">
                                                {{-- <div class="span-icon">
                                                    <div class="flaticon-box-1"></div>
                                                </div> --}}
                                                <div class="span-title">
                                                    {{$item['name']}}
                                                </div>
                                                <div class="span-mode"></div>
                                            </div>

                                            <div id="collapseFour-{{$item['id']}}" class="collapse" aria-labelledby="headingFour-{{$item['id']}}" data-parent="#accordion" role="button">
                                                <div class="card-body">
                                                    <ul>
                                                        @foreach ($item['details'] as $detail)
                                                        <li>{{$detail['id']}}. {!!$detail['title']!!}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="confirm-payment-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">Upload Bukti Bayar</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <x-text-field type="text" name="nama_rekening" label="Nama Rekening" placeholder="Contoh : Yudi Candra" />
                        <x-select name="bank_tujuan" label="Bank Tujuan">
                            <option value="">Pilih Bank Tujuan</option>
                            <option value="BCA">BCA</option>
                            <option value="MANDIRI">MANDIRI</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                        </x-select>
                        <x-select name="bank_dari" label="Bank Asal">
                            <option value="">Pilih Bank Tujuan</option>
                            <option value="BCA">BCA</option>
                            <option value="MANDIRI">MANDIRI</option>
                            <option value="BRI">BRI</option>
                            <option value="BNI">BNI</option>
                        </x-select>
                        <x-text-field type="number" name="jumlah_bayar" label="Jumlah Bayar" placeholder="Contoh : 100000" />
                        <x-input-photo foto="{{$foto_struk}}" path="{{optional($foto_struk_path)->temporaryUrl()}}" name="foto_struk_path" label="Foto Struk" />
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-sm" wire:click="saveConfirmPayment"><i class="fa fa-check pr-2"></i>Upload Bukti Bayar</button>

                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script src="{{asset('assets/js/plugin/summernote/summernote-bs4.min.js')}}"></script>
        <script src="{{asset('assets/js/plugin/datatables/datatables.min.js')}}"></script>
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

            window.livewire.on('closeModal', (data) => {
                $('#confirm-payment-modal').modal('hide')
            });

            window.livewire.on('showModalConfirm', (data) => {
                $('#confirm-payment-modal').modal('show')
            });
        })
        </script>

        @endpush
    </div>
    @endif
</div>