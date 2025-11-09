@php
// Buat array untuk tracking master_bin yang sudah muncul
$masterBinCounter = [];
$currentKey = 1;
$masterBinKeys = [];

// Pertama, mapping master_bin dengan key yang seharusnya
foreach($data as $row) {
$masterBin = $row['master_bin_id'];
if (!isset($masterBinKeys[$masterBin])) {
$masterBinKeys[$masterBin] = $currentKey;
$currentKey++;
}
}
@endphp
<table border="1">
  <thead>
    <tr>
      <th>Code SO</th>
      <th>customer code</th>
      <th>destinasi bin</th>
      <th>destinasi bin id</th>
      <th>nama product</th>
      <th>qty</th>
      <th>harga_satuan</th>
      {{-- <th>warehouse id</th> --}}
      <th>tax id</th>
      <th>sales</th>
      {{-- <th>payment_term</th> --}}
      <th>diskon rp</th>
      <th>created by</th>
      <th>expired at</th>
      <th>reference_number</th>
      <th>Notes</th>
      <th>kategori_data</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $row)
    <tr>
      <td>{{$masterBinKeys[$row['master_bin_id']]}}</td>
      <td>{{$row['uid']}}</td>
      <td>{{$row['master_bin_name']}}</td>
      <td>{{$row['master_bin_id']}}</td>
      <td>{{$row['product_name']}}</td>
      <td>{{isset($row['qty']) ? $row['qty'] : 0}}</td>
      <td></td>
      {{-- <td>{{$row['from_warehouse_id']}}</td> --}}
      <td>1</td>
      <td>{{$row['created_by_name']}}</td>
      {{-- <td>{{$row['payment_term_name']}}</td> --}}
      <td>{{$row['discount']}}</td>
      <td>{{auth()->user()->name}}</td>
      <td>{{date('d-m-Y',strtotime($expired_at))}}</td>
      <td></td>
      <td></td>
      <td>old/new</td>
    </tr>
    @endforeach
  </tbody>
</table>