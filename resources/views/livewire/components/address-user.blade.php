<div class="row">
    <div class="col-md-6">
        <x-text-field type="text" name="type" label="Alamat Sebagai *" placeholder="Contoh : Rumah, Kantor, dsb." />
    </div>

    <div class="col-md-6">
        <x-select name="provinsi_id" label="Provinsi *" handleChange="getKabupaten">
            <option value="">Pilih Provinsi</option>
            @foreach ($provinces as $provinsi)
            <option value="{{$provinsi->pid}}">{{$provinsi->nama}}</option>
            @endforeach
        </x-select>
    </div>
    <div class="col-md-6">
        <x-select name="kabupaten_id" label="Kota/Kabupaten *" handleChange="getKecamatan">
            <option value="">Pilih Kota/Kabupaten</option>
            @foreach ($kabupatens as $kab)
            <option value="{{is_array($kab) ? $kab['pid'] : $kab->pid}}">{{is_array($kab) ? $kab['nama'] : $kab->nama}}</option>
            @endforeach
        </x-select>
    </div>
    <div class="col-md-6">
        <x-select name="kecamatan_id" label="Kecamatan *" handleChange="getKelurahan">
            <option value="">Pilih Kecamatan</option>
            @foreach ($kecamatans as $kecamatan)
            <option value="{{is_array($kecamatan) ? $kecamatan['pid'] : $kecamatan->pid}}">{{is_array($kecamatan) ? $kecamatan['nama'] : $kecamatan->nama}}</option>
            @endforeach
        </x-select>
    </div>
    <div class="col-md-6">
        <x-select name="kelurahan_id" label="Kelurahan" handleChange="getKodepos">
            <option value="">Pilih Kelurahan</option>
            @foreach ($kelurahans as $kelurahan)
            <option value="{{is_array($kelurahan) ? $kelurahan['pid'] : $kelurahan->pid}}">{{is_array($kelurahan) ? $kelurahan['nama'] : $kelurahan->nama}}</option>
            @endforeach
        </x-select>

    </div>
    <div class="col-md-6">
        <x-text-field type="text" name="kode_pos" label="Kode Pos *" placeholder="Contoh : 19045" />
    </div>
    <div class="col-md-12">
        <x-textarea type="textarea" name="alamat" label="Alamat Lengkap *" placeholder="Jl. Asia Afrika No.38, Kembangan, Jakarta Barat." />
    </div>
    <div class="col-md-12">
        <x-textarea type="textarea" name="catatan" label="Catatan" placeholder="Silahkan masukkan catatan untuk pengiriman" />
    </div>
    <div class="col-md-12">
        <x-text-field type="text" name="nama" label="Nama Penerima *" placeholder="Nama Penerima" />
    </div>
    <div class="col-md-12">
        <x-text-field type="text" name="telepon" label="No. Handphone *" placeholder="No. Handphone" />
    </div>
</div>