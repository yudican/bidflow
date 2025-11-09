<?php

namespace App\Jobs;

use App\Models\CompanyUser;
use App\Models\Notification;
use App\Models\OrderManual;
use App\Models\OrderSubmitLogDetail;
use App\Models\OrderTransfer;
use App\Models\InventoryProductStock;
use App\Models\InventoryDetailItem;
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

class ImportTransferKonsinyasiQueue implements ShouldQueue
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
            $payment_term = DB::table('payment_terms')->where('name', 'like', '%' . $item['payment_term'] . '%')->select('id')->first();
            $sales = DB::table('users')->where('name', 'like', '%' . $item['sales'] . '%')->select('id')->first();
            $created_user = DB::table('users')->where('name', 'like', '%' . $item['created by'] . '%')->select('id')->first();
            $contact = DB::table('users')->where('uid', $item['customer code'])->select('id')->first();
            $address_users = DB::table('address_users')->where('user_id', $contact?->id)->where('is_default', 1)->select('id')->first();
            if (!$address_users) {
                $address_users = DB::table('address_users')->where('user_id', $contact?->id)->select('id')->first();
            }
            $code = 6;
            $order_number = OrderTransfer::generateOrderNumber($code, $key);
            $invoice_number = OrderTransfer::generateInvoiceNumber($code, $key);
            $so_ethix = $this->generateSONumber();
            $uid_inventory = generateUid();

            $datas = [
                'uid_lead' => $uid_inventory,
                'title' => $order_number,
                'order_number' => $order_number,
                'invoice_number' => $invoice_number,
                // 'transfer_number' => $so_ethix,
                'contact'  => $this->contact ?? $contact?->id,
                'sales'  => $sales?->id ?? $user?->id,
                'warehouse_id' => $item['warehouse id'],
                'master_bin_id' => $item['destinasi bin id'],
                'payment_term'  => $payment_term?->id,
                'preference_number' => $item['no_preferences'],
                'notes' => $item['notes'],
                'status'  => 'draft',
                'company_id' => $company_id,
                'user_created' => $created_user?->id ?? $user?->id,
            ];

            $order = OrderTransfer::create($datas);

            InventoryProductStock::create([
                'uid_inventory' => $uid_inventory,
                'uid_lead' => $uid_inventory,
                'allocated_by' => $order->user_created,
                'warehouse_id' => $item['warehouse id'],
                'master_bin_id' => $item['destinasi bin id'],
                'reference_number' => isset($item['no preferences']) ? $item['no preferences'] : '',
                'created_by' => $order->user_created,
                'inventory_status' => 'draft',
                'inventory_type' => 'konsinyasi',
                'status' => 'draft',
                'received_date' => date('Y-m-d'),
                'note' => isset($item['notes']) ? $item['notes'] : '',
                'company_id' => $order->company_id ?? 1,
                // 'so_ethix' => $so_ethix,
                'post_ethix' => 0,
                'is_konsinyasi' => '1',
                'transfer_category' => isset($item['kategori_data']) ? $item['kategori_data'] : 'new'
            ]);

            foreach ($item['items'] as $index => $row) {
                $product = DB::table('products')->where('name', 'like', '%' . $row['nama product'] . '%')->select('id')->first();
                DB::table('inventory_detail_items')->insert([
                    'uid_inventory' => $uid_inventory,
                    'product_id' => $product?->id,
                    'qty' => $row['qty'],
                    'price_nego' => isset($row['harga_satuan']) ? $row['harga_satuan'] * $row['qty'] : 0,
                    'tax_id' => isset($row['tax id']) ? $row['tax id'] : null,
                    'discount' => isset($row['diskon rp']) ? $row['diskon rp'] : 0,
                    'from_warehouse_id' => $order->warehouse_id,
                    'master_bin_id' => $order->master_bin_id
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

                    createNotification('OPR-IMPORT-TF', ['user_id' => $user?->id], [
                        'type' => 'Transfer Konsinyasi',
                        'status' => 'Berhasil',
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
                    'refresh' => true,
                    'error' => true
                ]);

                try {
                    createNotification('OPR-IMPORT-TF', ['user_id' => $user?->id], [
                        'type' => 'Transfer Konsinyasi',
                        'status' => 'Gagal',
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

    public function generateSONumber()
    {
        $lastPo = InventoryProductStock::whereNotNull('so_ethix')->orderBy('id', 'desc')->first();
        $number = '0001';
        if ($lastPo) {
            $number = substr($lastPo->so_ethix, -4);
            $number = (int) $number + 1;
            $number = str_pad($number, 4, '0', STR_PAD_LEFT);
        }
        return 'TF/FIS/WAREHOUSE/' . $number;
    }
}
