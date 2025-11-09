<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <title>Penyesuaian Stok</title>
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
      <span style="font-size: 32px">Penyesuaian Stok</span>
    </h2>
  </div>
  <br /><br />
  <table width="100%">
    <tr>
      <td></td>
    </tr>
    <tr>
      <td width="50%"></td>
      <td width="50%">
        <strong>Details</strong> :<br />
        <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr>
            <td>Adjustment ID</td>
            <td>{{(empty($lead->so_ethix)?'-':$lead->so_ethix)}}</td>
          </tr>
          <tr>
            <td>No. Preference</td>
            <td>{{(@$lead->preference_number)?@$lead->preference_number:'-'}}</td>
          </tr>
          <tr>
            <td>Created By</td>
            <td>{{(@$lead->created_by_name)?@$lead->created_by_name:'-'}}</td>
          </tr>
          <tr>
            <td>Created On</td>
            <td>{{ @$lead->created_on ? \Carbon\Carbon::parse(@$lead->created_on)->format('d-m-Y') : '-' }}</td>
          </tr>
          <tr>
            <td>Destinasi Warehouse</td>
            <td>{{(@$lead->warehouse_name)?@$lead->warehouse_name:'-'}}</td>
          </tr>
          <tr>
            <td>Destinasi BIN</td>
            <td>{{(@$lead->bin_name)?@$lead->bin_name:'-'}}</td>
          </tr>
          <tr>
            <td>Status</td>
            <td style="text-transform:capitalize">
                @if(@$lead->status)
                    @switch(@$lead->status)
                        @case('done')
                            Success
                            @break
                        @case('waiting')
                            Waiting Approval
                            @break
                        @default
                            {{ ucfirst(@$lead->status) }}
                    @endswitch
                @else
                    -
                @endif
            </td>
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
        PRODUK
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        QTY SEBELUMNYA
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        QTY PENYESUAIAN
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        UOM
      </th>
    </tr>


    @php $no=1; $subtotal=0; $diskon=0; $tax_amount=0; $ppn=0; @endphp
    @foreach ($productneeds as $prod)

    <tr>
      <td style="width: 3.4139%; text-align: center">{{ $no++ }}</td>
      <td style="width: 18.7137%">{{ $prod?->sku ?? $prod?->product?->sku }}<br /></td>
      <td style="width: 27.8037%">{{ @$prod->product_name }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->stock_awal }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->qty }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->u_of_m }}</td>
    </tr>
    @endforeach
  </table>
  <br />
  <p>*Notes: {{(@$lead->note)?@$lead->note:'-'}}</p>

  
  <br />
  <table width="100%">
    <tbody>
      <tr>
        <td width="25%">Dibuat oleh,</td>
        <td width="25%"></td>
        <td width="25%"></td>
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
          {{(@$lead->created_by_name)?@$lead->created_by_name:'-'}}
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
         
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
          
        </td>
        <td style="width: 12.5%"><br /></td>
        <td style="width: 12.5%">
          <hr />
          <br />
          {{(@$lead->allocated_by_name)?@$lead->allocated_by_name:'-'}}
        </td>
        <td style="width: 12.5%"><br /></td>
      </tr>
    </tbody>
  </table>
</body>

</html>