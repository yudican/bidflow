<div class="list-group-item-figure" id="re-{{$id}}" wire:key="item-{{ $id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
    ererer
      <button wire:click="getDataById('{{ $id }}')" class="dropdown-item">Ubah</button>
      <a wire:click="getId('{{ $id }}')" href="#confirm-modal" data-toggle="modal" class="dropdown-item">Hapus</a>
      <!-- <button wire:click="getDetailById('{{ $uid_case }}')" class="dropdown-item">Lihat</button> -->
    </div>
  </div>
</div>