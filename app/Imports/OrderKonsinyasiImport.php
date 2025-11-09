<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AddressUser;
use App\Models\OrderManual;
use App\Models\PaymentTerm;
use App\Models\ProductNeed;
use App\Models\OrderTransfer;
use App\Jobs\UpdatePriceQueue;
use App\Models\OrderSubmitLog;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use App\Models\OrderSubmitLogDetail;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;

class OrderKonsinyasiImport implements ToCollection, WithChunkReading, WithHeadingRow, WithBatchInserts
{
    use Importable;
    protected $contact;

    public function setContact($contact = null)
    {
        $this->contact = $contact;
        return $this; // Method chaining for convenience
    }

    public function batchSize(): int
    {
        return 100; // Sesuaikan dengan kebutuhan
    }

    public function chunkSize(): int
    {
        return 500; // Sesuaikan dengan kebutuhan
    }

    public function collection(Collection $rows)
    {
        // echo"<pre>";print_r($rows);die();
        if (!auth()->check()) {
            return null;
        }
        try {
            DB::beginTransaction();
            $user = auth()->user();

            $orderSi = OrderSubmitLog::create([
                'submited_by' => $user?->id,
                'type_si' => 'import-so',
                'vat' => 0,
                'tax' => 0,
                'ref_id' => null
            ]);

            $datas = [];
            $product_needs = [];
            foreach ($rows as $key => $row) {
                $code_so = @$row['code_so'];
                $uid = @$row['uid'];
                $nama_product = @$row['nama_product'];
                $qty = @$row['qty'];
                $price_nego = @$row['harga_satuan'] * $qty;
                $warehouse_id = @$row['warehouse_id'];
                $sales = @$row['sales'];
                $payment = @$row['payment_term'];
                $diskon_rp = @$row['diskon_rp'] ?? 0;
                $created_by = @$row['created_by'];
                $expired_at = @$row['expired_at'];
                $so_konsinyasi = @$row['so_konsinyasi'];
                $no_preferences = @$row['no_preferences'];
                $tax_id = @$row['tax_id'];
                $bin_id = @$row['bin_id'];
                $id_konsinyasi = null;

                if (empty($code_so) || empty($uid) || empty($nama_product) || empty($qty) || empty($price_nego)) {
                    continue; // Lewatkan baris jika ada inputan yang kosong
                }

                $product = ProductVariant::where('name', 'like', '%' . $nama_product . '%')->first(['id', 'name']);
                // echo"<pre>";print_r($product);die();
                $sales = User::where('name', 'like', '%' . $sales . '%')->first(['id', 'name']);
                $contact = User::where('uid', $uid)->first(['id', 'name']);
                $user_created = $sales->id ?? $user?->id;
                if ($sales != $created_by) {
                    $user_created = User::where('name', 'like', '%' . $created_by . '%')->first(['id', 'name']);
                    $user_created = $user_created?->id ?? $user?->id;
                }

                $payment_term = PaymentTerm::where('name', 'like', '%' . $payment . '%')->first(['id', 'name']);
                $userAddress = AddressUser::where('user_id', @$contact->id)->orWhere('is_default', 1)->first(['id']);

                $konsinyasi = OrderTransfer::where('order_number', $so_konsinyasi)->first(['id']);
                if ($konsinyasi) {
                    $id_konsinyasi = $konsinyasi->id;
                }

                if (empty($uid)) {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $orderSi->id,
                        'order_id' => 1
                    ], [
                        'order_submit_log_id' => $orderSi->id,
                        'order_id' => 1,
                        'status' => 'failed',
                        'error_message' => $uid . ' Custommer Code Tidak Terdaftar'
                    ]);

                    continue;
                }

                // echo"<pre>";print_r($code_so);die();
                $uid_lead = hash('crc32', Carbon::now()->format('U') . $code_so);
                $datas[$code_so] = [
                    'transfer_number' => $so_konsinyasi,
                    'preference_number' => $no_preferences,
                    'kode_unik' => $this->getUniqueCodeLead(),
                    'temp_kode_unik' => $this->getUniqueCodeLead(),
                    'brand_id' => 8, // Ganti sesuai kebutuhan
                    'contact' => $this->contact ?? $contact?->id, // Pastikan Anda memiliki $userContact yang sesuai
                    'sales' => $sales?->id ?? $user?->id,
                    'payment_term' => $payment_term?->id,
                    'customer_need' => '',
                    'status' => '1',
                    'warehouse_id' => $warehouse_id, // Ganti sesuai kebutuhan
                    'type_customer' => 'existing',
                    'address_id' => $userAddress?->id,
                    'type' => 'konsinyasi',
                    'user_created' => $user_created,
                    'expired_at' => $expired_at,
                    'id_konsinyasi' => $id_konsinyasi,
                    'company_id' => $user?->company_id ?? 1, // Ganti sesuai kebutuhan
                    'master_bin_id' => $bin_id,
                    'order_type'=> $order_type
                ];

                $product_needs[$code_so][] = [
                    'product_id' => $product?->id, // Ganti dengan metode yang sesuai
                    'qty' => $qty,
                    'price' => $price_nego, // Ganti dengan metode yang sesuai
                    'discount_id' => null, // Ganti dengan nilai yang sesuai
                    'tax_id' => $tax_id, // Ganti dengan nilai yang sesuai
                    'user_created' => $user_created,
                    'user_updated' => $user_created,
                    'status' => 1, // Ganti dengan nilai yang sesuai
                    'price_type' => 'product',
                    'tax_id' => $user?->company_id == 1 ? 1 : null,
                    'discount' => $diskon_rp
                ];
            }

            // echo"<pre>";print_r($datas);die();
            foreach ($datas as $key => $data) {
                $order_number = OrderManual::generateOrderNumber(4, $key);
                $data['uid_lead'] = str_replace('SO/2024/', '', $order_number) . $key;
                $data['title'] = $order_number;
                $data['order_number'] = $order_number;
                $data['input_type'] = 'import';
                $data['invoice_number'] = OrderManual::generateInvoiceNumber(4, $key);
                $order = OrderManual::create($data);

                foreach ($product_needs[$key] as $value) {
                    ProductNeed::updateOrCreate([
                        'uid_lead' => $order->uid_lead,
                        'product_id' => $value['product_id'],
                    ], array_merge([
                        'uid_lead' => $order->uid_lead,
                    ], $value));
                }
                UpdatePriceQueue::dispatch($order)->onQueue('queue-backend');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            //throw $th;
        }

        return true;
    }

    private function generateOrderNo($no = 1)
    {
        $year = date('Y');
        $nomor = 'SO/' . $year . '/2' . date('mdH');

        return $nomor;
    }

    private function generateInvoiceNo()
    {
        $year = date('Y');
        $nomor = 'SI/' . $year . '/2' . date('mdH');

        return $nomor;
    }

    // get unique code 3 digit max 500 with auto increment
    private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
    {
        return 0;
    }
}
