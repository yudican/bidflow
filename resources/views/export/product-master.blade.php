<table border="1" width="100">
  <thead>
    <tr>
      <th>No.</th>
      <th>ID</th>
      <th>Product</th>
      <th>SKU</th>
      <th>Berat</th>
      <th>Status</th>
      <th>Deskripsi</th>
      <th>Warehouse</th>
      <th>Stock</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @if (count($row['stock_warehouse']))
    @foreach ($row['stock_warehouse'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $row['id'] }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $row['name'] }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $row['sku'] }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $row['weight'] }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{{ $row['status'] }}</td>
      <td rowspan="{{count($row['stock_warehouse'])}}">{!! $row['description'] !!}</td>
      @endif
      <td>{{ $item['warehouse_name'] }}</td>
      <td>{{ $item['stock'] }}</td>
    </tr>
    @endforeach
    @else
    <tr>
      <td>{{ $key +1 }}</td>
      <td>{{ @$row['id'] }}</td>
      <td>{{ @$row['name'] }}</td>
      <td>{{ @$row['sku'] }}</td>
      <td>{{ @$row['weight'] }}</td>
      <td>{{ @$row['status'] }}</td>
      <td>{!! @$row['description'] !!}</td>
      <td>-</td>
      <td>-</td>
    </tr>
    @endif
    @endforeach
  </tbody>
</table>