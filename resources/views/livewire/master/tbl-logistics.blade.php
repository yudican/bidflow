<div class="page-inner">
    <x-loading />
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3 text-capitalize"></i>logistics</span>
                        </a>
                        <div class="pull-right">
                            <button class="btn btn-danger btn-sm" wire:click="updateKurir"><i class="fas fa-refresh"></i> Update Kurir</button>
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <livewire:table.logistic-table params="{{$route_name}}" />
        </div>

        {{-- Modal form --}}
        <div id="form-modal-diskon" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        @if ($row)
                        <h5 class="modal-title text-capitalize" id="my-modal-title"> Pengaturan diskon ({{$row->logistic_name}})</h5>

                        <img src="{{$row->logistic_url_logo}}" style="height:30px;" alt="">
                        @else
                        <h5 class="modal-title text-capitalize" id="my-modal-title"> Pengaturan diskon </h5>
                        @endif
                    </div>
                    <div class="modal-body">
                        @if ($row)
                        <div class="card">
                            <div class="table-responsive min-w-full">
                                <table class="table table-bordered w-full" width="100%">
                                    <thead class="thead-lightss">
                                        <tr>
                                            {{-- <td>Logistic</td> --}}
                                            {{-- <td>Logistic Rate Code</td> --}}
                                            <td width="15%">Rate Name</td>
                                            <td width="65%">Diskon (Rp)</td>
                                            <td width="20%">Tanggal Mulai</td>
                                            <td width="20%">Tanggal Berakhir</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row->logisticRates as $item)
                                        <tr>
                                            {{-- <td>{{ $item->logistic->logistic_name }}</td> --}}
                                            {{-- <td>{{ $item->logistic_rate_code }}</td> --}}
                                            <td>{{ $item->logistic_rate_name }}</td>
                                            <td>
                                                <x-text-field type="text" name="shipping_price_discount.{{$item->id}}" placeholder="10.000" />
                                            </td>
                                            <td>
                                                <x-text-field type="date" name="shipping_price_discount_start.{{$item->id}}" />
                                            </td>
                                            <td>
                                                <x-text-field type="date" name="shipping_price_discount_end.{{$item->id}}" />
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</a>
                            <button class="btn btn-success btn-sm" wire:click='saveDiscount'><i class="fa fa-check pr-2"></i>Simpan Diskon</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal form --}}
        <div id="form-modal" wire:ignore.self class="modal fade" tabindex="-1" permission="dialog" aria-labelledby="my-modal-title" aria-hidden="true">
            <div class="modal-dialog  modal-lg" permission="document">
                <div class="modal-content {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                    <div class="modal-header {{ $is_dark_mode ? 'bg-black' : 'bg-white' }}">
                        @if ($row)
                        <h5 class="modal-title text-capitalize" id="my-modal-title"> logistics rate ({{$row->logistic_name}})</h5>

                        <img src="{{$row->logistic_url_logo}}" style="height:30px;" alt="">
                        @else
                        <h5 class="modal-title text-capitalize" id="my-modal-title"> logistics rate </h5>
                        @endif
                    </div>
                    <div class="modal-body">
                        @if ($row)
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-lightss">
                                    <thead class="thead-lightss">
                                        <tr>
                                            {{-- <td>Logistic</td> --}}
                                            {{-- <td>Logistic Rate Code</td> --}}
                                            <td>Rate Name</td>
                                            <td>Status</td>
                                            <td>Custommer Status</td>
                                            <td>Agent Status</td>
                                            <td>Cod</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($row->logisticRates as $item)
                                        <tr>
                                            {{-- <td>{{ $item->logistic->logistic_name }}</td> --}}
                                            {{-- <td>{{ $item->logistic_rate_code }}</td> --}}
                                            <td>{{ $item->logistic_rate_name }}</td>
                                            <td>
                                                @livewire('components.toggle-status', [
                                                'id' => $item->id,
                                                'active' => $item->logistic_rate_status,
                                                'field' => 'logistic_rate_status',
                                                'emitter' => 'toggleStatus',
                                                'parent_id' => null,
                                                'child_id' => null,
                                                ],key('logistic_rate_status_'.$item->id))
                                            </td>
                                            <td>
                                                @livewire('components.toggle-status', [
                                                'id' => $item->id,
                                                'active' => $item->logistic_agent_status,
                                                'field' => 'logistic_agent_status',
                                                'emitter' => 'toggleStatus',
                                                'parent_id' => null,
                                                'child_id' => null,
                                                ],key('logistic_agent_status_'.$item->id))
                                            </td>
                                            <td>
                                                @livewire('components.toggle-status', [
                                                'id' => $item->id,
                                                'active' => $item->logistic_custommer_status,
                                                'field' => 'logistic_custommer_status',
                                                'emitter' => 'toggleStatus',
                                                'parent_id' => null,
                                                'child_id' => null,
                                                ],key('logistic_custommer_status_'.$item->id))
                                            </td>
                                            <td>
                                                @if ($item->logistic_cod_status)
                                                <svg class="h-5 w-5 stroke-current text-green-600 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                @else
                                                <svg class="h-5 w-5 stroke-current text-red-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                @endif
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger btn-sm" wire:click='_reset'><i class="fa fa-times pr-2"></i>Tutup</a>
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
            window.livewire.on('showModal', (data) => {
                $('#form-modal').modal('show')
            });

            window.livewire.on('closeModal', (data) => {
                $('#form-modal').modal('hide')
            });

            window.livewire.on('showModalDiskon', (data) => {
                $('#form-modal-diskon').modal(data)
            });
        })
    </script>
    @endpush
</div>