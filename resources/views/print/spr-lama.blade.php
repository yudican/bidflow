<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>SALES ORDER RETURNS</title>
</head>

<body>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>SALES ORDER RETURNS</span></strong></p>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
  <table style="border-collapse:collapse;border:none;" width="100%">
    <tbody>
      <tr>
        <td style="width: 219.5pt;border: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-family:"Times New Roman",serif;'>From Customer</span></strong></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>Details:</span></strong></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
      </tr>
      <tr>
        <td style="width: 219.5pt;border-top: none;border-bottom: none;border-left: none;border-image: initial;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$data->company_account_name}}</span></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>SO Return No</span></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>: {{$data->nomor_sr}}</span></p>
        </td>
      </tr>
      <tr>
        <td style="width: 219.5pt;border-top: none;border-bottom: none;border-left: none;border-image: initial;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>Vendor</span></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>: {{$data->vendor}}</span></p>
        </td>
      </tr>
      <tr>
        <td style="width: 219.5pt;border-top: none;border-bottom: none;border-left: none;border-image: initial;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>To Warehouse</span></strong></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>Cretated date</span></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>: {{date('d M Y',strtotime($data->created_at))}}</span></p>
        </td>
      </tr>
      <tr>
        <td style="width: 219.5pt;border-top: none;border-bottom: none;border-left: none;border-image: initial;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$data->warehouse_name}}</span></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>Received Date</span></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>: {{date('d M Y',strtotime($data->received_date))}}</span></p>
        </td>
      </tr>
      <tr>
        <td style="width: 219.5pt;border-top: none;border-bottom: none;border-left: none;border-image: initial;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
        <td style="width: 106.3pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>Notes</span></p>
        </td>
        <td style="width: 141.7pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>: {{$data->note ?? '-'}}</span></p>
        </td>
      </tr>
    </tbody>
  </table>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;text-align:center;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
  <table style="border-collapse:collapse;border:none;" width="100%">
    <tbody>
      <tr>
        <td style="width: 31.45pt;border: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>No.</span></strong></p>
        </td>
        <td style="width: 103pt;border-top: 1pt solid windowtext;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-image: initial;border-left: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>SKU</span></strong></p>
        </td>
        <td style="width: 297.65pt;border-top: 1pt solid windowtext;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-image: initial;border-left: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>Item Name</span></strong></p>
        </td>
        <td style="width: 35.4pt;border-top: 1pt solid windowtext;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-image: initial;border-left: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>Qty</span></strong></p>
        </td>
      </tr>
      @foreach ($data->items as $key => $item)
      <tr>
        <td style="width: 31.45pt;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-left: 1pt solid windowtext;border-image: initial;border-top: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$key+1}}.</span></p>
        </td>
        <td style="width: 103pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$item->sku}}</span></p>
        </td>
        <td style="width: 297.65pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$item->product->name}}</span></p>
        </td>
        <td style="width: 35.4pt;border-top: none;border-left: none;border-bottom: 1pt solid windowtext;border-right: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><span style='font-size:19px;font-family:"Times New Roman",serif;'>{{$item->qty}}</span></p>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
  <table style="border-collapse:collapse;border:none;" width="100%">
    <tbody>
      <tr>
        <td style="width: 155.8pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;line-height:150%;'><strong><span style='font-size:19px;line-height:150%;font-family:"Times New Roman",serif;'>Dikirim Oleh:</span></strong></p>
        </td>
        <td rowspan="2" style="width: 155.85pt;border: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;line-height:150%;'><strong><span style='font-size:19px;line-height:150%;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
        <td style="width: 155.85pt;border-top: none;border-right: none;border-left: none;border-image: initial;border-bottom: 1pt solid windowtext;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;line-height:150%;'><strong><span style='font-size:19px;line-height:150%;font-family:"Times New Roman",serif;'>Diterima Oleh:</span></strong></p>
        </td>
      </tr>
      <tr>
        <td style="width: 155.8pt;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-left: 1pt solid windowtext;border-image: initial;border-top: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
        <td style="width: 155.85pt;border-right: 1pt solid windowtext;border-bottom: 1pt solid windowtext;border-left: 1pt solid windowtext;border-image: initial;border-top: none;padding: 0cm 5.4pt;vertical-align: top;">
          <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:  "Times New Roman",serif;'>&nbsp;</span></strong></p>
        </td>
      </tr>
    </tbody>
  </table>
  <p style='margin:0cm;font-size:16px;font-family:"Calibri",sans-serif;'><strong><span style='font-size:19px;font-family:"Times New Roman",serif;'>&nbsp;</span></strong></p>
</body>

</html>