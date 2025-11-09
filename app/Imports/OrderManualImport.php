<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\User;
use App\Models\AddressUser;
use App\Models\OrderManual;
use App\Models\PaymentTerm;
use App\Models\ProductNeed;
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

class OrderManualImport implements ToCollection, WithChunkReading, WithHeadingRow, WithBatchInserts
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
        // echo"<pre>";print_r($rows[0]['code_so']);die();
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
                $reference_number = @$row['reference_number'];
                $notes = @$row['notes'];
                if (empty($code_so) || empty($uid) || empty($nama_product) || empty($qty) || empty($price_nego)) {
                    continue; // Lewatkan baris jika ada inputan yang kosong
                }
                // echo"<pre>";print_r($row);die();


                $product = ProductVariant::where('name', 'like', '%' . $nama_product . '%')->first(['id', 'name']);
                $sales = User::where('name', 'like', '%' . $sales . '%')->first(['id', 'name']);
                $contact = User::where('uid', $uid)->first(['id', 'name']);
                $user_created = $sales->id ?? $user?->id;
                if ($sales != $created_by) {
                    $user_created = User::where('name', 'like', '%' . $created_by . '%')->first(['id', 'name']);
                    $user_created = $user_created?->id ?? $user?->id;
                }

                $payment_term = PaymentTerm::where('name', 'like', '%' . $payment . '%')->first(['id', 'name']);
                $userAddress = AddressUser::where('user_id', @$contact->id)->orWhere('is_default', 1)->first(['id']);

                if (empty($uid)) {
                    OrderSubmitLogDetail::updateOrCreate([
                        'order_submit_log_id' => $orderSi->id,
                        'order_id' => $code_so
                    ], [
                        'order_submit_log_id' => $orderSi->id,
                        'order_id' => $code_so,
                        'status' => 'failed',
                        'error_message' => $uid . ' Custommer Code Tidak Terdaftar'
                    ]);
                    continue;
                }


                // echo"<pre>";print_r($code_so);die();
                $datas[$code_so] = [
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
                    'type' => 'manual',
                    'user_created' => $user_created,
                    'reference_number' => $reference_number,
                    'notes' => $notes,
                    'user_created' => $user_created,
                    'expired_at' => $expired_at,
                    'company_id' => $user->company_id, // Ganti sesuai kebutuhan
                ];

                $product_needs[$code_so][] = [
                    'product_id' => $product?->id, // Ganti dengan metode yang sesuai
                    'qty' => $qty,
                    'price' => $price_nego, // Ganti dengan metode yang sesuai
                    'discount_id' => null, // Ganti dengan nilai yang sesuai
                    'user_created' => $user_created,
                    'user_updated' => $user_created,
                    'status' => 1, // Ganti dengan nilai yang sesuai
                    'price_type' => 'product',
                    'tax_id' => $user->company_id == 1 ? 1 : null, // Ganti dengan nilai yang sesuai
                    'discount' => $diskon_rp
                ];
            }

            // echo"<pre>";print_r($datas);die();
            setSetting('TOTAL_IMPORT_DATA', count($datas));
            foreach ($datas as $key => $data) {
                $order_number = OrderManual::generateOrderNumber(2, $key);
                $data['uid_lead'] = str_replace('SO/2024/', '', $order_number) . $key;
                $data['title'] = $order_number;
                $data['order_number'] = $order_number;
                $data['input_type'] = 'import';
                $data['invoice_number'] = OrderManual::generateInvoiceNumber(2, $key);
                // $data['created_at'] = '2024-08-30 10:46:00';
                // $data['updated_at'] = '2024-08-30 10:46:00';
                $order = OrderManual::create($data);

                foreach ($product_needs[$key] as $value) {
                    ProductNeed::updateOrCreate([
                        'uid_lead' => $order->uid_lead,
                        'product_id' => $value['product_id'],
                    ], array_merge([
                        'uid_lead' => $order->uid_lead,
                        // 'created_at' => '2024-08-30 10:46:00',
                        // 'updated_at' => '2024-08-30 10:46:00',
                    ], $value));
                }
                UpdatePriceQueue::dispatch($order, 'import')->onQueue('queue-backend');
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
