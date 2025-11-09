<div>
    @if ($loading)
    <div>
        <div class="card">
            <div class="card-body">
                <p>Mohon Tunggu Convert Sedang Berlangsung</p>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: {{$percentage}}%;" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100">
                        {{$percentage}}%</div>

                </div>
            </div>
        </div>
    </div>
    @endif
    <div>
        <span>Success: <span class="text-success">{{$success_total}}</span></span>
        <span>Error: <span class="text-danger">{{$error_total}}</span></span>
    </div>
</div>