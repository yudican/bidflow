<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <title>Invoice</title>
  <style>
    @font-face {
      font-family: 'Poppins';
      font-weight: normal;
      font-style: normal;
      font-variant: normal;
      /* src: url("font url"); */
    }

    * {
      font-family: 'Poppins', sans-serif;
      font-size: 10px;
    }

    footer {
      position: fixed;
      bottom: 60px;
      left: 0px;
      right: 0px;
      height: 50px;

      /** Extra personal styles **/
      color: #000;
      padding-left: 10px;
    }

    table tr td,
    table tr th {
      font-size: 10px;
    }

    @media all {
      .page-break {
        display: none;
      }
    }

    @media print {
      .page-break {
        display: block;
        page-break-before: always;
      }
    }
  </style>
</head>

<body>
  @foreach ($transactions as $key => $data)
  <div style="margin-bottom: 50px;">
    <hr>
    <table width="100%">
      <tr>
        <td width="40%"><img src="{{asset('assets/img/logo-flimty.png')}}" style="height:80px;" alt=""></td>
        <td><img src="{{asset('assets/img/barcode1.gif')}}" style="height:90px;" alt=""></td>
        <td><img src="{{asset('assets/img/JNE.png')}}" style="height:90px;" alt=""></td>
      </tr>
    </table>
    <hr>
    <b>Receiver : </b>{{$data->user->name}} , {{$data->addressUser ? $data->addressUser->alamat_detail : '-'}}
    <hr>
    <table>
      <tr>
        <td width="60%"><b>Transaction ID :</b> {{$data->id_transaksi}}</td>
        <td><b>Resi :</b> {{$data->resi}}</td>
      </tr>
      <tr>
        <td width="60%"><b>Telp :</b> {{$data->addressUser ? $data->addressUser->telepon : '-'}}</td>
        <td><b>Created on :</b> {{$data->created_at}}</td>
      </tr>
      <tr>
        <td width="60%"><b>Delivery :</b> Pick Up</td>
        <td></td>
      </tr>
    </table>
    <hr>

    <table>
      <tr>
        <td width="60%"><img src="{{asset('assets/img/frame.png')}}" style="height:80px;" alt=""></td>
        <td><img src="{{asset('assets/img/barcode2.gif')}}" style="height:90px;" alt=""></td>
      </tr>
    </table>

    <hr>
    <table width="100%" style="margin-top:20px;border-bottom: 1px solid #000;">
      <tr style="background-color: #3D4043;">
        <th style="color: #fff;padding-top: 20px;padding-bottom:20px;border:0px solid #3D4043;" bgcolor="#3D4043">No</th>
        <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Item</th>
        <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Qty</th>
        <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Price</th>
        <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Subtotal</th>
      </tr>
      @php
      $resqty = 0;
      $restotal = 0;
      @endphp
      @foreach ($data->transactionDetail as $key => $item)
      @php
      $resqty += $item->qty;
      $restotal += $item->subtotal;
      @endphp
      <tr>
        <td align="center">
          {{$key+1}}
        </td>
        <td align="center">
          {{$item->product->name}}
        </td>
        <td align="center">
          {{$item->qty}}
        </td>
        <td align="center">
          <strong>
            Rp {{number_format($item->price)}}
          </strong>
        </td>
        <td align="center">
          <strong>
            Rp {{number_format($item->subtotal)}}
          </strong>
        </td>
      </tr>
      @endforeach
      <tr>
        <th colspan="2">TOTAL</th>
        <th>
          <?php echo $resqty; ?>
        </th>
        <th></th>
        <th><strong>Rp. {{ number_format($restotal) }}</strong></th>
      </tr>
    </table>
  </div>
  @if ($key %3 ==0)
  <div class="page-break"></div>
  @endif
  @endforeach
</body>

</html>