<div>
  <div @isset($ignore) wire:ignore @endisset class="form-group {{$errors->has($name) ? 'has-error has-feedback' : '' }} w-100">
    @if (isset($label))
    <label for="{{$name}}" class="placeholder"><b>{{$label}}</b> <span style="color:red">{{ $isreq ?? '' }}</span></label>
    @endif

    <select name="{{$name}}" id="{{isset($id) ? $id : $name}}" wire:model="{{$name}}" wire:change="{{isset($handleChange) ? $handleChange.'($event.target.value)' : ''}}" class="form-control {{isset($class) ? $class : ''}}" {{isset($multiple) ? 'multiple' : '' }}>
      {{$slot}}
    </select>
  </div>
  <small id="helpId" class="text-danger ml-2">{{ $errors->has($name) ? $errors->first($name) : '' }}</small>
</div>