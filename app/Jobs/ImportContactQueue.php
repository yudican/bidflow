<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Company;
use App\Models\OrderSubmitLogDetail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class ImportContactQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $row;
    protected $user;
    protected $submitLog_id;
    protected $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $user, $submitLog_id, $file)
    {
        $this->row = $row;
        $this->user = $user;
        $this->submitLog_id = $submitLog_id;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $row = $this->row;
        $user = DB::table('users')->where('id', $this->user)->select('id', 'name')->first();
        $submitLog_id = $this->submitLog_id;
        $file = $this->file;
        $key_progress = 'import-contact-' . $user?->id;

        try {
            DB::beginTransaction();

            // Basic validation
            if (
                empty($row['nama']) || empty($row['cs_code']) || empty($row['email']) ||
                empty($row['hp']) || empty($row['jk']) || empty($row['bod']) || empty($row['brand'])
            ) {
                throw new \Exception('Data mandatory tidak lengkap');
            }

            // Validate cs_code spaces
            if (strpos($row['cs_code'], ' ') !== false) {
                throw new \Exception('Customer Code tidak boleh mengandung spasi');
            }

            $nama = $row['nama'];
            $uid = $row['cs_code'];
            $email = $row['email'];

            // Created by validation
            $created_user = @$row['created_by'];
            $user_created = User::where('name', 'like', "%$created_user%")->first(['id']);
            $created_by = $user_created ? $user_created->id : '963b12db-5dbf-4cd5-91f7-366b2123ccb9';

            // Email uniqueness check
            $user_check = User::where('email', $email)->first(['id']);
            if ($user_check) {
                throw new \Exception("Email $email Sudah terdaftar");
            }

            // Customer code uniqueness check
            $user_uid_check = User::where('uid', $uid)->first(['id']);
            if ($user_uid_check) {
                throw new \Exception("Customer Code $uid Sudah terdaftar");
            }

            // Brand validation
            $brand = Brand::where('name', 'like', '%' . @$row['brand'] . '%')->first();
            if (!$brand) {
                throw new \Exception("Brand tidak ditemukan");
            }

            // Create user data
            $userData = [
                'name' => $nama,
                'uid' => $uid,
                'email' => $row['email'],
                'telepon' => formatPhone($row['hp']),
                'gender' => $row['jk'],
                'bod' => $row['bod'],
                'brand_id' => $brand->id,
                'sales_channel' => $row['sales_tag'] ?? 'modern-store',
                'created_by' => $created_by
            ];

            // Handle role
            $role_name = $row['role'] ?? 'Member';
            $role = Role::where('role_name', $role_name)->first();

            // Create user and assign role
            $user = User::updateOrCreate(['email' => $email, 'uid' => $uid], $userData);
            if (!empty($role)) {
                $user->roles()->sync($role->id);
                $user->teams()->sync(1, ['role' => $role->role_type]);
            }

            // Create company data
            $companyData = [
                'name' => $nama,
                'email' => $row['email'],
                'phone' => $row['hp'],
                'brand_id' => $brand->id,
                'status' => '1',
                'user_id' => $user->id,
                'need_faktur' => $row['need_faktur'] ?? null,
                'npwp_name' => $row['nama_npwp'] ?? null,
                'npwp' => $row['no_npwp'] ?? null,
            ];

            Company::updateOrCreate(['user_id' => $user->id], $companyData);

            // Update import log
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $uid
            ], [
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $uid,
                'status' => 'success',
                'error_message' => "Contact berhasil di import"
            ]);

            // Update progress
            $this->updateProgress($key_progress, $user, $file, true);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            // Log error
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $row['cs_code'] ?? $submitLog_id
            ], [
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $row['cs_code'] ?? $submitLog_id,
                'status' => 'failed',
                'error_message' => $th->getMessage()
            ]);

            // Update progress with error
            $this->updateProgress($key_progress, $user, $file, false, $th->getMessage());
        }
    }

    /**
     * Update import progress using Pusher
     */
    private function updateProgress($key_progress, $user, $file, $success = true, $error_message = null)
    {
        $pusher = new Pusher(
            'f01866680101044abb79',
            '4327409f9d87bdc35960',
            '1887006',
            [
                'cluster' => 'ap1',
                'useTLS' => true
            ]
        );

        $total_import = (int) getSetting($key_progress) ?? 1;
        $total_import = $total_import > 0 ? $total_import : 1;
        $total_success = (int) getSetting($key_progress . '-progress') ?? 0;
        setSetting($key_progress . '-progress', $total_success + 1);
        $total_success = $total_success + 1;
        $total_success = $total_success > 0 ? $total_success : 1;

        if ($total_success >= $total_import) {
            removeSetting($key_progress . '-progress');
            removeSetting($key_progress);

            $pusherData = [
                'progress' => $total_success,
                'total' => $total_import,
                'percentage' => round(($total_success / $total_import) * 100),
                'refresh' => true
            ];

            if (!$success) {
                $pusherData['error'] = true;
                $pusherData['error_message'] = $error_message;
            }

            $pusher->trigger('bidflow', $key_progress, $pusherData);

            try {
                createNotification('OPR-IMPORT-CONTACT', ['user_id' => $user?->id], [
                    'status' => $success ? 'berhasil' : 'gagal',
                    'created_by_name' => $user?->name,
                    'total_contact' => $total_import,
                    'created_at' => formatTanggalIndonesia(now(), 'l, d F Y H:i:s'),
                    'file_url' => getImage($file),
                    'file_name' => str_replace('file/', '', $file)
                ]);
            } catch (\Throwable $th) {
                // Handle notification error
            }
        } else {
            $pusher->trigger('bidflow', $key_progress, [
                'progress' => $total_success,
                'total' => $total_import,
                'percentage' => round(($total_success / $total_import) * 100),
                'refresh' => false
            ]);
        }
    }
}
