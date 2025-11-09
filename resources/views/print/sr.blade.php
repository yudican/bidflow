<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <title>Sales Order</title>
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
            font-size: 12px;
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
    </style>
</head>

<body>
    <table width="100%">
        <tr>
            <td width="50%">&nbsp;</td>
            <td>
                <h2>ORDER RETURN</h2>
            </td>
            <!-- <td><img src="https://i.ibb.co/YDXbbPY/AIMI.png" style="height:90px;" alt=""></td> -->
        </tr>
    </table>
    <h2>PT. ANUGRAH INOVASI MAKMUR INDONESIA</h2>

    <table width="100%">
        <tr>
            <td width="5%">TELP <br> NPWP <br> Salesman <br> Gudang <br></td>
            <td width="25%"> : - <br> : 00.00.00<br> : {{(empty($lead->salesUser->name)?'-':$lead->salesUser->name)}}<br> : {{(empty($lead->warehouse->name)?'-':$lead->warehouse->name)}}<br></td>
            <td width="30%">Kepada Yth. : <br> {{(empty($lead->contactUser->name)?'-':$lead->contactUser->name)}} <br>{{ @$mainaddress->alamat }}</td>
            <td width="6%">No. Faktur <br> Tgl. Faktur <br> Tgl. Jatuh Tempo <br> Cara Bayar <br></td>
            <td width="25%"> : {{(empty($lead->order_number)?'-':$lead->order_number)}} <br> : {{ (!empty($lead->created_at)?date('l, d F Y', strtotime($lead->created_at)):'-') }}<br> : {{(empty($lead->due_date)?'-':$lead->due_date)}}<br> :
                {{(empty($lead->paymentTerm->name)?'-':$lead->paymentTerm->name)}}<br></td>
        </tr>
    </table>

    <table width="100%" style="margin-top:20px;border-bottom: 1px solid #000;">
        <tr style="background-color: #3D4043;">
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;border:0px solid #3D4043;" bgcolor="#3D4043">SKU</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Nama Barang</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">UoM</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Harga</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Jumlah Satuan</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Disc. %</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Disc. Rp</th>
            <th style="color: #fff;padding-top: 20px;padding-bottom:20px;">Jumlah Rp</th>
        </tr>
        @php $subtotal = $total = $discount = 0; @endphp
        @foreach ($productneeds as $prod)
        @php
        $pricepcs = $prod->price/$prod->qty;
        $subtotal += $pricepcs * $prod->qty;
        $disc = (empty($prod->discount->percentage)?'0':$prod->discount->percentage);
        $discount += $disc * $pricepcs / 100;
        $ppn = (empty($prod->tax->tax_percentage)?'0':$prod->tax->tax_percentage);
        $ppnval = $ppn * $pricepcs / 100;
        $total = $subtotal - $discount + $ppnval;
        @endphp
        <tr>
            <td align="center">
                {{ @$prod->product->sku }}
            </td>
            <td align="center">
                {{ @$prod->product->name }}
            </td>
            <td align="center">
                
            </td>
            <td align="center">
                Rp {{ number_format($pricepcs,0,',','.') }}
            </td>
            <td align="center">
                {{ $prod->qty }}
            </td>
            <td align="center">
                {{ $disc }}
            </td>
            <td align="center">
                Rp {{ number_format($disc * $prod->price / 100,0,',','.')}}
            </td>
            <td align="right">
                Rp {{ number_format($pricepcs * $prod->qty,0,',','.') }}
            </td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5"></td>
            <td align="center">
                <b>Subtotal</b>
            </td>
            <td align="right">
                Rp {{ number_format($lead->subtotal,0,',','.')}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td align="center">
                <b>Kode Unik</b>
            </td>
            <td align="right">
                Rp {{ number_format($lead->kode_unik,0,',','.')}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td align="center">
                <b>Diskon</b>
            </td>
            <td align="right">
                Rp {{ number_format($lead->discount_amount,0,',','.')}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td align="center">
                <b>PPN (11%)</b>
            </td>
            <td align="right">
                Rp {{ number_format(@$lead->tax_amount,0,',','.')}}
            </td>
        </tr>
        <tr>
            <td colspan="5"></td>
            <td align="center">
                <b>Total</b>
            </td>
            <td align="right">
                Rp {{ number_format($lead->amount,0,',','.')}}
            </td>
        </tr>
        <tr>
            <td align="center">
                Notes
            </td>
            <td align="center">
                : {{(empty($lead->paymentTerm->name)?'-':$lead->paymentTerm->name)}}
            </td>
            <td align="right" colspan="5">
                <!-- (Delapan Ratus Tiga Puluh Dua Ribu Lima Ratus Rupiah) -->
            </td>
        </tr>
    </table>
    <br><br>
    <table width="100%">
        <tr>
            <td width="25%">Hormat Kami,</td>
            <td width="25%">Disetujui,</td>
            <td width="25%">Dikirim,</td>
            <td width="25%">Diterima,</td>
        </tr>
    </table>
</body>

</html>