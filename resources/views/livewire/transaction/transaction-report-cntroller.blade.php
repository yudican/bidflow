<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>Report Transaksi</span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body row">
                    <div class="col-md-3">
                        <x-text-field type="text" name="reportrange" label="Tanggal" id="reportrange" />
                    </div>
                    <div class="col-md-2">
                        <x-select name="status" label="Status" ignore>
                            <option value="">Status</option>
                            <option value="1">Menunggu Pembayaran</option>
                            <option value="2">Perlu Dikonfirmasi</option>
                            <option value="3">Pesanan Baru</option>
                            <option value="7">Diproses Gudang</option>
                        </x-select>
                    </div>
                    <div class="col-md-2">
                        <x-select name="status_delivery" label="Status Delivery" ignore>
                            <option value="">Status Delivery</option>
                            <option value="21">Siap Dikirim</option>
                            <option value="3">Pengiriman</option>
                            <option value="4">Pesanan Diterima</option>

                        </x-select>
                    </div>
                    {{-- <div class="col-md-3">
                        <x-select name="transaction_type" label="Tipe Transaksi" ignore>
                            <option value="">Tipe Transaksi</option>
                            <option value="customer">Custommer</option>
                            <option value="agent">Agent</option>
                        </x-select>
                    </div> --}}
                    <div class="col-md-2">
                        <label for=""></label>
                        <button class="btn btn-primary mt-8 btn-sm" wire:click="submitFilter" style="margin-top: 40px;">Filter</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <livewire:table.transaction-report-table />
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
        })
    </script>
    @endpush
</div>