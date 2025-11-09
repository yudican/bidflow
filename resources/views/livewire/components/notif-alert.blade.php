@if(count($notif)>1)
<div id="myDIV" data-notify="container" class="col-10 col-xs-11 col-sm-4 alert alert-info" role="alert" data-notify-position="top-center" style="display: inline-block; margin: 0px auto; padding-left: 65px; position: fixed; transition: all 0.5s ease-in-out 0s; z-index: 1031; top: 20px; left: 0px; right: 0px;">
    <button onclick="myFunction()" aria-hidden="true" class="close" data-notify="dismiss" style="position: absolute; right: 10px; top: 5px; z-index: 1033;">×</button>
    <span data-notify="icon" class="fa fa-bell"></span> 
    @foreach ($notif as $item)
    <span data-notify="message">{{ $item->name }}</span>
    @endforeach
</div>

<script>
function myFunction() {
  var x = document.getElementById("myDIV");
  if (x.style.display === "none") {
    x.style.display = "block";
  } else {
    x.style.display = "none";
  }
}
</script>
@endif
