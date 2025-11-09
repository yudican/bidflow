<div class="list-group-item-figure" id="lead-{{$id}}" wire:key="item-{{ $id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      <button wire:click="getDataById('{{ $id }}')" class="dropdown-item">Ubah</button>
      <button wire:click="getDetailById('{{ $uid_case }}')" class="dropdown-item">Lihat</button>
      <!-- <a href="https://wa.me/{{ $wa }}" class="dropdown-item">Chat Whatsapp</a> -->
      <button wire:click="chatWA('{{ $wa }}')" class="dropdown-item">Chat Whatsapp</button>
    </div>
  </div>
</div>
