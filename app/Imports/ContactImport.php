<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\User;
use App\Models\AddressUser;
use App\Models\Company;
use App\Models\Brand;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ContactImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $chunkSize = 50;
        $chunks = $rows->chunk($chunkSize);
        $import = OrderSubmitLog::create([
            'submited_by' => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
            'type_si' => 'import-contact',
            'vat' => 0,
            'tax' => 0,
        ]);
        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                // Validasi inputan
                if (empty($row['nama']) || empty($row['cs_code']) || empty($row['email']) || empty($row['hp']) || empty($row['jk']) || empty($row['bod']) || empty($row['brand'])) {
                    continue; // Lewatkan baris jika ada inputan yang kosong
                }

                // Validasi cs_code tidak mengandung spasi
                if (strpos($row['cs_code'], ' ') !== false) {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $row['cs_code'] // Jika cs_code yang memiliki spasi diterima
                    ], [
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $row['cs_code'],
                        'status' => 'failed',
                        'error_message' => "Customer Code tidak boleh mengandung spasi"
                    ]);

                    continue; // Lewatkan baris jika cs_code mengandung spasi
                }


                $nama = isset($row['nama']) ? $row['nama'] : null;
                $uid = isset($row['cs_code']) ? $row['cs_code'] : null;
                $email = isset($row['email']) ? $row['email'] : null;
                $created_user = @$row['created_by'];;
                $user_created = User::where('name', 'like', "%$created_user%")->first(['id']);
                $created_by = $user_created ? $user_created->id : '963b12db-5dbf-4cd5-91f7-366b2123ccb9';
                // cek email
                $user_check = User::where('email', $email)->first(['id']);

                if ($user_check) {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $uid
                    ], [
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $uid,
                        'status' => 'failed',
                        'error_message' => "Email $email Sudah terdaftar"
                    ]);

                    continue;
                }

                // cek customer code
                $user_uid_check = User::where('uid', $uid)->first(['id']);
                if ($user_uid_check) {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $uid
                    ], [
                        'order_submit_log_id' => @$import->id,
                        'order_id' => $uid,
                        'status' => 'failed',
                        'error_message' => "Customer Code $uid Sudah terdaftar"
                    ]);

                    continue;
                }


                $brand = Brand::where('name', 'like', '%' . @$row['brand'] . '%')->first();
                $userData = [
                    'name' => $nama,
                    'uid' => $uid,
                    'email' => @$row['email'],
                    'telepon' => formatPhone(@$row['hp']),
                    'gender' => @$row['jk'],
                    'bod' => @$row['bod'],
                    'brand_id' => $brand ? $brand->id : 1,
                    'sales_channel' => 'modern-store',
                    'created_by' => $created_by,
                    'sales_channel' => @$row['sales_tag']
                ];

                $role_name = isset($row['role']) ? $row['role'] : 'Member';
                $role = Role::where('role_name', $role_name)->first();
                $user = User::updateOrCreate(['email' => $email, 'uid' => $uid], $userData);
                if (!empty($role)) {
                    $user->roles()->sync(@$role->id);
                    $user->teams()->sync(1, ['role' => $role->role_type]);
                }

                $data = [
                    'name' => $nama,
                    'email' => @$row['email'],
                    'phone' => @$row['hp'],
                    'brand_id' => $brand ? $brand->id : 1,
                    'status' => '1',
                    'user_id' => @$user->id,
                    'need_faktur' => @$row['need_faktur'],
                    'npwp_name' => @$row['nama_npwp'],
                    'npwp' => @$row['no_npwp'],
                ];


                Company::updateOrCreate(['user_id' => @$user->id], $data);

                OrderSubmitLogDetail::updateOrCreate([
                    'order_submit_log_id' => @$import->id,
                    'order_id' => @$user_uid_check->id
                ], [
                    'order_submit_log_id' => @$import->id,
                    'order_id' => @$user_uid_check->id,
                    'status' => 'success',
                    'error_message' => "Contact berhasil di import"
                ]);
            }
        }

        return true;
    }
}
