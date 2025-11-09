<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Product</th>
      <th>Package</th>
      <th>Stock</th>
      <th>Sales Channel</th>
      <th>Variant</th>
      <th>Price</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    <tr>
      <td>{{ $row['no'] }}</td>
      <td>{{ $row['product_name'] }}</td>
      <td>{{ $row['package_name'] }}</td>
      <td>{{ $row['stock'] }}</td>
      <td>{{ $row['sales_channel'] }}</td>
      <td>{{ $row['variant_name'] }}</td>
      <td>{{ $row['price'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>