<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>TRX ID</th>
      <th>Customer</th>
      <th>Tanggal Transaksi</th>
      <th>Brand</th>
      <th>Voucher</th>
      <th>Metode Pembayaran</th>
      <th>Total</th>
      <th>Diskon</th>
      <th>Status</th>
      <th>Status Pengiriman</th>
      <th>Resi</th>
      <th>Nama Produk</th>
      <th>Harga Produk</th>
      <th>QTY</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @foreach ($row['details'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['details'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['id_transaksi'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['user'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['tanggal_transaksi'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['brand'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['voucher'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['payment_method'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['nominal'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['diskon'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['status'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['status_delivery'] }}</td>
      <td rowspan="{{count($row['details'])}}">{{ $row['resi'] }}</td>
      @endif
      <td>{{ $item['product_name'] }}</td>
      <td>{{ $item['price'] }}</td>
      <td>{{ $item['qty'] }}</td>
      <td>{{ $item['subtotal'] }}</td>
    </tr>
    @endforeach
    @endforeach
  </tbody>
</table>