<div class="list-group-item-figure" id="action-{{$id}}" wire:key="item-{{ $id }}">
    <div class="dropdown">
        <button class="btn-dropdown" data-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-ellipsis-h"></i>
            @if($badge === true)
            <span class="badge badge-danger ml-2">New</span>
            @endif
        </button>
        <div class="dropdown-arrow"></div>
        <div class="dropdown-menu dropdown-menu-right" x-placement="bottom-end" style="position: absolute; transform: translate3d(-124px, 25px, 0px); top: 0px; left: 0px; will-change: transform;">
            @if ($canUpdate)
            <button wire:click="getDataById('{{ $id }}')" class="dropdown-item">Ubah</button>
            @endif
            @if ($canDelete)
            <a wire:click="getId('{{ $id }}')" href="#confirm-modal" data-toggle="modal" class="dropdown-item">Hapus</a>
            @endif

            @foreach ($extraActions as $extra)
            @if ($extra['type'] == 'link')
            <a href="{{route($extra['route'],$extra['params'])}}" class="dropdown-item">{{$extra['label']}}</a>
            @elseif ($extra['type'] == 'modal')
            <a wire:click="{{$extra['route']}}" href="#{{$extra['id']}}" data-toggle="modal" class="dropdown-item">{{$extra['label']}}</a>
            @else
            <button wire:click="{{$extra['route']}}" class="dropdown-item">{{$extra['label']}}</button>
            @endif
            @endforeach
        </div>
    </div>
    {{-- {{$segment}} --}}
</div>