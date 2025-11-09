<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Received Number</th>
      <th>Doc Number</th>
      <th>Invoice Date</th>
      <th>Created By</th>
      <th>Vendor</th>
      <th>Type Invoice</th>
      <th>Status</th>
      <th>PO Number</th>
      <th>Product name</th>
      <th>Amount</th>
      <th>QTY</th>
      <th>SKU</th>
      <th>PPN</th>
      <th>SUBTOTAL</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @foreach ($row['product'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['product'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['received_number'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['doc_number'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['invoice_date'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['created_by'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['vendor'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['type_invoice'] }}</td>
      <td rowspan="{{count($row['product'])}}">{{ $row['status'] }}</td>
      @endif
      <td>{{ $item['po_number'] }}</td>
      <td>{{ $item['product_name'] }}</td>
      <td>{{ $item['extended_cost'] }}</td>
      <td>{{ $item['qty'] }}</td>
      <td>{{ $item['sku'] }}</td>
      <td>{{ $item['ppn'] }}</td>
      <td>{{ $item['subtotal'] }}</td>
    </tr>
    @endforeach
    @endforeach
  </tbody>
</table>