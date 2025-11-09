<div>
  <button wire:loading.remove class="btn btn-{{isset($variant) ? $variant : 'primary'}} btn-sm mr-2" wire:click="{{$onClick}}">
    @if (isset($icon))
    <i class="{{$icon}}"></i>
    @endif
    <span>{{$label}}</span>
  </button>
  <button wire:loading wire:target="delete,store,update,_reset,toggleForm,showModal,export,convert" class="btn btn-primary btn-sm flex justify-center items-center">
    <img src="{{asset('assets/img/loader-btn.gif')}}" style="height:20px;" alt="">
  </button>
</div>