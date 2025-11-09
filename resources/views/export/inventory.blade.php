@php
$hasTransfer = collect($datas)->contains('inventory_type', 'transfer');
$hasKonsinyasi = collect($datas)->contains('inventory_type', 'konsinyasi');
@endphp

<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th>No</th>
      <th>TRF ID</th>
      <th>Product Name</th>
      <th>QTY</th>
      <th>Warehouse</th>
      <th>Allocated By</th>
      <th>Created On</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
    @foreach($datas as $data)
    @foreach($data['product'] as $index => $product)
    <tr>
      <td>{{ $data['no'] }}</td>
      <td>{{ $data['inventory_type'] == 'transfer' ? $data['trf_id'] : '' }}</td>
      <td>{{ $product['product_name'] }}</td>
      <td>{{ $product['qty'] }}</td>
      <td>{{ $data['warehouse'] }}</td>
      <td>{{ $data['allocated_by'] }}</td>
      <td>{{ $data['created_on'] }}</td>
      <td>{{ $data['notes'] }}</td>
    </tr>
    @endforeach
    @endforeach
  </tbody>
</table>