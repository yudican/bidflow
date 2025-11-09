<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>FIS | Admin Portal</title>

  <!-- Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
  <!-- <link rel="icon" href="https://aimidev.s3.us-west-004.backblazeb2.com/upload/user/vRjRT1hSkFsQybE2DxYJHV4maRdirfcuOg1ENONH.ico" type="image/x-icon" /> -->
  <link rel="icon" href="{{asset('assets/img/bidflowlogo.jpg')}}" type="image/x-icon" />

  <!-- Fonts and icons -->
  <script src="{{asset('assets/js/plugin/webfont/webfont.min.js')}}"></script>
  <script>
    WebFont.load({
        			google: {"families":["Lato:300,400,700,900"]},
        			custom: {"families":["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: [`{{asset('assets/css/fonts.min.css')}}`]},
        			active: function() {
        				sessionStorage.fonts = true;
        			}
        		});
  </script>

  <!-- CSS Files -->
  <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
  <link rel="stylesheet" href="{{asset('assets/css/atlantis2.css')}}">

  <!-- Styles -->
  {{--
  <link rel="stylesheet" href="{{ mix('css/app.css') }}"> --}}
  @stack('styles')
  @livewireStyles
  <style>
    .cursor-pointer {
      cursor: pointer;
    }

    .cursor-default {
      cursor: default;
    }

    .absolute {
      position: absolute;
      bottom: 5px;
      left: 5px;
    }

    .table td,
    .table th {
      font-size: 14px;
      border-top-width: 0px;
      border-bottom: 1px solid;
      border-color: #ebedf2 !important;
      padding: 0 10px !important;
      height: 60px;
      vertical-align: middle !important;
    }

    .navbar .navbar-nav .nav-item .nav-link:hover {
      background-color: #fff !important;
      color: black border-radius:5px
    }

    .navbar .navbar-nav .nav-item {
      margin-right: 0;
    }

    .navbar .navbar-nav .nav-item:hover {
      background-color: #fff !important;
    }

    .btn-default {
      background-color: #fff;
    }

    .main-header[data-background-color="white"] .navbar-nav .nav-item .nav-link:hover,
    .main-header[data-background-color="white"] .navbar-nav .nav-item .nav-link:focus,
    .main-header.fixed[data-background-color="transparent"] .navbar-nav .nav-item .nav-link:hover,
    .main-header.fixed[data-background-color="transparent"] .navbar-nav .nav-item .nav-link:focus {
      background: #fff !important;
    }
  </style>
  <!-- Scripts -->
  {{-- <script src="{{ mix('js/app.js') }}" defer></script> --}}
</head>

<body class="font-sans antialiased" style="background-color: #fff;">
  <div class="wrapper">

    <div class="main-header shadow-sm" data-background-color="white">
      <div class="nav-top">
        <!-- Just an image -->
        <nav class="navbar navbar-light" data-background-color="white">
          <div class="d-flex flex-row justify-content-center align-items-center">
            <div class="flex">
              <a href="/login/dashboard" class="text-black">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
                  <title>ionicons-v5-a</title>
                  <polyline points="244 400 100 256 244 112" style="fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:48px" />
                  <line x1="120" y1="256" x2="412" y2="256" style="fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:48px" />
                </svg>
              </a>
            </div>

            <div class="mt-2 ml-2">
              <h3>
                Sign Up
              </h3>
            </div>
          </div>
          <a class="navbar-brand" href="#">
            {{-- <img src="https://daftar-agen.com/img/logo-light.png" height="30" alt=""> --}}
            <img src="https://media-exp1.licdn.com/dms/image/C560BAQG9nKjKqaZHbQ/company-logo_200_200/0/1662637215084?e=2147483647&v=beta&t=OxVksn_sADSbiS1K1PDtfi74Of9NbImrzu6rO-nYRio" width="60" height="60" alt="logo aimi capital">

          </a>
        </nav>
      </div>
    </div>

    <div class="main-panel">
      <div class="container">{{$slot}}</div>
    </div>
    {{-- <footer class="footer">
      <div class="container">
        <div class="copyright ml-auto">
          AIMI FIS - BY IT DIVISION 2022
          <!--{{date('Y')}}, made with <i class="fa fa-heart heart text-danger"></i> by <a
            href="http://www.themekita.com">ThemeKita</a>-->
        </div>
      </div>
    </footer> --}}
  </div>


  <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>

  <!-- jQuery UI -->
  <script src="{{ asset('assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
  <script src="{{ asset('assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}"></script>


  <!-- jQuery Scrollbar -->
  <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
  <script src="{{ asset('assets/js/atlantis2.min.js') }}"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  @stack('scripts')
  <script>
    $(document).ready(function(value) {
      window.livewire.on('showAlert', ({msg, redirect=false, path='/'}) => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: msg,
                timer: 2000,
                showCancelButton: false,
                showConfirmButton: false
            })

            if (redirect) {
                setTimeout(() => {
                    window.location.href=path
                }, 3000);
            }
      });
      
      window.livewire.on('showAlertError', (data) => {
        console.log(data,'data')
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: data.msg,
              timer: 2000,
              showCancelButton: false,
              showConfirmButton: false
          })
      });
    })
  </script>
  @livewireScripts
</body>

</html>