{{--
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <title>LABEL</title>
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
      font-size: 14px;
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
      font-size: 14px;
    }
  </style>
</head>

<body>
  @foreach ($transactions as $transaction)
  <div style="page-break-before: always;">
    <div>
      <div style="clear:both;">
        <p style="margin-top:0pt; margin-bottom:0pt;">&nbsp;</p>
      </div>
      <h2 style="margin-top:0pt; margin-bottom:0pt; text-align:center;"><span style="font-size: 32px;">LABEL</span></h2>
    </div>
    <br><br>

    <table width="100%">
      <tr>
        <td width="50%">FROM : <br> FLIMTY <br><br> TO CUSTOMER : <br>{{ @$transaction->addressUser?->user?->name }}<br><br> DELIVER TO : <br>{{ @$transaction->addressUser?->alamat_detail }}</td>
        <td width="50%">Details :<br>
          <table width="100%" border="1" cellpadding="0" cellspacing="0">
            <tr>
              <td>Order Number No.</td>
              <td>{{$transaction->id_transaksi}}</td>
            </tr>
            <tr>
              <td>Notes</td>
              <td>{{$transaction->note}}</td>
            </tr>
            <tr>
              <td>Nomor Resi</td>
              <td>{{$transaction->resi}}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table width="100%" style="margin-top:20px;border-bottom: 1px solid #000;">
      <tr style="background-color: #3D4043;">
        <th align="center" style="color: #fff;padding-top: 20px;padding-bottom:20px;border:0px solid #3D4043;" bgcolor="#3D4043">No</th>
        <th align="left" style="color: #fff;padding-top: 20px;padding-bottom:20px;">SKU</th>
        <th align="left" style="color: #fff;padding-top: 20px;padding-bottom:20px;">Item</th>
        <th align="center" style="color: #fff;padding-top: 20px;padding-bottom:20px;">Qty</th>
      </tr>
      @foreach ($transaction->transactionDetail as $key => $prod)
      <tr>
        <td align="center">
          {{$key+1}}
        </td>
        <td align="left">
          {{$prod->productVariant?->sku}}
        </td>
        <td align="left">
          {{ $prod->product_name }}
        </td>
        <td align="center">
          {{ $prod->qty }}
        </td>
      </tr>
      @endforeach
    </table>
    <br>

    <br>
    <p style="color:red;font-size:14px;">
      *TIDAK MELAYANI KOMPLAIN SETELAH DOKUMEN DITANDATANGANI , LAKUKAN VIDEO UNBOXING DENGAN JELAS SEBELUM PAKET DIBUKA SAMPAI SELESAI DAN PERIKSA KELENGKAPAN ISI. TANPA VIDEO UNBOXING KOMPLAIN ATAU RETUR RUSAK ATAU KURANG TIDAK DI TERIMA
    </p>
  </div>
  <hr>
  @endforeach
</body>

</html> --}}

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Label</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .label-container {
            border: 2px solid black;
            width: 1000px;
            padding: 20px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
        }

        .header img {
            height: 50px;
        }

        .header .right {
            text-align: right;
        }

        .barcode {
            text-align: center;
            margin: 20px 0;
            border-bottom: 2px solid black;
        }

        .barcode img {
            height: 50px;
        }

        .info {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
            margin-bottom: 10px;
            padding-top: 10px;
        }

        .info_resi {
            display: flex;
            justify-content: center;
            align-items: center;
            border-bottom: 2px solid black;
            padding-bottom: 10px;
        }


        .info .left,
        .info .right {
            width: 50%;
        }

        .section {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid black;
        }

        .section h3 {
            margin: 5px 0;
        }

        .section p,
        .left p,
        .right p,
        .barcode p {
            margin: 2px 0;
        }
    </style>
</head>

<body>
    @foreach ($transactions as $key => $transaction)
        <div class="label-container" style="{{ $key > 0 ? 'page-break-before: always;' : '' }}">
            <div class="header">
                <img src="{{ $transaction->shippingType->shipping_logo }}" style="height: 40px;" alt="ID Express">
                <div class="right">
                    {{-- <img src="{{ @$transaction->data_brand['brand_logo'] }}" style="height: 50px;" alt="Flimty"> --}}
                    <img src="https://s3.flimty.co/upload/master/brand/6ZBtWv7aZ3nLrjwWWdUvjRFArteSo0EdjpdgZjqQ.png"
                        style="height: 50px;" alt="Flimty">
                </div>
            </div>
            <div class="info">
                <div class="left">
                    <p style="font-size: 20px;"><b>{{ $transaction->id_transaksi }}</b></p>
                    <p>Jenis Layanan: {{ $transaction->shippingType->shipping_type_code }}</p>
                    <p>Asuransi: Rp 0</p>
                </div>
                <div class="right">
                    <p>Berat: {{ $transaction->weight ?? '-' }} Kg</p>
                    <p>Biaya Pengiriman: Rp {{ number_format($transaction->ongkir) }}</p>
                </div>
            </div>
            <div class="barcode">
                {!! DNS1D::getBarcodeSVG($transaction->resi ?? 'Belum Input Resi', 'C128', 4, 170, '#000', false) !!}
                <p>{{ $transaction->resi ?? 'Belum Input Resi' }}</p>
            </div>
            <div class="info_resi">
                <div style="text-align: center;">
                    <h1 style="margin: 0;">Non-COD</h1>
                    <p style="margin: 0;">Ongkir sudah dibayar lunas dan tidak perlu dibayar ke kurir</p>
                </div>
            </div>
            <div class="section">
                <table width="100%">
                    <tr>
                        <td width="60%" valign="top">
                            <h3>PENERIMA</h3>
                            <p>{{ @$transaction->data_user_address['nama'] }}</p>
                            <p>{{ formatPhone(@$transaction->data_user_address['telepon'], '628') }}</p>
                            <p>{{ @$transaction->data_user_address['alamat_detail'] }}</p>
                        </td>
                        <td width="30%" valign="top">
                            <h3>PENGIRIM</h3>
                            <p>{{ @$transaction->data_brand['brand_name'] }}</p>
                            <p>{{ formatPhone(@$transaction->data_brand['brand_phone'], '628') }}</p>
                            {{-- <p>{{ @$transaction->shipperWarehouse?->location}}</p> --}}
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>CATATAN</h3>
                @if (isset($transaction->data_user_address['catatan']))
                    <p>{{ $transaction->data_user_address['catatan'] }}</p>
                @endif
                <p>{{ $transaction->note }}</p>
            </div>
            <div class="section" style="border-bottom: 2px solid white;">
                <table width="100%">
                    <tr>
                        <th align="left">Nama Produk</th>
                        <th align="left">SKU</th>
                        <th align="left">QTY</th>
                    </tr>
                    @foreach ($transaction->transactionDetail as $key => $trans)
                        <tr>
                            <td>{{ isset($trans->data_product['product_name']) ? $trans->data_product['product_name'] : '-' }}
                            </td>
                            <td>{{ isset($trans->data_product['product_sku']) ? $trans->data_product['product_sku'] : '-' }}
                            </td>
                            <td>{{ $trans?->qty }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endforeach
</body>

</html>
