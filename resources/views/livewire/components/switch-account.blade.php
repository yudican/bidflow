<div>
    <select class="appearance-none bg-white/50 text-white font-semibold rounded-md border border-white" wire:change="handleSwitch($event.target.value)">
        @foreach ($accounts as $item)
        <option value="{{$item->id}}" @if($item->id == $account_id) selected @endif>{{$item->account_name}}</option>
        @endforeach
    </select>

    {{-- script --}}
    <script>
        document.addEventListener('livewire:load', function () {
            
            window.livewire.on('handleSwitch', (data) => {
                localStorage.setItem('account_id', data);
                window.location.reload();
            });
           
        })
    </script>
</div>