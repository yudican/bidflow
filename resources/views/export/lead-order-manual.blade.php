<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Contact</th>
      <th>Company</th>
      <th>Customer Need</th>
      <th>Sales</th>
      <th>Created On</th>
      <th>Created By</th>
      @if ($type != 'konsinyasi')
      <th>Warehouse</th>
      @endif
      <th>Order Number</th>
      <th>Invoice Number</th>
      <th>Payment Term</th>
      <th>Due Date</th>
      <th>Addres type</th>
      <th>Addres name</th>
      <th>Addres telp</th>
      <th>Addres street</th>
      <th>Status</th>
      <th>Print Status</th>
      <th>Resi Status</th>
      <th>SKU</th>
      <th>Nama Produk</th>
      <th>Harga Produk</th>
      <th>QTY</th>
      <th>Tax</th>
      <th>Discount</th>
      <th>Subtotal</th>
      <th>Total Dpp + PPN</th>
      <th>Total Price</th>
      <th>Notes</th>
      <th>Ongkir</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @foreach ($row['product'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['product'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['contact'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ ($row['company'])?$row['company']:'-' }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['customer_need'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['pic_sales'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['created_on'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['created_by'] }}</td>
      @if ($type != 'konsinyasi')
      <td rowspan="{{count($row['product'])}}">{{ $row['warehouse'] }}</td>
      @endif
      <td rowspan="{{count($row['product'])}}">{{ $row['order_number'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['invoice_number'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['payment_term'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ date('d-m-Y', strtotime($row['expired_date'])) }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['address_type'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['address_name'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['address_telp'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['address_street'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['status'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['print_status'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['resi_status'] }}</td>
      @endif
      <td>{{ $item['sku'] }}</td>
      <td>{{ $item['product_name'] }}</td>
      <td>{{ $item['price'] }}</td>
      <td>{{ $item['qty'] }}</td>
      <td>{{ $item['tax_amount'] }}</td>
      <td>{{ $item['discount_amount'] }}</td>
      <td>{{ $item['subtotal'] }}</td>
      <td>{{ $item['price_nego'] }}</td>
      <td>{{ $item['total_price'] }}</td>
      <td>{{ $row['notes'] }}</td>
      <td>{{ @$row['ongkir'] }}</td>
    </tr>
    @endforeach
    @endforeach
  </tbody>
</table>