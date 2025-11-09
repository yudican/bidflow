<div class="list-group-item-figure" id="{{$trans->id}}" wire:key="item-{{ $trans->id }}">
  <div class="dropdown">
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
    </button>
    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      @if (isset($role) && $trans)
      @if (in_array($role->role_type, ['admin', 'superadmin', 'finance', 'warehouse', 'admindelivery', 'adminsales', 'leadwh']))
      <a href="#" wire:click="getDataById({{$trans->id}})" class="dropdown-item">Detail Pesanan</a>
      @if ($role->role_type == 'finance' && $trans->status < 3) @if ($trans->confirmPayment)
        <a href="#" wire:click="showPaymentDetail({{$trans->id}})" class="dropdown-item">Verifikasi Manual</a>
        @endif
        @endif

        @if(in_array($role->role_type, ['warehouse', 'admindelivery','adminsales','leadwh','superadmin']) && $trans->status_delivery == 1 && $trans->status == '7')
        @if (in_array($role->role_type, ['adminsales','leadwh','superadmin']) && $trans->label)
        <a href="{{$trans->label->label_url}}" target="_blank" class="dropdown-item">Cetak Label</a>
        @endif
        @if (in_array($role->role_type, ['warehouse', 'admindelivery']))
        <a href="#" wire:click="packingProcess({{$trans->id}})" class="dropdown-item">Proses Pengemasan</a>
        @endif

        @endif

        @if(in_array($role->role_type, ['warehouse','admindelivery','adminsales','leadwh','superadmin']) && $trans->status_delivery == 21)
        @if (in_array($role->role_type, ['adminsales','leadwh','superadmin']) && $trans->label)
        <a href="{{$trans->label->label_url}}" target="_blank" class="dropdown-item">Cetak Label</a>
        @endif

        @if (in_array($role->role_type, ['warehouse', 'admindelivery']))
        <a href="#form-modal-resi" data-toggle="modal" wire:click="inputResi({{$trans->id}})" class="dropdown-item">Dikirim</a>
        <a href="#" wire:click="logTransaction({{$trans->id}})" class="dropdown-item">Log</a>
        @endif
        @endif

        @if(($role->role_type == 'warehouse' || $role->role_type == 'admindelivery') && $trans->status_delivery == 3)
        <!--<a href="{{route('invoice.struct.print', $trans->id)}}" target="_blank" class="dropdown-item">Data Penerima</a>-->
        <a href="#" wire:click="productReceived({{$trans->id}})" class="dropdown-item">Diterima</a>
        @endif

        @if (in_array($role->role_type, ['admin', 'superadmin', 'finance', 'adminsales', 'leadwh']) && $trans->status == 3)
        <a href="#" wire:click="assignWarehouse({{$trans->id}})" class="dropdown-item">Assign To Warehouse</a>
        @endif
        @elseif (in_array($role->role_type, ['agent','subagent']))
        <a href="{{route('transaction.detail', $trans->id)}}" class="dropdown-item">Detail Pesanan</a>
        @if ($trans->status_delivery >= 3 && $trans->resi)
        <button wire:click="showTimeline('{{$trans->resi}}')" class="dropdown-item">Lacak</button>
        @endif
        @endif
        @endif

    </div>
  </div>
</div>