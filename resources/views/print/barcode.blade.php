<html>

<head>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8">
    <meta name=Generator content="Microsoft Word 15 (filtered)">
    <title>AIMI_BARCODE</title>
    <style>
        /* Font Definitions */
        * {
            font-size: 12pt;
        }

        @font-face {
            font-family: "Cambria Math";
            panose-1: 2 4 5 3 5 4 6 3 2 4;
        }

        @font-face {
            font-family: "Microsoft Sans Serif";
            panose-1: 2 11 6 4 2 2 2 2 2 4;
        }

        @font-face {
            font-family: "Arial MT";
        }

        /* Style Definitions */

        p.MsoNormal,
        li.MsoNormal,
        div.MsoNormal {
            margin: 0in;
            text-autospace: none;
            font-size: 12pt;
            font-family: "Arial", sans-serif;
        }

        p.MsoBodyText,
        li.MsoBodyText,
        div.MsoBodyText {
            margin: 0in;
            text-autospace: none;
            font-size: 12pt;
            font-family: "Microsoft Sans Serif", sans-serif;
        }

        p.TableParagraph,
        li.TableParagraph,
        div.TableParagraph {
            mso-style-name: "Table Paragraph";
            margin-top: 1.25pt;
            margin-right: 0in;
            margin-bottom: 0in;
            margin-left: 0in;
            text-autospace: none;
            font-size: 12pt;
            font-family: "Arial", sans-serif;
        }

        .MsoChpDefault {
            font-family: "Calibri", sans-serif;
        }

        .MsoPapDefault {
            text-autospace: none;
        }

        @page WordSection1 {
            size: 595.5pt 842.0pt;
            margin: 15.0pt 11.0pt 0in 12.0pt;
        }

        div.WordSection1 {
            page: WordSection1;
        }
    </style>
</head>

<body lang=EN-US style='word-wrap:break-word'>
    <div class=WordSection1>
        <table class=MsoTableGrid width="50%" border=1 cellspacing=0 cellpadding=0 style='border-collapse:collapse;border:none' width="100%">
            <tr>
                <td rowspan="2" width=94 valign=top style='width:70.85pt;border:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    @if(isset($logo) && $logo)
                        <img src="{{ $logo }}" alt="Logo" style="height:50px; margin-right: 10px;">
                    @else
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Default Logo" style="height:50px; margin-right: 10px;">
                    @endif
                </td>
                <td width=180 valign=middle style='width:134.7pt;border:solid windowtext 1.0pt;border-left:none;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><b><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>{{ @$brand->pt_name??'PT. Anugrah Inovasi Makmur Indonesia' }}</span></b></p>
                </td>
            </tr>
            <tr>
                <td width=180 valign=top style='width:134.7pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>{{ $data->item_name }}</span></p>
                </td>
            </tr>
            <tr>
                <td width=94 valign="center" style='width:70.85pt;border-top:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt;padding-bottom:10px;padding-top:10px;'>
                    {!! QrCode::size(100)->generate($data->barcode) !!}
                </td>
                <td width=180 valign="center" style='width:134.7pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p style="margin-top:-1px;">{{$data->barcode}}</p>
                </td>
            </tr>
            <tr>
                <td width=94 valign=top style='width:70.85pt;border-top:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>Created At</span></p>
                </td>
                <td width=180 valign=top style='width:134.7pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>: {{ date('d-m-Y', strtotime($data->generate_date))}}</span></p>
                </td>
            </tr>
            <tr>
                <td width=94 valign=top style='width:70.85pt;border-top:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>Lokasi Asset</span></p>
                </td>
                <td width=180 valign=top style='width:134.7pt;border-top:none;border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;padding:0in 5.4pt 0in 5.4pt'>
                    <p class=MsoNormal style='margin-top:7.8pt'><span style='font-size:12.0pt;font-family:"Arial MT",sans-serif'>: {{ $data->asset_location??'-' }}</span></p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>