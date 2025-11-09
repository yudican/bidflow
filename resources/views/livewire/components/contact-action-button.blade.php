<div class="list-group-item-figure" id="contact-{{$id}}" wire:key="item-{{ $id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
      @if($created_at == date('Y-m-d'))
      &nbsp; <span class="badge badge-danger">New</span>
      @endif
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      <button wire:click="getDetailById('{{ $id }}')" class="dropdown-item">Lihat Detail</button>
      <button wire:click="getDetailById2('{{ $id }}')" class="dropdown-item">Ubah</button>
    </div>
  </div>
</div>