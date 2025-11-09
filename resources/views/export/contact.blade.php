<table border="1">
  <thead>
    <tr>
      <th>No.</th>
      <th>Nama</th>
      <th>Email</th>
      <th>Telepon</th>
      <th>Jenis Kelamin</th>
      <th>BOD</th>
      <th>Role</th>
      <th>Customer Code</th>
      <th>Alamat</th>
      <th>Kelurahan</th>
      <th>Kecamatan</th>
      <th>Kabupaten</th>
      <th>Provinsi</th>
      <th>Kodepos</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $key => $row)
    @if (isset($row['address']) && count($row['address']) > 0)
    @foreach ($row['address'] as $index => $item)
    <tr>
      @if ($index == 0)
      <td rowspan="{{count($row['address'])}}">{{ $key +1 }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['name'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['email'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['telepon'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['gender'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['bod'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['role'] }}</td>
      <td rowspan="{{count($row['address'])}}">{{ $row['uid'] }}</td>
      @endif
      <td>{{ $item['alamat_detail'] }}</td>
      <td>{{ $item['kelurahan_nama'] }}</td>
      <td>{{ $item['kecamatan_nama'] }}</td>
      <td>{{ $item['kabupaten_nama'] }}</td>
      <td>{{ $item['provinsi_nama'] }}</td>
      <td>{{ $item['kodepos'] }}</td>
    </tr>
    @endforeach
    @else
    <tr>
      <td>{{ $key +1 }}</td>
      <td>{{ $row['name'] }}</td>
      <td>{{ $row['email'] }}</td>
      <td>{{ $row['telepon'] }}</td>
      <td>{{ $row['gender'] }}</td>
      <td>{{ $row['bod'] }}</td>
      <td>{{ $row['role'] }}</td>
      <td>{{ $row['uid'] }}</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
      <td>-</td>
    </tr>
    @endif

    @endforeach
  </tbody>
</table>