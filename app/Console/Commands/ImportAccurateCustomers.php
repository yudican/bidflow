<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateCustomers extends Command
{
    protected $signature = 'accurate:import-customers';
    protected $description = 'Import customers from Accurate API to pgsql database';

    public function handle()
    {
        $startTime = now()->toDateTimeString();
        $this->sendSlackNotification("🚀 *Mulai sync data customer accurate *{$startTime}*.");

        $token   = env('ACCURATE_ACCESS_TOKEN');
        $secret  = "be7oZxpiPDXooS4ra2Hut3aLhB74lUi9yblxC2DKGPO2Mt7DhqhGttpKj57rnWnY";
        $appKey  = "ORCA FLIMGROUP";

        $baseUrl = 'https://zeus.accurate.id/accurate/api/customer/list.do';

        $params = [
            'fields' => 'id,name,defaultWarehouse,email,wpName,category,customerNo,npwpNo,workPhone,charField1',
            'sp.pageSize' => 100,
            'sp.page' => 1,
        ];

        $importedCount = 0;

        do {
            // PERBAIKAN: timestamp & signature HARUS diperbarui di setiap loop
            $timestamp = (string) round(microtime(true) * 1000);
            $signature = hash_hmac('sha256', $timestamp, $secret);

            // dd($timestamp.' -- '.$signature.' -- '.$appKey);

            $response = Http::withHeaders([
                'Authorization'     => 'Bearer aat.NTA.eyJ2IjoxLCJ1Ijo4MDQ5MzQsImQiOjE1OTYwNjQsImFpIjo1MTk2MCwiYWsiOiIyMTQxMmMzNS0wYmI2LTRiMDgtOWY4Mi03YjJhNzg0NDcwNzgiLCJhbiI6Ik9SQ0EgRkxJTUdST1VQIiwiYXAiOiI3MzU3ZTZjNC0xOGJmLTRiYjUtYTM5My05OTVjNWViZGIyYzQiLCJ0IjoxNzM3MDAzNzI5MzY3fQ.l95oW0P0BvFZlyg56v2mUMRryVBGJfpKO82FZQiiSRAhTyE6Tfrgwgc4LOqaK0VgDsAgkbANdJGD8bGJLsIYzct82oNjGcS+kbbYy8O3CKi0BUATKgy2JGBJ5KOun6Wq0WQE0e7e0S+LRytI+FK8FubdFOjZREsbdCl3Z9aoIVrYu57MhB7TIMREaQ2z1LtBExdXBVM6jrg=.Y4Wpfa+BUAvelo9jGxoVcsXZh91ny7ASrcR0PVPMyJc',
                'X-Api-Timestamp'   => $timestamp,
                'X-Api-Signature'   => $signature,
                'X-Api-AppKey'      => $appKey,
            ])
            ->timeout(30)
            ->retry(3, 2000)
            ->get($baseUrl, $params);

            if (!$response->successful()) {
                Log::error('Gagal mengambil data customer dari Accurate', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                $this->warn("Gagal mengambil data dari API Accurate.");
                return Command::FAILURE;
            }

            $json = $response->json();

            // dd($json['d']); die;

            if (!isset($json['d']) || !is_array($json['d'])) {
                Log::warning('Data "d" tidak valid atau bukan array', ['data' => $json]);
                $this->warn("Data customer tidak valid. Cek log.");
                return Command::FAILURE;
            }

            foreach ($json['d'] as $customer) {
                // CEK APAKAH SUDAH ADA
                $existing = DB::connection('pgsql')->table('accurate_customers')
                    ->where('accurate_id', $customer['id'])
                    ->exists();
            
                if ($existing) {
                    // Skip jika sudah ada, tidak perlu ambil detail
                    continue;
                }
            
                // Ambil detail customer dari API
                $timestamp = (string) round(microtime(true) * 1000);
                $signature = hash_hmac('sha256', $timestamp, $secret);
            
                $detailRes = Http::withHeaders([
                    'Authorization'     => 'Bearer ' . $token,
                    'X-Api-Timestamp'   => $timestamp,
                    'X-Api-Signature'   => $signature,
                    'X-Api-AppKey'      => $appKey,
                ])
                ->timeout(30)
                ->retry(3, 2000)
                ->get('https://zeus.accurate.id/accurate/api/customer/detail.do', [
                    'id' => $customer['id']
                ]);
            
                // DEFAULT DATA
                $shipStreet = null;
                $shipProvince = null;
                $shipCity = null;
                $customerReceivableAccounts = null;
            
                if ($detailRes->successful()) {
                    $detailData = $detailRes->json('d');
                    $shipStreet = $detailData['shipStreet'] ?? null;
                    $shipProvince = $detailData['shipProvince'] ?? null;
                    $shipCity = $detailData['shipCity'] ?? null;
            
                    if (!empty($detailData['customerReceivableAccountList'])) {
                        $names = collect($detailData['customerReceivableAccountList'])->pluck('name')->all();
                        $customerReceivableAccounts = json_encode($names);
                    }
                } else {
                    Log::warning('Gagal mengambil detail customer', [
                        'id' => $customer['id'],
                        'body' => $detailRes->body()
                    ]);
                }
            
                // Simpan ke DB karena belum ada
                DB::connection('pgsql')->table('accurate_customers')->insert([
                    'accurate_id'                => $customer['id'],
                    'name'                       => $customer['name'] ?? null,
                    'email'                      => $customer['email'] ?? null,
                    'wp_name'                    => $customer['wpName'] ?? null,
                    'warehouse_name'            => $customer['defaultWarehouse']['name'] ?? null,
                    'default_warehouse'         => isset($customer['defaultWarehouse']) ? json_encode($customer['defaultWarehouse']) : null,
                    'category_name'             => $customer['categoryName'] ?? null,
                    'npwp_no'                   => $customer['npwpNo'] ?? null,
                    'ship_street'               => $shipStreet,
                    'ship_province'             => $shipProvince,
                    'ship_city'                 => $shipCity,
                    'work_phone'                => $customer['workPhone'] ?? null,
                    'customer_no'               => $customer['customerNo'] ?? null,
                    'customer_type'             => $customer['charField1'] ?? null,
                    'customer_receivable_name' => $customer['customerReceivableName'] ?? null,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
            
                // Log insert
                DB::connection('pgsql')->table('accurate_customer_logs')->insert([
                    'accurate_id' => $customer['id'],
                    'action' => 'insert',
                    'created_at' => now()
                ]);
            
                $importedCount++;
            }            

            $params['sp.page']++;
            $hasMorePages = isset($json['sp']['pageCount']) && $params['sp.page'] <= $json['sp']['pageCount'];

        } while ($hasMorePages);

        $this->info("✅ Imported {$importedCount} customers from Accurate.");
        $this->sendSlackNotification("🚀 *Selesai sync data customer accurate *{$startTime}*.");

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
