<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\InventoryDetailItem;
use App\Models\InventoryProductStock;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use App\Models\OrderTransfer;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TransferKonsinyasiImport implements ToCollection, WithChunkReading, WithHeadingRow, WithBatchInserts
{
  use Importable;
  public $created_by;

  public function __construct($created_by)
  {
    $this->created_by = $created_by;
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

    $user = auth()->user();

    $orderSi = OrderSubmitLog::create([
      'submited_by' => $user?->id,
      'type_si' => 'import-tf-konsinyasi',
      'vat' => 0,
      'tax' => 0,
      'ref_id' => null
    ]);

    $datas = [];
    $product_needs = [];
    foreach ($rows as $key => $row) {
      $code_so = @$row['code_so'];
      $nama_product = @$row['nama_product'];
      $qty = @$row['qty'];
      $price_nego = @$row['harga_satuan'] * $qty;
      $from_warehouse = @$row['from_warehouse'];
      // $destination_bin = @$row['destination_bin'];
      $destination_bin = @$row['bin_id'];
      $contact_name = @$row['contact'];
      $sales_name = @$row['sales'];
      $diskon_rp = @$row['diskon_rp'] ?? 0;
      $payment = @$row['payment_term'];
      $no_preferences = @$row['no_preferences'];
      $notes = @$row['notes'];
      $tax_id = @$row['tax_id'];
      $created_by = $this->created_by;
      if (empty($code_so) || empty($nama_product) || empty($qty) || empty($price_nego)) {
        continue; // Lewatkan baris jika ada inputan yang kosong
      }

      $product = ProductVariant::where('name', 'like', '%' . $nama_product . '%')->select('id', 'product_id', 'name', 'sku')->first();
      $contact = DB::table('users')->where('name', 'like', '%' . $contact_name . '%')->select('id', 'name')->first();
      $sales = DB::table('users')->where('name', 'like', '%' . $sales_name . '%')->select('id', 'name')->first();
      $user_created = $created_by;
      $warehouse = DB::table('warehouses')->where('name', 'like', "%$from_warehouse%")->select('id', 'name')->first();
      // $masterBin = DB::table('master_bins')->where('name', 'like', "%$destination_bin%")->select('id', 'name')->first();
      $payment_term = DB::table('payment_terms')->where('name', 'like', '%' . $payment . '%')->select('id', 'name')->first();
      $warehouse_id = $warehouse?->id;
      if (empty($contact)) {
        OrderSubmitLogDetail::updateOrCreate([
          'order_submit_log_id' => $orderSi->id,
          'order_id' => 1
        ], [
          'order_submit_log_id' => $orderSi->id,
          'order_id' => 1,
          'status' => 'failed',
          'error_message' => $contact_name . ' Contact Tidak Terdaftar'
        ]);

        continue;
      }

      // echo"<pre>";print_r($code_so);die();
      $so_number = $this->generateSOKNumber();
      $si_number = $this->generateSIKNumber();
      $datas[$code_so] = [
        'title' => $so_number,
        'order_number' => $so_number,
        'invoice_number' => $si_number,
        'contact'  => @$contact->id,
        'sales'  => $sales?->id,
        'warehouse_id' => $warehouse_id,
        // 'master_bin_id' => $masterBin?->id,
        'master_bin_id' => $destination_bin,
        'payment_term'  => $payment_term?->id,
        'preference_number' => $no_preferences ?? '-',
        'notes' => $notes,
        'status'  => 'draft',
        'company_id' => $user->company_id ?? 1,
      ];

      $product_needs[$code_so][] = [
        'product_id' => $product?->product_id,
        'qty' => $qty,
        'qty_alocation' => $qty,
        // 'from_warehouse_id' => $masterBin?->id,
        'from_warehouse_id' => $destination_bin,
        'master_bin_id' => $warehouse_id,
        'sku' => $product?->sku,
        'u_of_m' => $product?->u_of_m,
        'discount' => $diskon_rp,
        'discount_amount' => $diskon_rp * $qty,
        'price_nego' => $price_nego,
      ];
    }

    // echo"<pre>";print_r($datas);die();
    foreach ($datas as $key => $data) {
      $so_ethix = $this->generateSONumber();
      $uid_inventory = hash('crc32', Carbon::now()->format('U'));
      InventoryProductStock::create([
        'uid_inventory' => $uid_inventory,
        'uid_lead' => $uid_inventory,
        'allocated_by' => $user_created,
        'warehouse_id' => $data['warehouse_id'],
        'destination_warehouse_id' => 19,
        'master_bin_id' => $data['master_bin_id'],
        'reference_number' => '-',
        'created_by' => $user_created,
        'inventory_status' => 'draft',
        'inventory_type' => 'konsinyasi',
        'status' => 'draft',
        'received_date' => date('Y-m-d'),
        'note' => $notes,
        'company_id' => $user->company_id,
        'so_ethix' => $so_ethix,
        'post_ethix' => 0,
        'is_konsinyasi' => '1',
        'transfer_category' => 'new'
      ]);

      $order = OrderTransfer::updateOrCreate(['uid_lead' => $uid_inventory], array_merge($data, ['uid_inventory' => $uid_inventory, 'uid_lead' => $uid_inventory, 'transfer_number' => $so_ethix]));

      foreach ($product_needs[$key] as $key => $value) {
        InventoryDetailItem::create(array_merge([
          'uid_inventory' => $order->uid_inventory,
          'from_warehouse_id' => $data['warehouse_id'],
          'to_warehouse_id' => 19,
          'master_bin_id' => $data['master_bin_id'],
          'tax_id' => $tax_id
        ], $value));
      }
    }

    return true;
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

  public function generateSOKNumber()
  {
    $username = auth()->user()->username ?? 99;
    $year = date('Y');
    $nomor = 'SO/' . $year . '/2' . $username . '000001';
    $rw = OrderTransfer::whereNotNull('order_number')->orderBy('id', 'desc')->orderBy('order_number', 'desc')->first(['order_number']);
    if ($rw) {
      $awal = substr($rw->order_number, -6);
      $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
      $nomor = 'SO/' . $year . '/' . $next;

      $row = OrderTransfer::where('order_number', $nomor)->first(['order_number']);
      if ($row) {
        $nomor = 'SO/' . $year . '/' . $next + 1;
      }
      return $nomor;
    }

    return $nomor;
  }

  public function generateSIKNumber()
  {
    $username = auth()->user()->username ?? 99;
    $year = date('Y');
    $nomor = 'SJ/' . $year . '/2' . $username . '000001';
    $rw = OrderTransfer::whereNotNull('invoice_number')->orderBy('id', 'desc')->orderBy('invoice_number', 'desc')->first();

    if ($rw) {
      $awal = substr($rw->invoice_number, -6);
      $next = '2' . $username . sprintf("%06d", ((int)$awal + 1));
      $nomor = 'SJ/' . $year . '/2' . $next;
    }

    return $nomor;
  }

  // get unique code 3 digit max 500 with auto increment
  private function getUniqueCodeLead($field = 'temp_kode_unik', $prefix = null)
  {
    return 0;
  }
}
