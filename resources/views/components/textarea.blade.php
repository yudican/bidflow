<div class="form-group {{$errors->has($name) ? 'has-error has-feedback' : '' }}">
    <label for="{{$name}}" class="placeholder"><b>{{$label}}</b> <span style="color:red">{{ $isreq ?? '' }}</span></label>
    <textarea id="{{$name}}" name="{{$name}}" wire:model="{{$name}}" type="text" class="form-control" rows="3"></textarea>
    <small id="helpId" class="text-danger">{{ $errors->has($name) ? $errors->first($name) : '' }}</small>
</div>