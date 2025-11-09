<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateSalesOrders extends Command
{
    protected $signature = 'accurate:import-sales-orders';
    protected $description = 'Import Sales Order list and detail from Accurate API';

    public function handle()
    {
        $startTime = now()->toDateTimeString();
        $this->sendSlackNotification("🚀 *Mulai sync data order accurate *{$startTime}* .");
        $date = date('d/m/Y', strtotime('-1 day'));
        $token   = env('ACCURATE_ACCESS_TOKEN');
        $secret  = env('ACCURATE_SECRET_KEY');
        $appKey  = env('ACCURATE_APP_KEY');
        $baseUrl = 'https://zeus.accurate.id/accurate/api/sales-order/list.do';

        $page     = 1;
        $pageSize = 100;
        $imported = 0;

        do {
            $timestamp = (string) round(microtime(true) * 1000);
            $signature = hash_hmac('sha256', $timestamp, $secret);

            $this->info("📄 Memuat halaman: {$page}");

            $response = Http::withHeaders([
                'Authorization'   => 'Bearer ' . $token,
                'X-Api-Timestamp' => $timestamp,
                'X-Api-Signature' => $signature,
                'X-Api-AppKey'    => $appKey,
            ])->timeout(30)
              ->retry(3, 2000)
              ->get($baseUrl, [
                  'fields'      => 'id,number,charField1,shipDate,poNumber,status,statusName,transDate,approvalStatus,customer,description,totalAmount',
                  'sp.page'     => $page,
                  'sp.pageSize' => $pageSize,
                //   'filter.transDate.op' => 'BETWEEN',
                  'filter.transDate.val' => $date,
                //   'filter.transDate.val' => '06/08/2025',
                //   'filter.transDate.val[1]' => '04/08/2025',
              ]);

            if (!$response->successful()) {
                Log::error('Gagal mengambil sales order list', ['response' => $response->body()]);
                $this->error('❌ Gagal ambil sales order dari API');
                return Command::FAILURE;
            }

            $json = $response->json();
            $data = $json['d'] ?? [];
            $totalPages = $json['sp']['pageCount'] ?? 1;

            $pageImportedCount = 0;

            foreach ($data as $item) {
                $salesOrderId = $item['id'];

                if ($salesOrderId == 55554) {
                    $this->warn("⚠️ Skip sales order dengan ID: $salesOrderId");
                    continue;
                }

                DB::connection('pgsql')->table('accurate_sales_order')->updateOrInsert(
                    ['id' => $salesOrderId],
                    [
                        'number'          => $item['number'] ?? null,
                        'char_field1'     => $item['charField1'] ?? null,
                        'ship_date'       => isset($item['shipDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $item['shipDate']))) : null,
                        'po_number'       => $item['poNumber'] ?? null,
                        'status'          => $item['status'] ?? null,
                        'status_name'     => $item['statusName'] ?? null,
                        'trans_date'      => isset($item['transDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $item['transDate']))) : null,
                        'approval_status' => $item['approvalStatus'] ?? null,
                        'description'     => $item['description'] ?? null,
                        'total_amount'    => $item['totalAmount'] ?? 0,
                        'customer_id'     => $item['customer']['id'] ?? null,
                        'customer_name'   => $item['customer']['name'] ?? null,
                        'customer_no'     => $item['customer']['customerNo'] ?? null,
                        'updated_at'      => now(),
                    ]
                );

                // Ambil detail sales order
                $detailResponse = Http::withHeaders([
                    'Authorization'   => 'Bearer ' . $token,
                    'X-Api-Timestamp' => $timestamp,
                    'X-Api-Signature' => $signature,
                    'X-Api-AppKey'    => $appKey,
                ])->timeout(30)
                  ->retry(3, 2000)
                  ->get('https://zeus.accurate.id/accurate/api/sales-order/detail.do', [
                      'id' => $salesOrderId,
                  ]);

                if (!$detailResponse->successful()) {
                    Log::warning("Gagal mengambil detail sales order ID $salesOrderId", ['body' => $detailResponse->body()]);
                    continue;
                }

                $detailData = $detailResponse->json('d');

                foreach ($detailData['detailItem'] ?? [] as $detail) {
                    DB::connection('pgsql')->table('accurate_sales_order_details')->updateOrInsert(
                        [
                            'sales_order_id' => $salesOrderId,
                            'item_no'        => $detail['item']['no'] ?? null,
                        ],
                        [
                            'sales_order_po_number'      => $detailData['poNumber'] ?? null,
                            'char_field2'                => $detailData['charField2'] ?? null,
                            'date_field1'                => $detailData['dateField1'] ?? null,
                            'tax_1_amount'               => $detailData['tax1Amount'] ?? null,
                            'po_number'                  => $detailData['poNumber'] ?? null,
                            'status'                     => $detailData['status'] ?? null,
                            'status_name'                => $detailData['statusName'] ?? null,
                            'description'                => $detailData['description'] ?? null,
                            'trans_date'                 => isset($detailData['transDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $detailData['transDate']))) : null,
                            'approval_status'            => $detailData['approvalStatus'] ?? null,

                            'unit_name'                  => $detail['itemUnit']['name'] ?? null,
                            'default_warehouse_do_name'  => $detail['defaultWarehouseDeliveryOrder']['name'] ?? null,
                            'salesman_name'              => $detail['salesmanName'] ?? null,

                            'item_name'                  => $detail['item']['name'] ?? null,
                            'item_char_field1'           => $detail['item']['charField1'] ?? null,
                            'item_type'                  => $detail['item']['itemType'] ?? null,

                            'unit_price'                 => $detail['unitPrice'] ?? 0,
                            'quantity'                   => $detail['quantity'] ?? 0,
                            'total_price'                => $detail['totalPrice'] ?? 0,

                            'warehouse_name'             => $detail['warehouse']['name'] ?? null,
                            'project_name'               => $detail['project']['name'] ?? null,
                            'updated_at'                 => now(),
                        ]
                    );
                }

                $pageImportedCount++;
                $imported++;
            }

            $this->line("✅ Selesai halaman {$page} - data diimpor: {$pageImportedCount}");
            $this->sendSlackNotification("🚀 *Selesai sync data order accurate *{$page}*.");


            $page++;
        } while ($page <= $totalPages);

        $this->info("🎉 Berhasil import $imported sales order dan detail.");

        DB::connection('pgsql')->update("
            UPDATE accurate_sales_order aso
            SET customer_no_sub = cgc.customer_no
            FROM contact_group_customer cgc
            WHERE aso.char_field1 = cgc.customer_name
            AND aso.customer_no_sub IS NULL
        ");

        $this->info("🔄 customer_no_sub berhasil diperbarui berdasarkan contact_group_customer");

        DB::connection('pgsql')->table('accurate_sales_order_summary')->truncate();

        DB::connection('pgsql')->insert("
            INSERT INTO accurate_sales_order_summary (customer_no, customer_name, total_qty, created_at, updated_at)
            SELECT
                o.customer_no,
                o.customer_name,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_sales_order_details d
            JOIN
                accurate_sales_order o ON o.id = d.sales_order_id
            GROUP BY
                o.customer_no, o.customer_name
        ");

        $this->info("📊 Tabel summary berhasil diisi ulang.");
        $this->sendSlackNotification("🚀 *Selesai sync data order accurate all page.");
        return Command::SUCCESS;
    }

    protected function sendSlackNotification(string $message): void
    {
        try {
            Http::post(env('SLACK_WEBHOOK_URL'), [
                'text' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Gagal mengirim notifikasi Slack', [
                'error' => $e->getMessage(),
                'message' => $message,
            ]);
        }
    }
}
