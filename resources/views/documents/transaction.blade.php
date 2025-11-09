<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/atlantis.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <title>Report Transaction</title>
  </head>
  <body>
    <div class="container">
        <div class="row">
            <center>Report Transaction</center>
            <br>
            <table class="table table-striped mt-5" border='1'>
                <thead>
                  <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Trx Id</th>
                    <th scope="col">User Id</th>
                    <th scope="col">Name</th>
                    <th scope="col">Nominal</th>
                  </tr>
                </thead>
                <tbody>
                    @php
                        $no = 0;
                    @endphp
                    @foreach ($data as $data)
                    <tr>
                        <th scope="row">{{ ++$no }}</th>
                        <td>{{ $data->id_transaksi }}</td>
                        <td>{{ $data->user_id }}</td>
                        <td>{{ $data->name }}</td>
                        <td>{{ $data->nominal }}</td>
                      </tr>
                    @endforeach 
                </tbody>
              </table>
        </div>
    </div>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  </body>
</html>