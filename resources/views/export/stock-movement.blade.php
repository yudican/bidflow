<table border="1">
    <thead>
        <tr>
            <th>No.</th>
            <th>Product Name</th>
            <th>SKU</th>
            <th>UoM</th>
            <th>Begin Stock</th>
            <th>In. Purchase Delivered</th>
            <th>In. Sales Return</th>
            <th>Out. Stock Order</th>
            <th>Out. Return To Suplier</th>
            <th>Out. Sales</th>
            <th>Out. Transfer Out</th>
            <th>End Stock</th>
            <th>End Forecast</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $key => $row)
        <tr>
            <th>{{ $key+1 }}</th>
            <th>{{ $row['product_name'] }}</th>
            <th>{{ $row['sku'] }}</th>
            <th>{{ $row['uom'] }}</th>
            <th>{{ $row['qty_begin_stock'] }}</th>
            <th>{{ $row['qty_delivered'] }}</th>
            <th>{{ $row['qty_sales_return'] }}</th>
            <th>{{ $row['qty_stock'] }}</th>
            <th>{{ $row['qty_return_suplier'] }}</th>
            <th>{{ $row['qty_sales'] }}</th>
            <th>{{ $row['qty_transfer_out'] }}</th>
            <th>{{ $row['qty_end_stock'] }}</th>
            <th>{{ $row['qty_end_forecast'] }}</th>
        </tr>
        @endforeach
    </tbody>
</table>