<div class="form-group">
  @if (!empty($label))
  <label>{{$label}}</label>
  @endif
  {{-- loading --}}
  <img wire:loading wire:target="{{$name}}" src="{{asset('assets/img/loader.gif')}}" style="height: 30px;margin-top: 5px;margin-left: 10px;position: absolute;z-index: 9999;right:170px;" class="float-right" alt="loader">
  <div class="input-group">
    <div class="custom-file">
      <input type="file" class="custom-file-input" id="{{$name}}" wire:model="{{$name}}" accept="*">
      @if ($file)
      @if ($path)
      <label class="custom-file-label" for="{{$name}}">{{substr($path, 0, 10)}}...</label>
      @else
      <label class="custom-file-label" for="{{$name}}">{{$file}}</label>
      @endif
      @else
      @if ($path)
      <label class="custom-file-label" for="{{$name}}">{{$path}}</label>
      @else
      <label class="custom-file-label" for="{{$name}}">Choose file</label>
      @endif
      @endif
    </div>
  </div>
  <small id="helpId" class="text-danger">{{ $errors->has($name) ? $errors->first($name) : '' }}</small>
</div>