<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <title>Sales Order</title>
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
  <div>
    <div style="clear: both">
      <p style="margin-top: 0pt; margin-bottom: 0pt">&nbsp;</p>
    </div>
    <h2 style="margin-top: 0pt; margin-bottom: 0pt; text-align: center">
      <span style="font-size: 32px">SALES ORDER</span>
    </h2>
  </div>
  <br /><br />
  <table width="100%">
    <tr>
      <td>PT. ANUGRAH INOVASI MAKMUR INDONESIA</td>
    </tr>
    <tr>
      <td width="50%">
        <strong>Customer</strong> :
        <br />{{(empty($lead->contact_name)?'-':$lead->contact_name)
        }}<br /><br />
        <strong>Delivery Address</strong> : <br />{{ @$mainaddress->alamat }}
      </td>
      <td width="50%">
        <strong>Details</strong> :<br />
        <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr>
            <td>Sales Order No.</td>
            <td>{{(empty($lead->order_number)?'-':$lead->order_number)}}</td>
          </tr>
          <tr>
            <td>Reference No.</td>
            <td>{{(@$lead->preference_number)?@$lead->preference_number:'-'}}</td>
          </tr>
          <tr>
            <td>Approved</td>
            <td>-</td>
          </tr>
          <tr>
            <td>Delivery Date</td>
            <td>
              {{ @$lead->orderShipping? date('l, d F Y', strtotime(@$lead->orderShipping->delivery_date)) :'-' }}
            </td>
          </tr>
          <tr>
            <td>Salesperson</td>
            <td>
              {{(empty($lead->sales_name)?'-':$lead->sales_name)}}
            </td>
          </tr>
          <tr>
            <td>Term of Payment</td>
            <td>
              Konsinyasi
            </td>
          </tr>
          <tr>
            <td>Notes</td>
            <td>{{(empty($lead->notes)?'-':$lead->notes)}}</td>
          </tr>
          <tr>
            <td>Currency</td>
            <td>IDR</td>
          </tr>
          <tr>
            <td>Due Date</td>
            <td>{{(@$lead->due_date)?@$lead->due_date:'-'}}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <br />
  <table style="width: 100%" border="1" cellpadding="0" cellspacing="0">
    <tr style="background-color: #3d4043">
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">No</th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        SKU
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        Item Name
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        QTY
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        PRICE
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        DISCOUNT (%)
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        PRICE NEGO
      </th>
      <th width() style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        AMOUNT
      </th>
    </tr>
    @php $no=1; $subtotal=0; $diskon=0; $tax_amount=0; $ppn=0; @endphp
    @foreach ($productneeds as $prod)
    @php
    $subtotal += $prod->price_nego;
    $diskon += $prod->discount_amount;
    $ppn += $prod->tax_amount;
    @endphp
    <tr>
      <td style="width: 3.4139%; text-align: center">{{ $no++ }}</td>
      <td style="width: 18.7137%">{{ $prod?->sku ?? $prod?->product?->sku }}<br /></td>
      <td style="width: 27.8037%">{{ @$prod->product_name }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->qty }}</td>
      <td style="width: 10%; text-align: right">
        Rp {{ number_format(($prod->price_nego/$prod->qty),0,',','.') }}
      </td>
      <td style="width: 7%; text-align: right">
        {{$prod->discount_percentage }}
      </td>
      <td style="width: 10%; text-align: right">
        Rp {{ number_format(($prod->price_nego),0,',','.') }}
      </td>
      <td style="width: 23.5528%; text-align: right">
        Rp {{ number_format($prod->total,0,',','.') }}
      </td>
    </tr>
    @endforeach
  </table>
  <br />
  <table style="width: 26%; margin-left: calc(74%)">
    <tbody>
      <tr>
        <td style="width: 62.5044%">Subtotal</td>
        <td style="width: 36.8587%; text-align: right">
          Rp {{ number_format($subtotal,0,',','.')}}
        </td>
      </tr>
      <tr>
        <td style="width: 62.5044%">Discount</td>
        <td style="width: 36.8587%; text-align: right">
          Rp {{ number_format($diskon,0,',','.')}}
        </td>
      </tr>
      <tr>
        <td style="width: 62.5044%">DPP</td>
        <td style="width: 36.8587%; text-align: right">
          Rp {{ number_format($subtotal,0,',','.')}}
        </td>
      </tr>
      <tr>
        <td style="width: 62.5044%">PPN</td>
        <td style="width: 36.8587%; text-align: right">
          Rp {{ number_format($ppn,0,',','.')}}
        </td>
      </tr>
      <tr>
        <td style="width: 62.5044%">Total</td>
        <td style="width: 36.8587%; text-align: right">
          Rp {{ number_format($subtotal+$ppn,0,',','.')}}
        </td>
      </tr>
    </tbody>
  </table>
  <br />
  <table width="100%">
    <tbody>
      <tr>
        <td width="25%">Hormat Kami,</td>
        <td width="25%">Disetujui,</td>
        <td width="25%">Dikirim,</td>
        <td width="25%">Diterima,</td>
      </tr>
    </tbody>
  </table>
  <p><br /></p>
  <p style="
        margin-top: 0pt;
        margin-bottom: 0pt;
        line-height: 150%;
        font-size: 11pt;
      ">
    <br />
  </p>
  <table style="width: 100%">
    <tbody>
      <tr>
        <td style="width: 12.5%">
          <hr />
          <br />
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
          <hr />
          <br />
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
          <hr />
          <br />
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
          <hr />
          <br />
        </td>
        <td style="width: 12.5%"><br /></td>
      </tr>
    </tbody>
  </table>
  @if (@$lead->inventoryProductStock?->inventory_type == 'konsinyasi')
  <table width="100%">
    <tr>
      <td style="text-align: right;">
        <p>
          *Catatan Sistem : {{ ($lead->inventoryProductStock?->transfer_category == 'old')?'Data Lama':'Data Baru' }}
        </p>
      </td>
    </tr>
  </table>
  @endif
</body>

</html>