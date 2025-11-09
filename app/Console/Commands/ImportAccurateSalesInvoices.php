<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateSalesInvoices extends Command
{
    protected $signature = 'accurate:import-sales-invoices';
    protected $description = 'Import Sales Invoice list and details from Accurate API';

    public function handle()
    {
        $token   = env('ACCURATE_ACCESS_TOKEN');
        $secret  = env('ACCURATE_SECRET_KEY');
        $appKey  = env('ACCURATE_APP_KEY');
        $baseUrl = 'https://zeus.accurate.id/accurate/api/sales-invoice/list.do';

        $page      = 1;
        $pageSize  = 100;
        $imported  = 0;

        do {
            $timestamp = (string) round(microtime(true) * 1000);
            $signature = hash_hmac('sha256', $timestamp, $secret);

            $response = Http::withHeaders([
                'Authorization'     => 'Bearer ' . $token,
                'X-Api-Timestamp'   => $timestamp,
                'X-Api-Signature'   => $signature,
                'X-Api-AppKey'      => $appKey,
            ])->timeout(30)
              ->retry(3, 2000)
              ->get($baseUrl, [
                  'fields'       => 'id,taxNumber,tax1Amount,poNumber,dueDate,description,transDate,approvalStatus,number,charField1,shipDate,totalAmount,customer,deliveryOrder',
                  'sp.pageSize'  => $pageSize,
                  'sp.page'      => $page,
              ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch sales invoices', ['response' => $response->body()]);
                $this->error('Gagal mengambil data sales invoice');
                return Command::FAILURE;
            }

            $json = $response->json();
            $data = $json['d'] ?? [];
            $totalPages = $json['sp']['pageCount'] ?? 1;

            foreach ($data as $item) {
                $invoiceId = $item['id'];

                // Lewati ID bermasalah
                if ($invoiceId == 48669) {
                    Log::warning("Lewati invoice ID 48669 karena error permanen.");
                    continue;
                }

                // Simpan master invoice
                DB::connection('pgsql')->table('accurate_sales_invoice')->updateOrInsert(
                    ['id' => $invoiceId],
                    [
                        'approval_status'   => $item['approvalStatus'] ?? null,
                        'due_date'          => isset($item['dueDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $item['dueDate']))) : null,
                        'tax_number'        => $item['taxNumber'] ?? null,
                        'description'       => $item['description'] ?? null,
                        'ship_date'         => isset($item['shipDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $item['shipDate']))) : null,
                        'tax1_amount'       => $item['tax1Amount'] ?? 0,
                        'number'            => $item['number'] ?? null,
                        'total_amount'      => $item['totalAmount'] ?? 0,
                        'trans_date'        => isset($item['transDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $item['transDate']))) : null,
                        'char_field1'       => $item['charField1'] ?? null,
                        'po_number'         => $item['poNumber'] ?? null,
                        'customer_id'       => $item['customer']['id'] ?? null,
                        'customer_name'     => $item['customer']['name'] ?? null,
                        'customer_no'       => $item['customer']['customerNo'] ?? null,
                        'updated_at'        => now(),
                    ]
                );

                // Lewati jika detail sudah ada
                $detailExists = DB::connection('pgsql')
                    ->table('accurate_sales_invoice_details')
                    ->where('invoice_id', $invoiceId)
                    ->exists();

                if ($detailExists) {
                    Log::info("Lewati detail invoice_id $invoiceId karena sudah ada.");
                    continue;
                }

                // Ambil detail invoice
                $detailRes = Http::withHeaders([
                    'Authorization'     => 'Bearer ' . $token,
                    'X-Api-Timestamp'   => $timestamp,
                    'X-Api-Signature'   => $signature,
                    'X-Api-AppKey'      => $appKey,
                ])->timeout(30)
                  ->retry(3, 2000)
                  ->get("https://zeus.accurate.id/accurate/api/sales-invoice/detail.do", [
                      'id' => $invoiceId
                  ]);

                if (!$detailRes->successful()) {
                    Log::warning('Gagal ambil detail invoice', ['id' => $invoiceId, 'body' => $detailRes->body()]);
                    continue;
                }

                $detailData = $detailRes->json('d');
                $charField1 = $detailData['charField1'] ?? null;
                $branchName = $detailData['branchName'] ?? null;
                $invoiceNo  = $detailData['number'] ?? null;

                foreach ($detailData['detailItem'] ?? [] as $detail) {
                    DB::connection('pgsql')->table('accurate_sales_invoice_details')->updateOrInsert(
                        [
                            'invoice_id' => $invoiceId,
                        ],
                        [
                            'invoice_number'       => $invoiceNo,
                            'char_field1'          => $charField1,
                            'branch_name'          => $branchName,
                            'sales_order_number'   => $detailData['detailExpense'][0]['salesOrder']['number'] ?? null,
                            'sales_order_id'       => $detailData['detailExpense'][0]['salesOrder']['id'] ?? null,
                            'delivery_order_detail_id' => $detail['deliveryOrderDetailId'] ?? null,
                            'item_no'              => $detail['item']['no'] ?? null,
                            'item_name'            => $detail['item']['name'] ?? null,
                            'item_code'            => $detail['item']['charField1'] ?? null,
                            'unit_name'            => $detail['itemUnit']['name'] ?? null,
                            'unit_price'           => $detail['unitPrice'] ?? 0,
                            'quantity'             => $detail['quantity'] ?? 0,
                            'updated_at'           => now(),
                        ]
                    );
                }

                $imported++;
            }

            $page++;
        } while ($page <= $totalPages);

        $this->info("✅ Berhasil import $imported sales invoices dan detailnya.");
        return Command::SUCCESS;
    }
}
