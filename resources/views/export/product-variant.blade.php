<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Master Product</th>
      <th>Product Name</th>
      <th>Package Name</th>
      <th>Variant Name</th>
      <th>SKU</th>
      <th>SKU Variant</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    <tr>
      <td>{{$row['no'] }}</td>
      <td>{{ $row['master_name'] }}</td>
      <td>{{ $row['product_name'] }}</td>
      <td>{{ $row['package_name'] }}</td>
      <td>{{ $row['variant_name'] }}</td>
      <td>{{ $row['sku'] }}</td>
      <td>{{ $row['sku_variant'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>