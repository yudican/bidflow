<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Sales Return</title>
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
    <br><br>
    <center>
        <h1><span style="font-size:32px;">SALES RETURN</span></h1>
    </center>
    <table width="100%">
        <tr>
            <td width="50%">FROM : <br>PT. ANUGRAH INOVASI MAKMUR INDONESIA<br>Jakarta<br><br> To Warehouse : <br>{{$data->warehouse_name}}</td>
            <td width="50%">Details :<br>
                <table width="100%" border="1" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>SO Return No</td>
                        <td>{{$data->nomor_sr}}</td>
                    </tr>
                    <tr>
                        <td>Vendor</td>
                        <td>{{$data->vendor}}</td>
                    </tr>
                    <tr>
                        <td>Cretated Date</td>
                        <td>{{date('d M Y',strtotime($data->created_at))}}</td>
                    </tr>
                    <tr>
                        <td>Received Date</td>
                        <td>{{date('d M Y',strtotime($data->received_date))}}</td>
                    </tr>
                    <tr>
                        <td>Notes</td>
                        <td>{{$data->note ?? '-'}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-top:20px;border-bottom: 1px solid #000;">
        <tr style="background-color: #3D4043;">
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;border:0px solid #3D4043;" bgcolor="#3D4043">No</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">SKU</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Item Name</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Qty</th>
        </tr>
        @foreach ($data->items as $key => $item)
        <tr>
            <td align="center">
                {{$key+1}}
            </td>
            <td align="left">
                {{$item->sku}}
            </td>
            <td align="left">
                {{$item->product->name}}
            </td>
            <td align="center">
                {{ $item->qty }}
            </td>
        </tr>
        @endforeach
    </table>
    <br><br>
    <table width="100%">
        <tr>
            <td width="25%">Gudang</td>
            <td width="25%">Admin</td>
            <td width="25%">Pengemudi</td>
            <td width="25%">Penerima</td>
        </tr>
    </table>

    <br><br><br><br><br><br>

</body>
</html> 