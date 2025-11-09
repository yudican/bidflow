{{-- <div>
  <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width=100% style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:1184;mso-padding-alt:0cm 5.4pt 0cm 5.4pt;font-family:arial;'>
    <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
      <td width=623 valign=middle style='width:467.5pt;border:solid windowtext 1.0pt;
          mso-border-alt:solid windowtext .5pt;background:#fff;mso-background-themecolor:
          background2;mso-background-themeshade:191;padding:0cm 5.4pt 0cm 5.4pt; text-align: center;'>
        <p class=MsoNormal style='margin: 0; display: flex; align-items: center;'>
          @if(isset($logo) && $logo)
          <img src="{{ $logo }}" alt="Logo" style="height:50px; margin-right: 10px;">
          @else
          <img src="{{ asset('assets/img/logo.png') }}" alt="Default Logo" style="height:50px; margin-right: 10px;">
          @endif
          <b><span lang=EN-US style='color:black;mso-color-alt:windowtext; mso-ansi-language:EN-US; font-family:arial;'>{{ $brand->pt_name }}</span></b>
          <b><span lang=EN-US style='mso-ansi-language:EN-US'>
              <o:p></o:p>
            </span></b>
        </p>
      </td>
    </tr>

    <tr style='mso-yfti-irow:1'>
      <td width=623 valign=top style='width:467.5pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US;font-family:arial;margin-bottom:10px'>PURCHASE
            REQUISITION<o:p></o:p></span></p>
      </td>
    </tr>
    <tr style='mso-yfti-irow:2;mso-yfti-lastrow:yes'>
      <td width=623 valign=top style='width:467.5pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  padding:0cm 5.4pt 0cm 5.4pt'>
        <p class=MsoNormal>
          <o:p>&nbsp;</o:p>
        </p>
        <table class=MsoTableGrid border=1 cellspacing=0 cellpadding=0 width=100% style='border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
   mso-yfti-tbllook:1184;mso-padding-alt:0cm 5.4pt 0cm 5.4pt'>
          <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
            <td width=116 colspan=3 valign=top style='width:87.35pt;border:none;
    border:none;mso-border-bottom-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>Brand<o:p></o:p></span></p>

            </td>
            <td width=254 colspan=5 valign=top style='width:190.3pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>: {{$data->brand_name}}<o:p></o:p></span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.6pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>Date<o:p></o:p></span></p>
            </td>
            <td width=137 colspan=2 valign=top style='width:102.45pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>: {{date('d-m-Y',strtotime($data->created_at))}}<o:p></o:p></span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:0;mso-yfti-firstrow:yes'>
            <td width=116 colspan=3 valign=top style='width:87.35pt;
    border:none;border-bottom:solid windowtext 1.0pt;border:none;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>Requestor<o:p></o:p></span></p>

            </td>
            <td width=254 colspan=5 valign=top style='width:190.3pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>: {{$data->request_by_name}}<o:p></o:p></span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.6pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>No. PR<o:p></o:p></span></p>
            </td>
            <td width=137 colspan=2 valign=top style='width:102.45pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>: {{$data->pr_number}}<o:p></o:p></span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:2'>
            <td width=116 colspan=3 valign=top style='width:87.35pt;
    border:none;border-bottom:solid windowtext 1.0pt;mso-border-bottom-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>Division<o:p></o:p></span></p>

            </td>
            <td width=254 colspan=5 valign=top style='width:190.3pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>: {{$data->request_by_division}}<o:p></o:p></span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.6pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  Request by
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=137 colspan=2 valign=top style='width:102.45pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  : {{$data->request_by_name}}
                  <o:p></o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:3'>
            <td width=49 valign=top style='width:36.7pt;border:solid windowtext 1.0pt;
    border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><b><span lang=EN-US style='mso-ansi-language:EN-US'>No.<o:p></o:p></span></b></p>
            </td>
            <td width=210 colspan=4 valign=top style='width:157.45pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><b><span lang=EN-US style='mso-ansi-language:EN-US'>Request<o:p></o:p></span></b></p>
            </td>
            <td width=111 colspan=3 valign=top style='width:83.5pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><b><span lang=EN-US style='mso-ansi-language:EN-US'>Qty<o:p></o:p></span></b></p>
            </td>
            <td width=239 colspan=4 valign=top style='width:179.05pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><b><span lang=EN-US style='mso-ansi-language:EN-US'>Description<o:p></o:p></span></b></p>
            </td>
          </tr>

          @foreach ($data->items as $key => $item)
          <tr style='mso-yfti-irow:4'>
            <td width=49 valign=top style='width:36.7pt;border:solid windowtext 1.0pt;
    border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>{{$key+1}}.<o:p></o:p></span></p>
            </td>
            <td width=210 colspan=4 valign=top style='width:157.45pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>{{$item->item_name}}</span></p>
            </td>
            <td width=55 colspan=3 valign=top style='width:41.0pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>{{$item->item_qty}}<o:p></o:p></span></p>
            </td>

            <td width=239 colspan=4 valign=top style='width:179.05pt;border-top:none;
    border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
    mso-border-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>{{@$item->item_note}}<o:p></o:p></span></p>
            </td>
          </tr>
          @endforeach

          <tr style='mso-yfti-irow:8'>
            <td width=49 valign=top style='width:36.7pt;border:none;border-bottom:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-top-alt:solid windowtext .5pt;
    mso-border-bottom-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=210 colspan=4 valign=top style='width:157.45pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=55 colspan=2 valign=top style='width:41.0pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=57 valign=top style='width:42.5pt;border:none;border-bottom:solid windowtext 1.0pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-top-alt:solid windowtext .5pt;
    mso-border-bottom-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=239 colspan=4 valign=top style='width:179.05pt;border:none;
    border-bottom:solid windowtext 1.0pt;mso-border-top-alt:solid windowtext .5pt;
    mso-border-top-alt:solid windowtext .5pt;mso-border-bottom-alt:solid windowtext .5pt;
    padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:9'>
            <td width=609 colspan=12 valign=top style='width:456.7pt;border:solid windowtext 1.0pt;
    border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal align=center style='text-align:center'><b><span lang=EN-US style='mso-ansi-language:EN-US'>Notes<o:p></o:p></span></b></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:10'>
            <td width=609 colspan=12 valign=top style='width:456.7pt;border:solid windowtext 1.0pt;
    border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:
    solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>{{$data->request_note}}<o:p></o:p></span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:11'>
            <td width=609 colspan=12 valign=top style='width:456.7pt;border:none;
    mso-border-top-alt:solid windowtext .5pt;padding:0cm 5.4pt 0cm 5.4pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:12;height:12.6pt'>
            <td width=87 rowspan=2 valign=top style='width:65.05pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal><span class=GramE><span lang=EN-US style='mso-ansi-language:EN-US'>Date :</span></span><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Requested By,<o:p></o:p></span></p>
            </td>
            <td width=116 colspan=2 valign=top style='width:87.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Verified by,<o:p></o:p></span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Approved by,<o:p></o:p></span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Approved By,<o:p></o:p></span></p>
            </td>
            <td width=87 colspan=2 valign=top style='width:65.35pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=116 valign=top style='width:86.9pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.6pt'>
              <p class=MsoNormal align=center style='text-align:center'><span class=SpellE><span lang=EN-US style='mso-ansi-language:EN-US'>Excecuted</span></span><span lang=EN-US style='mso-ansi-language:EN-US'> by,<o:p></o:p></span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:13;height:12.55pt'>
            <td width=116 valign=top style='width:87.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <!-- {{date('d/m/Y',strtotime($data->created_at))}} -->
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <!-- {{date('d/m/Y',strtotime($data->created_at))}} -->
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <!-- {{date('d/m/Y',strtotime($data->created_at))}} -->
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=87 colspan=2 valign=top style='width:65.35pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=116 valign=top style='width:86.9pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <!-- {{date('d/m/Y',strtotime($data->created_at))}} -->
                  <o:p></o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:14;height:12.55pt'>
            <td width=87 valign=top style='width:65.05pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=87 colspan=2 valign=top style='width:65.35pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <center>
                <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                    <o:p><img src="{{asset('assets/img/confirmed.png')}}" style="height:50px; margin-right: 10px;" alt=""></o:p>
                  </span></p>
              </center>
            </td>
            <td width=116 colspan=2 valign=top style='width:87.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>

            </td>
            <td width=102 colspan=2 valign=top style='width:76.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>

            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>

            </td>

            <td width=116 colspan=2 valign=top style='width:86.9pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>

            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>

            </td>
          </tr>
          <tr style='mso-yfti-irow:15;height:12.55pt'>
            <td width=87 valign=top style='width:65.05pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=116 colspan=2 valign=top style='width:87.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.15pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=87 colspan=2 valign=top style='width:65.35pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=116 valign=top style='width:86.9pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
          </tr>
          <tr style='mso-yfti-irow:16;mso-yfti-lastrow:yes;height:12.55pt'>
            <td width=87 valign=top style='width:65.05pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal><span class=GramE><span lang=EN-US style='mso-ansi-language:EN-US'>Name :</span></span><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p></o:p>
                </span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>{{$data->request_by_name}}<o:p></o:p></span></p>
            </td>
            <td width=116 colspan=2 valign=top style='width:87.15pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>({{$data->verified_by_name}})<o:p></o:p></span></p>
            </td>
            <td width=102 colspan=2 valign=top style='width:76.15pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Edward Jogia<o:p></o:p></span></p>
            </td>
            <td width=101 colspan=2 valign=top style='width:76.1pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>Dennis Hadi<o:p></o:p></span></p>
            </td>

            <td width=87 colspan=2 valign=top style='width:65.35pt;border:none;
    padding:0cm 5.4pt 0cm 5.4pt;height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>
                  <o:p>&nbsp;</o:p>
                </span></p>
            </td>
            <td width=116 valign=top style='width:86.9pt;border:none;padding:0cm 5.4pt 0cm 5.4pt;
    height:12.55pt'>
              <p class=MsoNormal align=center style='text-align:center'><span lang=EN-US style='mso-ansi-language:EN-US'>({{$data->excecuted_by_name}})<o:p></o:p></span></p>
            </td>
          </tr>
        </table>
        <p class=MsoNormal><span lang=EN-US style='mso-ansi-language:EN-US'>
            <o:p></o:p>
          </span></p>
      </td>
    </tr>
  </table>
</div> --}}

<table border="2" width="100%" style="border-collapse: collapse; border-color: #000;">
  <tr>
    <th style="border: 0;" width="15%">
  <tr>
    <td style="display: flex;border: 0;">
      @if(isset($logo) && $logo)
      <img src="{{ $logo }}" alt="Logo" style="height:30px; margin:10px;">
      @else
      <img src="{{ asset('assets/img/logo.png') }}" alt="Default Logo" style="height:30px; margin-right: 10px;">
      @endif
      <p><strong>{{ $brand->pt_name }}</strong></p>
    </td>
  </tr>
  </th>
  </tr>
  <tr>
    <td colspan="6" style="margin-left:10px;"><strong style="margin-left:10px;">PURCHASE REQUITITION</strong></td>
  </tr>
  <tr>
    <td style="padding:10px;">
      <table width="100%" border="1" style="border-collapse: collapse; border-color: #000;">
        <tbody>
          <tr>
            <td colspan="6" style="border: 1px solid #fff;border-right: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td style="border: 1px solid #fff;">Brand</td>
            <td style="border: 1px solid #fff;">: {{$data->brand_name}}</td>
            <td rowspan="3" style="border: 1px solid #fff;" colspan="2"></td>
            <td style="border: 1px solid #fff;">Date</td>
            <td style="border: 1px solid #fff;">: {{date('d-m-Y',strtotime($data->created_at))}}</td>
          </tr>
          <tr>
            <td style="border: 1px solid #fff;">Requestor</td>
            <td style="border: 1px solid #fff;">: {{$data->request_by_name}}</td>
            <td style="border: 1px solid #fff;">No. PR</td>
            <td style="border: 1px solid #fff;">: {{$data->pr_number}}</td>
          </tr>
          <tr>
            <td style="border: 1px solid #fff;">Division</td>
            <td style="border: 1px solid #fff;">: {{$data->request_by_division}}</td>
            <td style="border: 1px solid #fff;">Request By</td>
            <td style="border: 1px solid #fff;">: {{$data->request_by_name}}</td>
          </tr>
          <tr>
            <td colspan="6" style="border-left: 1px solid #fff;border-right: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td align="center" style="border-right: 0;"><strong>No.</strong></td>
            <td align="center" colspan="3"><strong>Request</strong></td>
            <td align="center"><strong>QTY</strong></td>
            <td align="center"><strong>Description</strong></td>
          </tr>
          @foreach ($data->items as $key => $item)
          <tr>
            <td align="center">{{$key+1}}.</td>
            <td align="center" colspan="3">{{$item->item_name}}</td>
            <td align="center">{{$item->item_qty}}</td>
            <td align="center">{{@$item->item_note}}</td>
          </tr>
          @endforeach

          <tr>
            <td colspan="6" style="border-left: 1px solid #fff;border-right: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td colspan="6" style="border: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td colspan="6" style="border-left: 1px solid #fff;border-right: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td colspan="6" style="text-align: center;"><strong>Notes</strong></td>
          </tr>
          <tr>
            <td colspan="6">
              <p style="height: 100px;text-align:start;">{{$data->request_note}}</p>
            </td>
          </tr>
          <tr>
            <td colspan="6" style="border-left: 1px solid #fff;border-right: 1px solid #fff;height:20px;"></td>
          </tr>
          <tr>
            <td style="border: 1px solid #fff;" align="center">Date:</td>
            <td style="border: 1px solid #fff;" align="center">Requested By</td>
            @foreach ($data->approvalLeads as $approval)
            @if ($approval->user_name != 'Tenawati')
            <td style="border: 1px solid #fff;" align="center">{{$approval->label}}</td>
            @endif
            @endforeach
          </tr>
          <tr>
            <td style="border: 1px solid #fff;" align="center">

            </td>
            <td style="border: 1px solid #fff;" align="center">
              <img src="{{asset('assets/img/confirmed.png')}}" style="height:50px; margin-right: 10px;" alt="">
            </td>
            @foreach ($data->approvalLeads as $approval)
            @if ($approval->user_name != 'Tenawati')
            <td style="border: 1px solid #fff;" align="center"></td>
            @endif
            @endforeach
          </tr>
          <tr>
          </tr>
          <tr>
            <td style="border: 1px solid #fff;" align="center">Name:</td>
            <td style="border: 1px solid #fff;" align="center">({{$data->request_by_name}})</td>
            @foreach ($data->approvalLeads as $approval)
            @if ($approval->user_name != 'Tenawati')
            <td style="border: 1px solid #fff;" align="center">({{$approval->user_name}})</td>
            @endif
            @endforeach
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</table>