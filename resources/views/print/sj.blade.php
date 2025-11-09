<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
            <!-- Barcode area -->
            <svg id="barcode" style="position: absolute; top: 10px; left: 10px;"></svg>
        </div>
        <h2 style="margin-top:0pt; margin-bottom:0pt; text-align:center;"><span style="font-size: 32px;">SURAT JALAN</span></h2>
        @if ($total_print > 1)
        <p style="position: absolute;right:50px;top:30px;">COPY {{$total_print-1}}</p>
        @endif
    </div>
    <br><br>

    <table width="100%">
        <tr>
            <td width="50%">DARI : <br> {{$lead->company_name}} <br><br> KEPADA PELANGGAN : <br>{{ $lead->contact_name }}<br><br> ALAMAT PELANGGAN : <br>{{ @$mainaddress->alamat }}</td>
            <td width="50%">Details :<br>
                <table width="100%" border="1" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>Nomor Order</td>
                        <td>{{$lead->order_number}}</td>
                    </tr>
                    <tr>
                        <td>No. Referensi</td>
                        <td>{{$lead->preference_number}}</td>
                    </tr>
                    @if (isset($delivery->delivery_date))
                    <tr>
                        <td>Tanggal Pengiriman</td>
                        <td>{{ formatTanggalIndonesia($delivery->delivery_date,'l, d F Y')}}</td>
                    </tr>
                    @else
                    <tr>
                        <td>Tanggal Pengiriman</td>
                        <td>{{ formatTanggalIndonesia(@$delivery->created_at,'l, d F Y')}}</td>
                    </tr>
                    @endif
                    @if (isset($lead->expired_at))
                    <tr>
                        <td>Expired SO</td>
                        <td>{{ formatTanggalIndonesia($lead->expired_at,'l, d F Y')}}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Sales</td>
                        <td>{{$lead->sales_name}}</td>
                    </tr>
                    <tr>
                        <td>Notes</td>
                        <td>{{$lead->notes}}</td>
                    </tr>
                    <tr>
                        <td>Printed</td>
                        <td>{{$lead->print_status}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-top:20px;border-bottom: 1px solid #000;">
        <tr style="background-color: #3D4043;">
            <th align="center" style="color: #fff;padding-top: 20px;padding-bottom:20px;border:0px solid #3D4043;" bgcolor="#3D4043">No</th>
            <th align="left" style="color: #fff;padding-top: 20px;padding-bottom:20px;">SKU</th>
            <th align="left" style="color: #fff;padding-top: 20px;padding-bottom:20px;">Nama Produk</th>
            <th align="center" style="color: #fff;padding-top: 20px;padding-bottom:20px;">Qty</th>
        </tr>
        @foreach ($productneeds as $key => $prod)
        <tr>
            <td align="center">
                {{$key+1}}
            </td>
            <td align="left">
                {{$prod->sku}}
            </td>
            <td align="left">
                {{ $prod->product_name }}
            </td>
            <td align="center">
                {{ $prod->qty_delivered }}
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
    <p style="color:red;font-size:14px;margin-bottom:10px;">
        *TIDAK MELAYANI KOMPLAIN SETELAH DOKUMEN DITANDATANGANI , LAKUKAN VIDEO UNBOXING DENGAN JELAS SEBELUM PAKET DIBUKA SAMPAI SELESAI DAN PERIKSA KELENGKAPAN ISI. TANPA VIDEO UNBOXING KOMPLAIN ATAU RETUR RUSAK ATAU KURANG TIDAK DI TERIMA
    </p>
    @if ($lead->type == 'konsinyasi')
    <p>
        *Catatan Sistem : {{ ($lead->order_type == 'old')?'Data Lama':'Data Baru' }}
    </p>
    @endif

    <script>
        const orderNumber = "{{ $lead->order_number }}";
        JsBarcode("#barcode", orderNumber, {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true
        });
    </script>

</body>

</html>