<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateItems extends Command
{
    protected $signature = 'accurate:import-items';
    protected $description = 'Import item list from Accurate API and store to database';

    public function handle()
    {
        $token = env('ACCURATE_ACCESS_TOKEN');
        $secret  = "be7oZxpiPDXooS4ra2Hut3aLhB74lUi9yblxC2DKGPO2Mt7DhqhGttpKj57rnWnY";
        $appKey  = "ORCA FLIMGROUP";

        $baseUrl = 'https://zeus.accurate.id/accurate/api/item/list.do';

        $timestamp = (string) round(microtime(true) * 1000);
        $signature = hash_hmac('sha256', $timestamp, $secret);

        $params = [
            'fields' => 'id,name,no,unit1,itemTypeName',
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
            
                $existing = DB::connection('pgsql')->table('accurate_items')
                    ->where('accurate_id', $item['id'])
                    ->first();
            
                DB::connection('pgsql')->table('accurate_items')->updateOrInsert(
                    ['accurate_id' => $item['id']],
                    [
                        'name'          => $item['name'] ?? null,
                        'item_no'       => $item['no'] ?? null,
                        'unit1'         => $item['unit1']['name'] ?? null,
                        'item_type_name' => $item['itemTypeName'] ?? null,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]
                );
            
                if (!$existing) {
                    DB::connection('pgsql')->table('accurate_item_logs')->insert([
                        'accurate_id' => $item['id'],
                        'action'      => 'insert',
                        'created_at'  => now(),
                    ]);
                }
            
                $importedCount++;
            }            

            $params['sp.page']++;
            $hasMorePages = isset($json['sp']['pageCount']) && $params['sp.page'] <= $json['sp']['pageCount'];

        } while ($hasMorePages);

        $this->info("Imported {$importedCount} items.");
        return Command::SUCCESS;
    }
}
