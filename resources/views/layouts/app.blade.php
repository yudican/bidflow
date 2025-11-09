<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user_id" content="{{ auth()->user()->id }}">
    @if (config('app.production'))
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <title>BIDFLOW | Admin Portal</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
    <link rel="icon" href="{{asset('assets/img/bidflowlogo.jpg')}}" type="image/x-icon" />

    {{-- <script type="text/javascript">
        ;(function (c, l, a, r, i, t, y) {
        c[a] =
        c[a] ||
        function () {
            ;(c[a].q = c[a].q || []).push(arguments)
        }
        t = l.createElement(r)
        t.async = 1
        t.src = "https://www.clarity.ms/tag/" + i
        y = l.getElementsByTagName(r)[0]
        y.parentNode.insertBefore(t, y)
    })(window, document, "clarity", "script", "k206ctkddf")
    </script> --}}

    <!-- Fonts and icons -->
    <script src="{{asset('assets/js/plugin/webfont/webfont.min.js')}}"></script>
    <script>
        WebFont.load({
        			google: {"families":["Lato:300,400,700,900"]},
        			custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: [`{{request()->getSchemeAndHttpHost()}}/assets/css/fonts.min.css`]},
        			active: function() {
        				sessionStorage.fonts = true;
        			}
        		});
    </script>

    <script>
        global = globalThis 
        window.csrf_token = "{{ csrf_token() }}";
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/atlantis.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    {{--
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> --}}
    <!-- Styles -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="{{ asset('js/app.js') }}" defer></script>
</head>

<body class="font-sans antialiased dark" id="body" data-background-color="{{$is_dark_mode ? 'dark' : ''}}" style="background-color: #f8f9fa;">
    <div>
        {{$slot}}
    </div>


    {{-- <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/core/popper.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script> --}}


    {{-- @livewireScripts --}}
    {{-- <script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v0.x.x/dist/livewire-sortable.js"></script> --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        function showToast(message, type = 'success') {
        const backgroundColor = type === 'success' ? '#28a745' : 
                              type === 'error' ? '#dc3545' :
                              type === 'warning' ? '#ffc107' : '#17a2b8';
                              
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                backgroundColor,
                stopOnFocus: true,
                onClick: function(){} // Callback after click
            }).showToast();
        }
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;
        const user_id = `{{Auth::user()->id}}`
        var pusher = new Pusher('f01866680101044abb79', {
          cluster: 'ap1'
        });

        var channel = pusher.subscribe('bidflow');
        channel.bind('switch-account', function(data) {
            if (data?.user_id == user_id) {
                 setTimeout(() => {
                    window.location.reload();
                 }, 2000);
            }
        });

        // popaket_order
        channel.bind('popaket_order', function(data) {
            showToast(data.message, data.type);
        });
    </script>
</body>

</html>