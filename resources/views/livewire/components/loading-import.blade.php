<div>
    @if ($loading)
    <div wire:poll.keep-alive>
        <div class="card">
            <div class="card-body">
                <p>Mohon Tunggu Upload Sedang Berlangsung</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{$percentage}}%;" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100">
                        {{$percentage}}%</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>