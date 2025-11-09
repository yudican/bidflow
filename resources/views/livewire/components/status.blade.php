@if (isset($status))
<span class="badge badge-{{$status == 1 ? 'success' : 'danger'}}">{{$status == 1 ? 'Active' : 'Non Active'}}</span>
@else
<span>-</span>
@endif