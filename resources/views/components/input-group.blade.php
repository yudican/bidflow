<div class="form-group {{$errors->has($name) ? 'has-error has-feedback' : '' }}">
  @if (isset($label))
  @if (in_array($type, ['text', 'password', 'date', 'email','number']))
  <label for="{{$name}}" class="placeholder"><b>{{$label}}</b> <span style="color:red">{{ $isreq ?? '' }}</span></label>
  @endif
  @endif

  <div class="input-group">
    <input id="{{$name}}" value="{{$value ?? ''}}" name="{{$name}}" wire:model="{{$name}}" type="{{$type ?? 'text'}}" class="form-control" {{isset($readonly) ? 'readonly' : '' }} placeholder="{{isset($placeholder) ? $placeholder : ''}}">
    <div class="input-group-append cursor-pointer" wire:click="{{$onclick}}">
      @if (isset($buttonLabel))
      <span class="input-group-text" id="basic-addon2">{{$buttonLabel}}</span>
      @else
      <span class="input-group-text" id="basic-addon2"><i class="fas fa-search"></i></span>
      @endif
    </div>
  </div>
  <small id="helpId" class="text-danger">{{ $errors->has($name) ? $errors->first($name) : '' }}</small>
</div>