<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
	<title>Sales Invoice</title>
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
		<h2 style="margin-top:0pt; margin-bottom:0pt; text-align:center;"><span style="font-size: 32px;">SALES INVOICE</span></h2>
		@if ($total_print > 1)
		<p style="position: absolute;right:50px;top:30px;">COPY {{$total_print-1}}</p>
		@endif
	</div>
	{{-- {{dd($productneeds)}} --}}
	<br><br>
	<table width="100%">
		<tr>
			<td>{{$lead->company_name}}</td>
		</tr>
		<tr>
			<td width="50%"><strong>Customer</strong> : <br>{{(empty($lead->contact_name)?'-':$lead->contact_name)}}<br><br> <strong>Delivery Address</strong> : <br>{{ @$mainaddress->alamat }}</td>
			<td width="50%"><strong>Details</strong> :<br>
				<table width="100%" border="1" cellpadding="0" cellspacing="0">

					<tr>
						<td>Sales Invoice No.</td>
						<td>{{$invoice_number}}</td>
					</tr>
					<tr>
						<td>No. Referensi</td>
						<td>{{$lead->preference_number}}</td>
					</tr>
					<tr>
						<td>Approved</td>
						<td>-</td>
					</tr>
					<tr>
						<td>Created Date</td>
						<td>
							{{ formatTanggalIndonesia(($lead->created_at),'l, d F Y')}}
						</td>
					</tr>
					@if (isset($delivery->delivery_date))
					<tr>
						<td>Delivery Date</td>

						<td> {{ formatTanggalIndonesia(($delivery->delivery_date),'l, d F Y')}}</td>
					</tr>
					@else
					<tr>
						<td>Delivery Date</td>
						<td>-</td>
					</tr>
					@endif
					@if (isset($lead->expired_date))
					<tr>
						<td>Expired Date</td>
						<td>{{ formatTanggalIndonesia(($lead->expired_date),'l, d F Y')}}</td>
					</tr>
					@endif
					@if (isset($productneeds[0]->invoice_date))
					<tr>
						<td>Invoice Date</td>
						<td>{{ formatTanggalIndonesia(($productneeds[0]->invoice_date),'l, d F Y')}}</td>
					</tr>
					@else
					<tr>
						<td>Invoice Date</td>
						<td>{{ $lead->invoice_date ? formatTanggalIndonesia(($lead->invoice_date),'l, d F Y') : '-'}}</td>
					</tr>
					@endif
					<tr>
						<td>Salesperson</td>
						<td>{{(empty($lead->sales_name)?'-':$lead->sales_name)}}</td>
					</tr>
					<tr>
						<td>Term of Payment</td>
						<td>{{(empty($lead->payment_term_name)?'-':$lead->payment_term_name)}}</td>
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
						<td>{{formatTanggalIndonesia($lead->due_date,'d-m-Y')}}</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
	<br>
	<table style="width: 100%;" border="1" cellpadding="0" cellspacing="0">
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
				HARGA SATUAN
			</th>
			<th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
				DISCOUNT (%)
			</th>
			<th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
				TAX (%)
			</th>
			<th style="color: #fff; padding-top: 20px; padding-bottom: 20px">
				SUBTOTAL
			</th>
			<th width() style="color: #fff; padding-top: 20px; padding-bottom: 20px">
				TOTAL
			</th>
		</tr>
		{{-- {{dd($productneeds,$single)}} --}}
		<?php $no=1; ?>
		@if ($single)
		@foreach ($productneeds as $prod)
		<tr>
			<td style="width: 3.4139%; text-align: center">{{ $no++ }}</td>
			<td style="width: 18.7137%">{{ $prod->sku }}<br /></td>
			<td style="width: 27.8037%">{{ @$prod->product_name }}</td>
			<td style="width: 5%; text-align: center">{{ $prod->qty_delivered }}</td>
			<td style="width: 10%; text-align: right">
				Rp {{ number_format($prod->price_item,0,',','.') }}
			</td>
			<td style="width: 7%; text-align: right">
				{{$prod->discount_amount }}
			</td>
			<td style="width: 7%; text-align: right">
				{{$prod->tax_percentage }}
			</td>
			<td style="width: 10%; text-align: right">
				Rp {{ number_format($prod->subtotal,0,',','.') }}
			</td>
			<td style="width: 23.5528%; text-align: right">
				Rp {{ number_format($prod->total,0,',','.') }}
			</td>
		</tr>
		@endforeach
		@else
		@foreach ($productneeds as $prod)
		<tr>
			<td style="width: 3.4139%; text-align: center">{{ $no++ }}</td>
			<td style="width: 18.7137%">{{ @$prod->sku }}<br /></td>
			<td style="width: 27.8037%">{{ @$prod->product_name }}</td>
			<td style="width: 5%; text-align: center">{{ $prod->qty }}</td>
			<td style="width: 10%; text-align: right">
				Rp {{ number_format($prod->price_item,0,',','.') }}
			</td>
			<td style="width: 7%; text-align: right">
				{{$prod->discount_amount }}
			</td>
			<td style="width: 7%; text-align: right">
				{{$prod->tax_percentage }}
			</td>
			<td style="width: 10%; text-align: right">
				Rp {{ number_format(($prod->subtotal),0,',','.') }}
			</td>
			<td style="width: 23.5528%; text-align: right">
				Rp {{ number_format($prod->total,0,',','.') }}
			</td>
		</tr>
		@endforeach
		@endif

	</table>
	<br>
	{{-- {{dd($lead,$invoice)}} --}}
	<table style="width: 26%; margin-left: calc(74%);">
		<tbody>

			@if ($single)
			<tr>
				<td style="width: 62.5044%;">Subtotal</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($invoice->subtotal,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">Subtotal</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($lead->subtotal,0,',','.')}}</td>
			</tr>
			@endif
			@if ($single)
			<tr>
				<td style="width: 62.5044%;">Discount</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($invoice?->discount_amount,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">Discount</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($lead->diskon,0,',','.')}}</td>
			</tr>
			@endif

			{{-- <tr>
				<td style="width: 62.5044%;">Freight</td>
				<td style="width: 36.8587%; text-align: right;">0</td>
			</tr>
			<tr>
				<td style="width: 62.5044%;">Miscellanaous</td>
				<td style="width: 36.8587%; text-align: right;">0</td>
			</tr> --}}
			@if ($single)
			<tr>
				<td style="width: 62.5044%;">DPP</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($invoice->dpp,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">DPP</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format($lead->dpp,0,',','.')}}</td>
			</tr>
			@endif
			@if ($single)
			<tr>
				<td style="width: 62.5044%;">PPN</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$invoice->ppn,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">PPN</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$lead->ppn,0,',','.')}}</td>
			</tr>
			@endif
			@if ($single)
			<tr>
				<td style="width: 62.5044%;">KODE UNIK</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$invoice->kode_unik,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">KODE UNIK</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$lead->kode_unik,0,',','.')}}</td>
			</tr>
			@endif
			@if ($single)
			<tr>
				<td style="width: 62.5044%;">ONGKIR</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$invoice->ongkir,0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">ONGKIR</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format(@$lead->ongkir,0,',','.')}}</td>
			</tr>
			@endif

			@if ($single)
			<tr>
				<td style="width: 62.5044%;">TOTAL</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format((@$invoice->total),0,',','.')}}</td>
			</tr>
			@else
			<tr>
				<td style="width: 62.5044%;">TOTAL</td>
				<td style="width: 36.8587%; text-align: right;">Rp {{ number_format((@$lead->total),0,',','.')}}</td>
			</tr>
			@endif

		</tbody>
	</table>
	<br>
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
	<p><br></p>
	<p style="margin-top:0pt; margin-bottom:0pt; line-height:150%; font-size:11pt;"><br></p>
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td style="width: 12.50000%;">
					<hr><br>
				</td>
				<td style="width: 12.50000%;"><br></td>
				<td style="width: 12.50000%;">
					<hr><br>
				</td>
				<td style="width: 12.50000%;"><br></td>
				<td style="width: 12.50000%;">
					<hr><br>
				</td>
				<td style="width: 12.50000%;"><br></td>
				<td style="width: 12.50000%;">
					<hr><br>
				</td>
				<td style="width: 12.50000%;"><br></td>
			</tr>
		</tbody>
	</table>
	@if ($lead->type == 'konsinyasi')
	<table width="100%">
		<tr>
			<td style="text-align: right;">
				<p>
					*Catatan Sistem : {{ ($lead->order_type == 'old')?'Data Lama':'Data Baru' }}
				</p>
			</td>
		</tr>
	</table>
	@endif
</body>

</html>