<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\User;
use App\Models\UserData;
use App\Models\RoleUser;
use App\Models\AddressUser;
use App\Models\Company;
use App\Models\Brand;
use App\Models\Role;
use App\Models\MasterBin;
use App\Models\MasterBinUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BinImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $chunkSize = 50;
        $chunks = $rows->chunk($chunkSize);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                
                // Validasi inputan
                if (empty($row['kabupaten']) || empty($row['provinsi'])) {
                    continue; // Lewatkan baris jika ada inputan yang kosong
                }
                // echo"<pre>";print_r($row);die();

                // Validate the district (kabupaten) and province (provinsi)
                $prov_dc = DB::table('addr_provinsi')->where('nama', 'like', '%' . @$row['provinsi'] . '%')->first();
                if (!$prov_dc) {
                    $this->rowsWithLocationNotMatch[] = [
                        'name' => @$row['name'],
                        'msg' => 'Provinsi tidak sesuai'
                    ];
                    continue;
                }

                $kab_dc = DB::table('addr_kabupaten')->where('nama', 'like', '%' . @$row['kabupaten'] . '%')->where('prov_id', @$prov_dc->pid)->first();
                if (!$kab_dc) {
                    $this->rowsWithLocationNotMatch[] = [
                        'name' => @$row['name'],
                        'msg' => 'Kabupaten/Kota tidak sesuai'
                    ];
                    continue;
                }

                $kec_dc = DB::table('addr_kecamatan')->where('nama', 'like', '%' . @$row['kecamatan'] . '%')->where('kab_id', @$kab_dc->pid)->first();
                if (!$kec_dc) {
                    $this->rowsWithLocationNotMatch[] = [
                        'name' => @$row['name'],
                        'msg' => 'Kecamatan tidak sesuai'
                    ];
                    continue;
                }

                $kel_dc = DB::table('addr_kelurahan')->where('nama', 'like', '%' . @$row['kelurahan'] . '%')->where('kec_id', @$kec_dc->pid)->first();
                if (!$kel_dc) {
                    $this->rowsWithLocationNotMatch[] = [
                        'name' => @$row['name'],
                        'msg' => 'Kelurahan tidak sesuai'
                    ];
                    continue;
                }

                $prov_dc = DB::table('addr_provinsi')->where('nama', 'like', '%' . $row['provinsi'] . '%')->first();
                if ($prov_dc) {
                    $kab_dc = DB::table('addr_kabupaten')->where('nama', 'like', '%' . $row['kabupaten'] . '%')->where('prov_id', @$prov_dc->pid)->first();
                }

                if ($kab_dc) {
                    $kec_dc = DB::table('addr_kecamatan')->where('nama', 'like', '%' . $row['kecamatan'] . '%')->where('kab_id', @$kab_dc->pid)->first();
                }
                if ($kec_dc) {
                    $kel_dc = DB::table('addr_kelurahan')->where('nama', 'like', '%' . $row['kelurahan'] . '%')->where('kec_id', @$kec_dc->pid)->first();
                }

                $data = [
                    'name'  => @$row['name'],
                    'location'  => @$row['type'],
                    'address'  => @$row['alamat'],
                    'status'  => 1,
                    'telepon'  => @$row['telepon'],
                    'provinsi_id' => $prov_dc ? @$prov_dc->pid : null,
                    'kabupaten_id' => $kab_dc ? @$kab_dc->pid : null,
                    'kecamatan_id' => $kec_dc ? @$kec_dc->pid : null,
                    'kelurahan_id' => $kel_dc ? @$kel_dc->pid : null,
                    'kodepos'  => @$row['kodepos'],
                    'created_by'  => auth()->user()->id,
                ];
                $check_bin = MasterBin::where('name', 'like', '%' . @$row['name'] . '%')->first();
                if (!empty($check_bin)) {
                    $master_bin = MasterBin::where('id', $check_bin->id)->update($data);
                } else {
                    $master_bin = MasterBin::create($data);
                }
                $user = User::where('uid', 'like', '%' . @$row['customer_code'] . '%')->first();
                $data_user = [
                    'master_bin_id' => @$master_bin->id,
                    'user_id' => @$user->id,
                    'status' => 1
                ];
                $master_bin_user = MasterBinUser::create($data_user);
            }
        }

        return true;
    }

    public function getRowsWithLocationNotMatch()
    {
        return $this->rowsWithLocationNotMatch;
    }
}
