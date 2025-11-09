<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <title>{{$title}}</title>
  <style>
    @font-face {
      font-family: "Poppins";
      font-weight: normal;
      font-style: normal;
      font-variant: normal;
      /* src: url("font url"); */
    }

    * {
      font-family: "Poppins", sans-serif;
      font-size: 12px;
      font-weight: 600;
    }

    footer {
      position: fixed;
      bottom: -30px;
      left: 0px;
      right: 0px;
      height: 50px;

      /** Extra personal styles **/
      color: #000;
      padding-left: 10px;
    }

    table tr td,
    table tr th {
      font-size: 12px;
    }
  </style>
</head>


<body onload="window.print()">
  @foreach ($transactions as $key => $data)
  <div style="{{$key > 0 ?'page-break-before: always;' : ''}} position: relative;">
    @if ($data->logPrint()->count() > 1)
    <p style="position: absolute;right:10px;top:10px;">COPY {{$data->logPrint()->count()-1}}</p>
    @endif
    <table width="100%">
      <tr>
        <td width="90%" valign="top">
          <img src="{{ asset('assets/img/logo.png') }}" style="height: 80px" alt="" />
        </td>
        <td width="10%">
          @if ($data->payment_qr_url)
          <p style="margin-top: 30px;margin-bottom:0;text-align:center;">Scan QR Code</p>
          <img src="{{$data->payment_qr_url}}" width="150px" alt="QRIS">
          @endif
        </td>
      </tr>
      <tr>
        <td width="80%" align="right">
        </td>
        <td width="20%" align="right">
          <h2>INVOICE</h2>
        </td>
      </tr>
    </table>
    <hr />
    <table width="100%" style="margin-top: 20px">
      <tr>
        <td width="20%">No. Invoice</td>
        <td width="40%">: {{$data->id_transaksi}}</td>
        <td width="10%" valign="top"><b>Alamat</b></td>
        <td width="30%">{{$data->data_user_address['alamat_detail']}}</td>
      </tr>
      <tr>
        <td>Email</td>
        <td>: {{$data->user ? $data->user->email : '-'}}</td>
      </tr>
      <tr>
        <td>Tanggal</td>
        <td>: {{formatTanggalIndonesia($data->created_at)}}</td>
      </tr>
      <tr>
        <td>Status</td>
        @if (in_array($data->status, [1]))
        <td>: <span style="color: #fbbc05">Unpaid</span></td>
        @elseif (in_array($data->status, [2]))
        <td>: <span style="color: #fbbc05">Checking</span></td>
        @elseif (in_array($data->status, [3,7]))
        <td>: <span style="color: #2e8e60">Paid</span></td>
        @elseif (in_array($data->status, [4,5,6]))
        <td>: <span style="color: #f85640">Canceled</span></td>
        @else
        <td>: <span style="color: #fbbc05">Unpaid</span></td>
        @endif
      </tr>
      @if ($data->paymentMethod)
      <tr>
        <td>Metode Pembayaran</td>
        <td>: {{$data->paymentMethod->nama_bank}}</td>
      </tr>
      @if ($data->paymentMethod->payment_type == 'Otomatis')
      @if($data->paymentMethod->payment_channel == 'bank_transfer' && $data->status != 3)
      <tr>
        <td>Nomor Virtual Akun</td>
        <td>: {{$data->payment_va_number ?? '-'}}</td>
      </tr>
      @endif
      @else
      <tr>
        <td>Nomor Rekening</td>
        <td>
          :
          {{$data->paymentMethod->nomor_rekening_bank}}
          ({{$data->paymentMethod->nama_rekening_bank}})
        </td>
      </tr>
      @endif
      @endif
    </table>

    <table width="100%" style="margin-top: 20px; border-bottom: 1px solid #000">
      <tr style="background-color: #3d4043">
        <th style="color: #fff; padding: 1rem; border: 0px solid #3d4043" bgcolor="#3D4043">
          No
        </th>
        <th style="color: #fff; padding: 1rem">Nama Produk</th>
        <th style="color: #fff; padding: 1rem">Qty</th>
        <th style="color: #fff; padding: 1rem">Harga Satuan</th>
        <th style="color: #fff; padding: 1rem">Subtotal</th>
      </tr>
      @foreach ($data->transactionDetail as $key => $item)
      <tr>
        <td align="center">
          {{ $key + 1 }}
        </td>
        <td align="left">
          {{$item->product->name}}
          @if ($item->variant)
          <span>Variant: {{$item->variant->name}}</span>
          @endif
        </td>
        <td align="center">
          {{$item->qty}}
        </td>
        <td align="center">
          <strong> Rp {{number_format($item->price)}} </strong>
        </td>
        <td align="center">
          <strong> Rp {{number_format($item->subtotal)}} </strong>
        </td>
      </tr>
      @endforeach
    </table>
    <table width="100%" style="
          margin-top: 20px;
          border-bottom: 1px solid #000;
          padding-bottom: 20px;
        ">
      <tr>
        <td width="40%">Metode Pengiriman</td>
        <td width="60%">
          :
          {{@$data->shippingType->shipping_type_name}}
          ({{@$data->shippingType->shipping_duration}})
        </td>
      </tr>
      <tr>
        <td width="40%">Biaya Pengiriman</td>
        <td width="60%">
          : Rp. {{number_format(@$data->shippingType->shipping_price)}}
        </td>
      </tr>
      @if (@$data->shippingType->shipping_discount > 0)
      <tr>
        <td width="40%">Diskon Pengiriman</td>
        <td width="60%">
          : Rp. {{number_format($data->shippingType->shipping_discount)}}
        </td>
      </tr>
      @endif
      @if ($data->diskon > 0)
      <tr>
        <td width="40%">Discount</td>
        <td width="60%">
          : Rp {{number_format($data->diskon)}}
          @if ($data->voucher)
          <span> | {{$data->voucher->voucher_code}}</span>
          @endif
        </td>
      </tr>
      @endif
      @if ($data->payment_unique_code > 0)
      <tr>
        <td width="40%">Unique Code</td>
        <td width="60%">: {{$data->payment_unique_code}}</td>
      </tr>
      @endif
      <tr>
        <td width="40%"><span>Subtotal</span></td>
        <td width="60%">
          : <span>Rp {{number_format($data->subtotal)}}</span>
        </td>
      </tr>
      {{-- @if ($data->admin_fee > 0)
      <tr>
        <td width="40%"><span>Biaya Admin</span></td>
        <td width="60%">
          : <span>Rp {{number_format($data->admin_fee)}}</span>
        </td>
      </tr>
      @endif --}}
      <tr>
        <td width="40%"><strong>Total Pembayaran</strong></td>
        <td width="60%">
          : <strong>Rp {{number_format($data->nominal)}}</strong>
        </td>
      </tr>
      {{-- <tr>
        <td width="40%"><strong>Pajak 11%</strong></td>
        <td width="60%">
          : <strong>Rp {{number_format($data->ppn)}}</strong>
        </td>
      </tr>
      <tr>
        <td width="40%"><strong>Total + PPN</strong></td>
        <td width="60%">
          : <strong>Rp {{number_format($data->total)}}</strong>
        </td>
      </tr> --}}
    </table>

    <p style="font-size: 10px">
      Jika Anda memiliki pertanyaan mengenai faktur ini, gunakan informasi
      <br />
      kontak berikut di bawah ini: <br />
      {{$data->brand->email}} | www.flimty.com
    </p>
    {{-- <footer style="padding-bottom:20px;">
      <p style="font-size: 10px; width: 100%;">
        {{$data->brand->alamat}}
      </p>
    </footer> --}}
  </div>
  @endforeach

</body>

</html>