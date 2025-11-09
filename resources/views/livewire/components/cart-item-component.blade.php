<article class="mb-3 border-b-2 pb-3">
    <div class="d-flex justify-content-between align-items-center flex-row">
        <div>
            <a href="#" class="itemside align-items-center">
                <div class="form-check p-0 m-0">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" value="">
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
                    @if ($cart->qty <= 1) <button class="btn btn-light btn-xs" type="button" disabled> <i class="fas fa-minus"></i> </button>
                        @else
                        <button class="btn btn-light btn-xs" type="button" wire:click="min_qty({{$cart->id}})"> <i class="fas fa-minus"></i> </button>
                        @endif

                        <button class="btn btn-light btn-xs" type="button"> {{$cart->qty}} </button>
                        <button class="btn btn-light btn-xs" type="button" wire:click="add_qty({{$cart->id}})"> <i class="fas fa-plus"></i> </button>
                </div> <!-- input-group.// -->
                <button class="btn btn-light btn-xs" wire:click="delete({{$cart->id}})"> <i class="fas fa-trash"></i> </button>
            </div>
        </div>
    </div> <!-- row.// -->
</article>