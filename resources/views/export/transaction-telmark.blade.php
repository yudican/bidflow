<table border="1">
    <thead>
        <tr>
            <th>No.</th>
            <th>TRX ID</th>
            <th>Metode Pembayaran</th>
            <th>Metode Pengiriman</th>
            <th>Nama Customer</th>
            <th>Status</th>
            <th>Tanggal Transaksi</th>
            <th>Nama Produk</th>
            <th>SKU</th>
            <th>Harga Satuan</th>
            <th>QTY</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $row)
            @foreach ($row['product'] as $index => $item)
                <tr>
                    @if ($index == 0)
                        <td rowspan="{{ count($row['product']) }}">{{ $key + 1 }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['id_transaksi'] }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['payment_method_name'] }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['shipping_method'] }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['created_by'] }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['status'] }}</td>
                        <td rowspan="{{ count($row['product']) }}">{{ $row['created_date'] }}</td>
                    @endif
                    <td>{{ $item['product_name'] }}</td>
                    <td>{{ $item['sku'] }}</td>
                    <td>{{ $item['price'] }}</td>
                    <td>{{ $item['qty'] }}</td>
                    <td>{{ $item['subtotal'] }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
