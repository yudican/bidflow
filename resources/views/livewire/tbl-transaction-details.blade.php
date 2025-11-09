<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3"></i>Inventory</span>
                        </a>
                        <div class="pull-right">
                            @if ($form_active)
                            <button class="btn btn-danger btn-sm" wire:click="toggleForm(false)"><i class="fas fa-times"></i> Cancel</button>

                            @endif
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            @if ($detail_produk)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{$product->name}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Name</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$product->name}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Name</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$product->name}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Code</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$product->code}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Slug</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$product->slug}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Description</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{!! @$product->description !!}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Weight</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$product->weight}}</span>
                        </div>
                    </div>
                </div>
            </div>
            @elseif ($detail_trans_view)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Transaction
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Id</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$transaction->id}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Invoice</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$transaction->id_transaksi}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Payment Method</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$transaction->paymentMethod->bank_name}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Nominal</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">Rp {{number_format(@$transaction->nominal,0,',','.')}}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-bold text-muted">Resi</label>
                        <div class="col-lg-8">
                            <span class="fw-bolder fs-6 text-gray-800">{{@$transaction->resi}}</span>
                        </div>
                    </div>
                </div>
            </div>
            @elseif ($detail_trans)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detail Transaction
                    </h4>
                </div>
                <div class="card-body">
                    <table id="basic-datatables2" class="display table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Transaction Date</th>
                                <th>Invoice Number</th>
                                <th>Nominal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaction_list as $trans)
                            <tr>
                                <td>{{ @$trans->transaction_id }}</td>
                                <td>{{ @$trans->created_at }}</td>
                                <td>{{ @$trans->id_transaksi }}</td>
                                <td>{{ @$trans->nominal }}</td>
                                <td><button wire:click="getDetailTrans('{{ @$trans->transaction_id }}')" class="btn btn-primary btn-sm">Detail</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <livewire:table.inventory-table params="{{$route_name}}" />
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



    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
                
                
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>