<div class="list-group-item-figure" id="action-{{$id}}" wire:key="item-{{ $id }}">
  @if (auth()->user()->hasTeamPermission($curteam, $segment.':update') ||
  auth()->user()->hasTeamPermission($curteam, $segment.':delete'))
  <div class="dropdown">
    @if (isset($order))
    <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
      <i class="fas fa-ellipsis-h"></i>
      @if($order->status == '0')
      &nbsp; <span class="badge badge-danger text-danger w-3 h-3" style="padding: 1px 4px;">n</span>
      @endif
    </button>
    @endif

    <div class="dropdown-arrow"></div>
    <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
      @if (auth()->user()->hasTeamPermission($curteam, $segment.':update'))
      <button wire:click="getDataById('{{ $id }}')" class="dropdown-item">Ubah</button>
      @endif
      @if (auth()->user()->hasTeamPermission($curteam, $segment.':delete'))
      <a wire:click="getId('{{ $id }}')" href="#confirm-modal" data-toggle="modal" class="dropdown-item">Hapus</a>
      @endif

      @if (isset($extraActions))
      @foreach ($extraActions as $extra)
      @if ($extra['type'] == 'link')
      <a href="{{route($extra['route'],$extra['params'])}}" class="dropdown-item">{{$extra['label']}}</a>
      @elseif ($extra['type'] == 'modal')
      <a wire:click="{{$extra['route']}}" href="#{{$extra['id']}}" data-toggle="modal" class="dropdown-item">{{$extra['label']}}</a>
      @else
      <button wire:click="{{$extra['route']}}" class="dropdown-item">{{$extra['label']}}</button>
      @endif
      @endforeach
      @endif

    </div>
  </div>
  @endif
</div>