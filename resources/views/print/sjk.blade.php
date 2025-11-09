<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Surat Jalan</title>
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
    <div>
        <div style="clear:both;">
            <p style="margin-top:0pt; margin-bottom:0pt;">&nbsp;</p>
        </div>
        <h2 style="margin-top:0pt; margin-bottom:0pt; text-align:center;"><span style="font-size: 32px;">SURAT JALAN</span></h2>
    </div>
    <br><br>

    <table width="100%">
        <tr>
            <td width="50%">FROM : <br> {{$lead->company_name}}<br>Jakarta<br><br> TO CUSTOMER : <br>{{ $lead->contact_name }}<br><br> DELIVER TO : <br>{{ @$mainaddress->alamat }}</td>
            <td width="50%">Details :<br>
                <table width="100%" border="1" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>Invoice Order No.</td>
                        <td>{{$delivery->invoice_number ?? $lead->invoice_number}}</td>
                    </tr>
                    <tr>
                        <td>No. Referensi</td>
                        <td>{{($lead->preference_number)?$lead->preference_number:'-'}}</td>
                    </tr>
                    @if($lead->status_pengiriman == 1)
                    <tr>
                        <td>Delivery Date</td>
                        <td>
                            {{ $lead->created_at? date('l, d F Y', strtotime($lead->created_at)) :'-' }}
                        </td>
                    </tr>
                    @else
                    @if (isset($delivery->delivery_date))
                    <tr>
                        <td>Delivery Date</td>
                        <td>{{ date('l, d F Y', strtotime($delivery->delivery_date))}}</td>
                    </tr>
                    @endif
                    @endif
                    <tr>
                        <td>Salesperson</td>
                        <td>{{$lead->sales_name}}</td>
                    </tr>
                    <tr>
                        <td>Notes</td>
                        <td>{{$lead->notes}}</td>
                    </tr>
                    <tr>
                        <td>Printed</td>
                        <td>{{($lead->print_status)?$lead->print_status:'-'}}</td>
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
        @foreach ($productneeds as $key => $prod)
        <tr>
            <td align="center">
                {{$key+1}}
            </td>
            <td align="left">
                {{ $prod?->sku ?? $prod?->product?->sku }}
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
    <table width="100%">
        <tr>
            <td width="25%">
                Gudang
                <div style="height: 50px; border-bottom: 1px solid black; width: 90%" />
            </td>
            <td width="25%">
                Admin
                <div style="height: 50px; border-bottom: 1px solid black; width: 90%" />
            </td>
            <td width="25%">
                Pengemudi
                <div style="height: 50px; border-bottom: 1px solid black; width: 90%" />
            </td>
            <td width="25%">
                Penerima
                <div style="height: 50px; border-bottom: 1px solid black; width: 90%" />
            </td>
        </tr>
    </table>

    <br>
    <p style="color:red;font-size:12px;">
        *TIDAK MELAYANI KOMPLAIN SETELAH DOKUMEN DITANDATANGANI , LAKUKAN VIDEO UNBOXING DENGAN JELAS SEBELUM PAKET DIBUKA SAMPAI SELESAI DAN PERIKSA KELENGKAPAN ISI. TANPA VIDEO UNBOXING KOMPLAIN ATAU RETUR RUSAK ATAU KURANG TIDAK DI TERIMA
    </p>
    <br>
    @if ($lead->inventoryProductStock?->inventory_type == 'konsinyasi')
    <p>
        *Catatan Sistem : {{ ($lead->inventoryProductStock?->transfer_category == 'old')?'Data Lama':'Data Baru' }}
    </p>
    @endif
</body>

</html>