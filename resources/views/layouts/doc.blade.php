<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="{{ asset('assets/css/pdf-style.css') }}" rel="stylesheet">
    <title>@yield('title',config('app.name', 'Aimi Crm'))</title>
</head>

<body>
    @yield('content')
</body>

</html>