<div class="list-group-item-figure" id="lead-{{$id}}" wire:key="item-{{ $id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      <button wire:click="getDataById('{{ $id }}')" class="dropdown-item">Ubah</button>
      <button wire:click="getDetailById('{{ $uid_lead }}')" class="dropdown-item">Lihat</button>
      @if ($lead->status == 2 && (auth()->user()->role->role_type == 'adminsales' || auth()->user()->role->role_type != 'leadwh' || auth()->user()->role->role_type == 'superadmin' || auth()->user()->role->role_type == 'leadsales'))
      <button data-toggle='modal' data-target='#approve-modal' wire:click="getDetailApprove('{{ $uid_lead }}')" class="dropdown-item">Approve</button>
      @endif
      <!-- <button data-toggle='modal' data-target='#confirm-modal' wire:click="getId('{{ $id }}')" class="dropdown-item">Delete</button> -->
      @if($lead->status != '1')
      <button data-toggle='modal' data-target='#confirm-modal' wire:click="getId('{{ $id }}')" class="dropdown-item">Cancel</button>
      @endif
    </div>
    @if($lead->status == '0')
    <span class="badge badge-danger text-danger w-3 h-3 absolute right-0 bottom-1" style="padding: 1px 4px;">n</span>
    @endif
  </div>
</div>