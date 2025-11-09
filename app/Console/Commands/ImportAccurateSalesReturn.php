<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportAccurateSalesReturn extends Command
{
    protected $signature = 'accurate:import-sales-return';
    protected $description = 'Import Accurate Sales Return and Detail Data';

    public function handle()
    {
        $token = env('ACCURATE_ACCESS_TOKEN');
        $secret = env('ACCURATE_SECRET_KEY');
        $appKey = env('ACCURATE_APP_KEY');

        $baseUrl = 'https://zeus.accurate.id/accurate/api/sales-return/list.do';
        $detailUrl = 'https://zeus.accurate.id/accurate/api/sales-return/detail.do';

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $timestamp, $secret);

        $params = [
            'fields' => 'id,number,subTotal,totalAmount,description,transDate,approvalStatus,customer',
            'sp.pageSize' => 100,
            'sp.page' => 1,
        ];

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Api-Timestamp' => $timestamp,
                'X-Api-Signature' => $signature,
                'X-Api-AppKey' => $appKey,
            ])->get($baseUrl, $params);

            if (!$response->successful()) {
                Log::error('Gagal mengambil data sales return', ['response' => $response->body()]);
                return Command::FAILURE;
            }

            $json = $response->json();

            foreach ($json['d'] ?? [] as $sr) {
                $salesReturnId = $sr['id'];

                DB::connection('pgsql')->table('accurate_sales_returns')->updateOrInsert(
                    ['id_sales_return' => $salesReturnId],
                    [
                        'customer_id' => $sr['customer']['id'] ?? null,
                        'customer_name' => $sr['customer']['name'] ?? null,
                        'customer_code' => $sr['customer']['customerNo'] ?? null,
                        'number' => $sr['number'] ?? null,
                        'sub_total' => $sr['subTotal'] ?? 0,
                        'total_amount' => $sr['totalAmount'] ?? 0,
                        'description' => $sr['description'] ?? null,
                        'trans_date' => isset($sr['transDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $sr['transDate']))) : null,
                        'approval_status' => $sr['approvalStatus'] ?? null,
                        'updated_at' => now(),
                    ]
                );

                // Ambil detail
                $detailResp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'X-Api-Timestamp' => $timestamp,
                    'X-Api-Signature' => $signature,
                    'X-Api-AppKey' => $appKey,
                ])->get($detailUrl, ['id' => $salesReturnId]);

                if (!$detailResp->successful()) {
                    Log::warning("Gagal ambil detail sales return ID {$salesReturnId}", ['body' => $detailResp->body()]);
                    continue;
                }

                $data = $detailResp->json('d');
                foreach ($data['detailItem'] ?? [] as $detail) {
                    DB::connection('pgsql')->table('accurate_sales_return_details')->updateOrInsert(
                        [
                            'sales_return_id' => $salesReturnId,
                            'item_code' => $detail['item']['no'] ?? null,
                        ],
                        [
                            'number' => $data['number'] ?? null,
                            'unit_price' => $detail['unitPrice'] ?? 0,
                            'item_unit_name' => $detail['itemUnit']['name'] ?? null,
                            'item_name' => $detail['item']['name'] ?? null,
                            'item_field' => $detail['item']['charField1'] ?? null,
                            'item_type' => $detail['item']['itemType'] ?? null,
                            'warehouse_name' => $detail['warehouse']['name'] ?? null,
                            'detail_name' => $detail['detailName'] ?? null,
                            'qty' => $detail['quantity'] ?? 0,
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            $params['sp.page']++;
            $hasMore = isset($json['sp']['pageCount']) && $params['sp.page'] <= $json['sp']['pageCount'];
        } while ($hasMore);

        $this->info('Sales return data imported successfully.');
        return Command::SUCCESS;
    }
}
