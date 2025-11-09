<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>BIDFLOW | LOGIN</title>

  <!-- Fonts -->
  <!-- <link rel="icon" href="https://aimidev.s3.us-west-004.backblazeb2.com/upload/user/vRjRT1hSkFsQybE2DxYJHV4maRdirfcuOg1ENONH.ico" type="image/x-icon" /> -->
  <link rel="icon" href="{{asset('assets/img/bidflowlogo.jpg')}}" type="image/x-icon" />
  <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
  @viteReactRefresh
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  <div id="spa-index"></div>
  <script>
    const global = globalThis;
  </script>
</body>

</html>