<div class="page-inner">
    <x-loading />
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-capitalize">
                        <a href="{{route('dashboard')}}">
                            <span><i class="fas fa-arrow-left mr-3 text-capitalize"></i>Shipping Voucher</span>
                        </a>
                        <div class="pull-right">
                            <button class="btn btn-primary btn-sm" wire:click="store"><i class="fas fa-plus"></i> Simpan</button>
                        </div>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="accordion" id="accordionExample">
                        @foreach ($logistics as $logistic)
                        <div class="card shadow">
                            <div class="card-header" id="headingOne-{{$logistic->id}}">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left text-black text-decoration-none" type="button" wire:click="toggleOpen({{$logistic->id}})">
                                        <div class="d-flex flex-row justify-content-start align-items-center">
                                            <img src="{{$logistic->logistic_url_logo}}" style="height: 30px;" alt="">
                                            <span class="ml-4 " style="color:black;">{{$logistic->logistic_name}}</span>
                                        </div>
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne-{{$logistic->id}}" class="collapse @if(isset($open[$logistic->id])) show @endif" aria-labelledby="headingOne-{{$logistic->id}}" data-parent="#accordionExample">
                                <div class="card-body  bg-white ">
                                    <div class="accordion" id="accordionRate">
                                        @foreach ($logistic->logisticRates as $rate)
                                        <div class="card mt-2 shadow-sm">
                                            <div class="card-header" id="headingOneRate-{{$rate->id}}">
                                                <h2 class="mb-0">
                                                    <button class="btn btn-link btn-block text-left text-black text-decoration-none" type="button" data-toggle="collapse" data-target="#collapseOneRate-{{$rate->id}}" aria-expanded="true" aria-controls="collapseOneRate-{{$rate->id}}">
                                                        <span style="color:black;">{{$rate->logistic_rate_name}}</span>

                                                    </button>
                                                </h2>
                                            </div>

                                            <div id="collapseOneRate-{{$rate->id}}" class="collapse @if(isset($open[$logistic->id])) show @endif" aria-labelledby="headingOneRate-{{$rate->id}}" data-parent="#accordionRate">
                                                <div class="card-body row">
                                                    <div class="col-md-4">
                                                        <x-text-field type="text" name="shipping_price_discount.{{$rate->id}}" label="Shipping Discount" placeholder="10.000" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-text-field type="date" name="shipping_price_discount_start.{{$rate->id}}" label="Start Date" />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-text-field type="date" name="shipping_price_discount_end.{{$rate->id}}" label="End Date" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
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
        })
    </script>
    @endpush
</div>