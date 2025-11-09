<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <title>TRANSFER PRODUCT</title>
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
      <span style="font-size: 32px">TRANSFER</span>
    </h2>
  </div>
  <br /><br />
  <table width="100%">
    <tr>
      <td>PT. ANUGRAH INOVASI MAKMUR INDONESIA</td>
    </tr>
    <tr>
      <td width="50%">

      </td>
      <td width="50%">
        <strong>Details</strong> :<br />
        <table width="100%" border="1" cellpadding="0" cellspacing="0">
          <tr>
            <td>Transfer No.</td>
            <td>{{(empty($transfer->so_ethix)?'-':$transfer->so_ethix)}}</td>
          </tr>

          <tr>
            <td>Notes</td>
            <td>{{(empty($transfer->note)?'-':$transfer->note)}}</td>
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
        Item Name
      </th>

      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        From WH
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        TO WH
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        SKU
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        UofM
      </th>
      <th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
        QTY Diterima
      </th>

    </tr>
    @foreach ($transfer->historyAllocations as $key => $prod)
    <tr>
      <td style="width: 3.4139%; text-align: center">{{ $key+1 }}</td>
      <td style="width: 27.8037%">{{ $prod?->product?->name }}</td>
      <td style="width: 18.7137%">{{ $prod?->fromWarehouse?->name }}<br /></td>
      <td style="width: 18.7137%">{{ $prod?->toWarehouse?->name }}<br /></td>
      <td style="width: 5%; text-align: center">{{ $prod->sku }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->u_of_m }}</td>
      <td style="width: 5%; text-align: center">{{ $prod->quantity }}</td>
    </tr>
    @endforeach
  </table>
  <br />

  <br />
  <table width="100%">
    <tbody>
      <tr>
        <td width="50%">Hormat Kami,</td>
        <td width="50%">Disetujui,</td>
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
        <td style="width: 25%">
          <hr />
          <br />
        </td>
        <td style="width: 25%"><br /></td>
        <td style="width: 25%">
          <hr />
          <br />
        </td>
        <td style="width: 25%"><br /></td>
      </tr>
    </tbody>
  </table>
</body>

</html>