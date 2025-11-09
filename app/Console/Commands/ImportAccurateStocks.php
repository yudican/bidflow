<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateStocks extends Command
{
    protected $signature = 'accurate:import-stocks';
    protected $description = 'Import Stock list from Accurate API and store to database';

    public function handle()
    {
        $token = env('ACCURATE_ACCESS_TOKEN');
        $secret = env('ACCURATE_SECRET_KEY');
        $appKey = env('ACCURATE_APP_KEY');

        $baseUrl = 'https://zeus.accurate.id/accurate/api/item/list-stock.do';

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $timestamp, $secret);

        $params = [
            // 'fields' => 'id,name,no,unit1,itemTypeName',
            'sp.pageSize' => 100,
            'sp.page' => 1
        ];

        $importedCount = 0;

        do {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-Api-Timestamp' => $timestamp,
                'X-Api-Signature' => $signature,
                'X-Api-AppKey' => $appKey
            ])->get($baseUrl, $params);

            if (!$response->successful()) {
                Log::error('Gagal mengambil data dari Accurate', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $this->warn("Gagal mengambil data dari API.");
                return Command::FAILURE;
            }

            $json = $response->json();

            if (!isset($json['d']) || !is_array($json['d'])) {
                Log::warning('Data item bukan array, dilewati', [
                    'response' => $json
                ]);
                $this->warn("Data item tidak valid. Cek log.");
                return Command::FAILURE;
            }

            foreach ($json['d'] as $item) {
                if (!is_array($item)) {
                    Log::warning('Item bukan array, dilewati', ['item' => $item]);
                    continue;
                }

                DB::connection('pgsql')->table('accurate_stocks')->updateOrInsert(
                    ['accurate_id' => $item['id']],
                    [
                        'item_no' => $item['no'] ?? null,
                        'quantity' => $item['quantity'] ?? null,
                        'name' => $item['name'] ?? null,
                        'accurate_id' => $item['id'] ?? null,
                        'quantity_in_all_unit' => $item['quantityInAllUnit'] ?? null,
                    ]
                );

                $importedCount++;
            }

            $params['sp.page']++;
            $hasMorePages = isset($json['sp']['pageCount']) && $params['sp.page'] <= $json['sp']['pageCount'];

        } while ($hasMorePages);

        $this->info("Imported {$importedCount} items.");
        return Command::SUCCESS;
    }
}
