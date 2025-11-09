<div class="list-group-item-figure" id="contact-{{$id}}" wire:key="item-{{ $id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      <button wire:click="getDetailTransById('{{ $id }}')" class="dropdown-item">Lihat Detail Transaksi</button>
      <button wire:click="getDetailProductById('{{ $product_id }}')" class="dropdown-item">Lihat Detail Produk</button>
    </div>
  </div>
</div>