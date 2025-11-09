<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Title</th>
      <th>Contact</th>
      <th>Sales</th>
      <th>Created By</th>
      <th>Brand</th>
      <th>Nama Produk</th>
      <th>Harga Produk</th>
      <th>QTY</th>
      <th>Tax</th>
      <th>Discount</th>
      <th>Subtotal</th>
      <th>Total Dpp + PPN</th>
      <th>Total Price</th>
      <th>Created Date</th>
      <th>DD</th>
      <th>MM</th>
      <th>YY</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @foreach ($row['product_needs'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['product_needs'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['product_needs'])}}">{{ $row['title'] }}</td>
      <td rowspan="{{count($row['product_needs'])}}">{{ $row['contact_name'] }}</td>
      <td rowspan="{{count($row['product_needs'])}}">{{ $row['sales_name'] }}</td>
      <td rowspan="{{count($row['product_needs'])}}">{{ $row['created_by_name'] }}</td>
      <td rowspan="{{count($row['product_needs'])}}">{{ $row['brand_name'] }}</td>
      @endif
      <td>{{ $item['product_name'] }}</td>
      <td>{{ $item['price'] }}</td>
      <td>{{ $item['qty'] }}</td>
      <td>{{ $item['tax_amount'] }}</td>
      <td>{{ $item['discount_amount'] }}</td>
      <td>{{ $item['subtotal'] }}</td>
      <td>{{ $item['price_nego'] }}</td>
      <td>{{ $item['total_price'] }}</td>
      <td>{{ $row['created_at'] }}</td>
      <td>{{ date("l d", strtotime($row['created_at'])) }}</td>
      <td>{{ date("m", strtotime($row['created_at'])) }}</td>
      <td>{{ date("Y", strtotime($row['created_at'])) }}</td>
    </tr>
    @endforeach
    @endforeach
  </tbody>
</table>