<div wire:key="item-{{ $data_id }}">
    @if ($active)
    <div class="toggle btn btn-round btn-success" wire:click="toggleStatusAgent('{{$data_id}}','{{$field}}','{{$emitter}}')" data-toggle="toggle" style="width: 92.8906px; height: 43.7812px;"><input type="checkbox" checked="" data-toggle="toggle" data-onstyle="success" data-style="btn-round">
        <div class="toggle-group"><label class="btn btn-success toggle-on">On</label><label class="btn btn-black active toggle-off">Off</label><span class="toggle-handle btn btn-black"></span></div>
    </div>
    @else
    <div class="toggle btn btn-round btn-black off" wire:click="toggleStatusAgent('{{$data_id}}','{{$field}}','{{$emitter}}')" data-toggle="toggle" style="width: 92.8906px; height: 43.7812px;"><input type="checkbox" checked="" data-toggle="toggle" data-onstyle="info" data-style="btn-round">
        <div class="toggle-group"><label class="btn btn-info toggle-on">On</label><label class="btn btn-black active toggle-off">Off</label><span class="toggle-handle btn btn-black"></span></div>
    </div>
    @endif
</div>