<?php

namespace App\Jobs;

use App\Models\CompanyUser;
use App\Models\Notification;
use App\Models\OrderManual;
use App\Models\OrderSubmitLogDetail;
use App\Models\OrderTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class ImportSalesOrderQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $item;
    protected $type;
    protected $key;
    protected $user;
    protected $submitLog_id;
    protected $file;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($item, $type, $key, $user, $submitLog_id, $file)
    {
        $this->item = $item;
        $this->type = $type;
        $this->key = $key;
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
        $item = $this->item;
        $type = $this->type;
        $key = $this->key;
        $user = DB::table('users')->where('id', $this->user)->select('id', 'name')->first();
        $company_id = CompanyUser::where('user_id', $user?->id)->first(['company_id'])?->company_id ?? 1;
        $submitLog_id = $this->submitLog_id;
        $file = $this->file;
        $key_progress = 'import-so-' . $type . '-' . $user?->id;
        $order = null;
        try {
            DB::beginTransaction();
            $payment_term = isset($item['payment_term']) ? $item['payment_term'] : null;
            $payment_term = DB::table('payment_terms')->where('name', 'like', '%' . $payment_term . '%')->select('id')->first();


            $sales = DB::table('users')->where('name', 'like', '%' . $item['sales'] . '%')->select('id')->first();
            $created_user = DB::table('users')->where('name', 'like', '%' . $item['created by'] . '%')->select('id')->first();
            $contact = DB::table('users')->where('uid', $item['customer code'])->select('id')->first();
            $address_users = DB::table('address_users')->where('user_id', $contact?->id)->where('is_default', 1)->select('id')->first();
            if (!$address_users) {
                $address_users = DB::table('address_users')->where('user_id', $contact?->id)->select('id')->first();
            }
            $kode_so = isset($item['Code SO']) ? $item['Code SO'] : $key;
            $code = $type == 'manual' ? 2 : 3;
            $code = $type == 'konsinyasi' ? 4 : $code;
            $warehouse_id = $type == 'konsinyasi' ? 19 : null;
            $order_number = OrderManual::generateOrderNumber($code, $kode_so);
            $invoice_number = OrderManual::generateInvoiceNumber($code, $kode_so);
            $datas = [
                'uid_lead' => generateUid($kode_so),
                'title' => $order_number,
                'order_number' => $order_number,
                'input_type' => 'import',
                'invoice_number' => $invoice_number,
                // 'kode_unik' => $this->getUniqueCodeLead(),
                // 'temp_kode_unik' => $this->getUniqueCodeLead(),
                'brand_id' => 8, // Ganti sesuai kebutuhan
                'contact' => $this->contact ?? $contact?->id, // Pastikan Anda memiliki $userContact yang sesuai
                'sales' => $sales?->id ?? $user?->id,
                'payment_term' => $type == 'konsinyasi' ? 4 : $payment_term?->id,
                'customer_need' => '',
                'status' => '1',
                'warehouse_id' => isset($item['warehouse id']) ? $item['warehouse id'] : $warehouse_id, // Ganti sesuai kebutuhan
                'type_customer' => 'existing',
                'address_id' => $address_users?->id,
                'type' => $type,
                'user_created' => $created_user?->id ?? $user?->id,
                'reference_number' => isset($item['reference_number']) ? $item['reference_number'] : null,
                'preference_number' => isset($item['reference_number']) ? $item['reference_number'] : null,
                'notes' => isset($item['Notes']) ? $item['Notes'] : null,
                'expired_at' => isset($item['expired at']) ? Carbon::parse($item['expired at']) : Carbon::now()->addDays(1),
                'company_id' => $company_id, // Ganti sesuai kebutuhan
                'import_log_id' => $submitLog_id,
                'batch_code' => isset($item['Code SO']) ? $item['Code SO'] : 0,
                'order_type' => isset($item['kategori_data']) ? $item['kategori_data'] : null
            ];

            if ($type == 'konsinyasi') {
                if (isset($item['destinasi bin id'])) {
                    $datas['master_bin_id'] = $item['destinasi bin id'];
                }
            }

            $order = OrderManual::create($datas);

            foreach ($item['items'] as $index => $row) {
                $product = DB::table('product_variants')->where('name', 'like', '%' . $row['nama product'] . '%')->select('id')->first();
                DB::table('product_needs')->insert([
                    'uid_lead' => $order->uid_lead,
                    'product_id' => $product?->id, // Ganti dengan metode yang sesuai
                    'qty' => $row['qty'],
                    'price' => isset($row['harga_satuan']) ? $row['harga_satuan'] * $row['qty'] : 0, // Ganti dengan metode yang sesuai
                    'discount_id' => null, // Ganti dengan nilai yang sesuai
                    'user_created' => $order->user_created,
                    'user_updated' => $order->user_created,
                    'status' => 1, // Ganti dengan nilai yang sesuai
                    'price_type' => 'product',
                    'tax_id' => isset($item['tax id']) ? $item['tax id'] : null, // Ganti dengan nilai yang sesuai
                    'discount' => isset($row['diskon rp']) ? $row['diskon rp'] : 0
                ]);
            }

            // progress data
            $pusher = new Pusher(
                'f01866680101044abb79',
                '4327409f9d87bdc35960',
                '1887006',
                [
                    'cluster' => 'ap1',
                    'useTLS' => true
                ]
            );
            // $key_progress = 'import-so-' . $type . '-' . $order->user_created;
            $total_import = (int) getSetting($key_progress);
            $total_import = $total_import > 0 ? $total_import : 1;
            $total_success = (int) getSetting($key_progress . '-progress') ?? 0;
            setSetting($key_progress . '-progress', $total_success + 1);
            $total_success = $total_success + 1;
            $total_success = $total_success > 0 ? $total_success : 1;
            if ($total_success >= $total_import) {
                removeSetting($key_progress . '-progress');
                removeSetting($key_progress);
                $pusher->trigger('bidflow', $key_progress, [
                    'progress' => $total_success,
                    'total' => $total_import,
                    'percentage' => round(($total_success / $total_import) * 100),
                    'refresh' => true
                ]);

                try {
                    createNotification('OPR-IMPORT-SO', ['user_id' => $user?->id], [
                        'type' => $type,
                        'status' => 'berhasil',
                        'created_by_name' => $user?->name,
                        'total_so' => $total_import,
                        'created_at' => formatTanggalIndonesia($order->created_at, 'l, d F Y H:i:s'),
                        'file_url' => getImage($file),
                        'file_name' => str_replace('file/', '', $file)
                    ]);
                } catch (\Throwable $th) {
                }
            } else {
                $pusher->trigger('bidflow', $key_progress, [
                    'progress' => $total_success,
                    'total' => $total_import,
                    'percentage' => round(($total_success / $total_import) * 100),
                    'refresh' => false
                ]);
            }

            if ($type != 'freebies') {
                UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $submitLog_id
            ], [
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $submitLog_id,
                'status' => 'failed',
                'error_message' => $th->getMessage()
            ]);

            // Pusher
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
                $pusher->trigger('bidflow', $key_progress, [
                    'progress' => $total_success,
                    'total' => $total_import,
                    'percentage' => round(($total_success / $total_import) * 100),
                    'refresh' => true,
                    'error' => true,
                    'error_message' => $th->getMessage()
                ]);

                try {
                    createNotification('OPR-IMPORT-SO', ['user_id' => $user?->id], [
                        'type' => $type,
                        'status' => 'gagal',
                        'created_by_name' => $user?->name,
                        'total_so' => $total_import,
                        'created_at' => formatTanggalIndonesia($order?->created_at, 'l, d F Y H:i:s'),
                        'file_url' => getImage($file),
                        'file_name' => str_replace('file/', '', $file)
                    ]);
                } catch (\Throwable $th) {
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

    private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
    {
        return rand(121, 999);
        $data = OrderManual::whereDate('created_at', date('Y-m-d'))->select($field)->orderBy('id', 'desc')->limit(1)->first();
        if ($data) {
            if ($data->$field > 0) {
                if ($data->$field == 500) {
                    $nomor = $prefix . '001';
                } else {
                    $awal = substr($data->$field, 3);
                    $next = sprintf("%03d", ((int)$awal + 1));
                    $nomor = $prefix . $next;
                }
            }
        } else {
            $nomor = $prefix . '001';
        }
        return $nomor;
    }
}
