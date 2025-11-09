<div class="page-inner">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <span>List Data Metode Pembayaran</span>
                        <div class="pull-right">
                            @if (!$form && !$modal)
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
            <livewire:table.payment-method-table params="{{$route_name}}" />
        </div>

        {{-- Modal form --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        <h5 class="modal-title text-capitalize" id="my-modal-title">{{$update_mode ? 'Update' : 'Tambah'}} Data Pembayaran</h5>
                        <button style="float:right;" class="btn btn-danger btn-xs" wire:click='_reset'><i class="fa fa-times"></i></button>
                    </div>
                    <div class="modal-body">
                        <x-select name="parent_id" label="Kategori Pembayaran" ignore>
                            <option value="">Pilih Kategori Pembayaran</option>
                            @foreach ($parents as $parent)
                            <option value="{{$parent->id}}">{{$parent->nama_bank}}</option>
                            @endforeach
                        </x-select>
                        <x-text-field type="text" name="nama_bank" label="Nama Pembayaran" />

                        <div>
                            @if ($parent_id)
                            <x-select name="payment_type" label="Payment Type" id="type_payment" ignore>
                                <option value="">Select Payment Type</option>
                                <option value="Manual">Manual</option>
                                <option value="Otomatis">Otomatis</option>
                            </x-select>
                            <x-select name="payment_channel" label="Channel Pembayaran" id="channel_payment" ignore>
                                <option value="">Select Payment Type</option>
                                <option value="bank_transfer">bank_transfer</option>
                                <option value="echannel">echannel</option>
                                <option value="bca_klikpay">bca_klikpay</option>
                                <option value="bca_klikbca">bca_klikbca</option>
                                <option value="bri_epay">bri_epay</option>
                                <option value="gopay">gopay</option>
                                <option value="shopeepay">shopeepay</option>
                                <option value="qris">Qris</option>
                                <option value="mandiri_clickpay">mandiri_clickpay</option>
                                <option value="cimb_clicks">cimb_clicks</option>
                                <option value="danamon_online">danamon_online</option>
                                <option value="cstore">cstore</option>
                                <option value="cod_jne">cod_jne</option>
                                <option value="cod_jxe">cod_jxe</option>
                            </x-select>
                            @if ($payment_channel == 'bank_transfer' && $payment_type == 'Otomatis')
                            <div>
                                <x-text-field type="number" name="payment_va_number" label="Nomor Virtual Akun" />
                                <x-text-field type="text" name="payment_code" label="Kode Pembayaran" />
                            </div>
                            @endif
                            <div>
                                @if ($payment_type == 'Manual')
                                <div>
                                    <x-text-field type="number" name="nomor_rekening_bank" label="Nomor Rekening Bank" />
                                    <x-text-field type="text" name="nama_rekening_bank" label="Nama Rekening Bank" />
                                </div>
                                @endif


                                <x-input-photo foto="{{$logo_bank}}" path="{{optional($logo_bank_path)->temporaryUrl()}}" name="logo_bank_path" label="Logo Bank" />
                            </div>
                            @endif
                        </div>
                        <x-select name="status" label="Status" ignore>
                            <option value="">Select Status</option>
                            <option value="1">Active</option>
                            <option value="0">Non Active</option>
                        </x-select>
                    </div>
                    <div class="modal-footer">

                        <button type="button" wire:click={{$update_mode ? 'update' : 'store' }} wire:target="logo_bank_path" wire:loading.attr="disabled" class="btn btn-primary btn-sm"><i class="fa fa-check pr-2"></i>Simpan</button>

                    </div>
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
    @push('scripts')
    <script src="{{ asset('assets/js/plugin/select2/select2.full.min.js') }}"></script>


    <script>
        $(document).ready(function(value) {
            window.livewire.on('loadForm', (data) => {
           
                $('#type_payment').select2({
                        theme: "bootstrap",
                        width:'auto'
                });
                $('#type_payment').on('change', function (e) {
                    let data = $(this).val();
                    console.log(data);
                    @this.set('payment_type', data);
                });

                $('#channel_payment').select2({
                    theme: "bootstrap",
                    width:'auto'
                });
                $('#channel_payment').on('change', function (e) {
                    let data = $(this).val();
                    @this.set('payment_channel', data);
                });
            });
            window.livewire.on('showModal', (data) => {
                    $('#parent_id').select2({
                        theme: "bootstrap",
                        width:'auto'
                    });
                    $('#parent_id').on('change', function (e) {
                        let data = $(this).val();
                        console.log(data);
                        @this.set('parent_id', data);
                    });

                    $('#status').select2({
                        theme: "bootstrap",
                        width:'auto'
                    });
                    $('#status').on('change', function (e) {
                        let data = $(this).val();
                        console.log(data);
                        @this.set('status', data);
                    });
                
                $('#form-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#confirm-modal').modal('hide')
                $('#form-modal').modal('hide')
            });
        })
    </script>
    @endpush
</div>