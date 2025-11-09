<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportAccurateItemTransfer extends Command
{
    protected $signature = 'accurate:import-item-transfer';
    protected $description = 'Import Item Transfer list from Accurate API and store to database';

    public function handle()
    {
        $startTime = now()->toDateTimeString();
        $this->sendSlackNotification("🚀 *Mulai sync data transfer accurate *{$startTime}* .");
        $date = date('d/m/Y', strtotime('-1 day'));
        $token = env('ACCURATE_ACCESS_TOKEN');
        $secret = env('ACCURATE_SECRET_KEY');
        $appKey = env('ACCURATE_APP_KEY');

        $baseUrl = 'https://zeus.accurate.id/accurate/api/item-transfer/list.do';
        $detailUrl = 'https://zeus.accurate.id/accurate/api/item-transfer/detail.do';

        $importedCount = 0;

        // Mulai dari halaman yang ditentukan oleh opsi --start
        $params = [
            'fields' => 'id,number,charField3,description,approvalStatus,transDate,referenceWarehouse,warehouse,charField1,itemTransferType,charField6',
            'sp.pageSize' => 100,
            'sp.page' => 1,
            'filter.transDate.val' => $date,
            // 'filter.transDate.op' => 'BETWEEN',
            // 'filter.transDate.val' => '09/08/2025',
            // 'filter.transDate.val[1]' => '13/08/2025',
        ];

        do {
            // generate ulang timestamp & signature di setiap permintaan
            $timestamp = (string) round(microtime(true) * 1000);
            $signature = hash_hmac('sha256', $timestamp, $secret);

            $this->info("📄 Memuat halaman: {$params['sp.page']}");
            $pageImportedCount = 0;

            $response = Http::timeout(60)->retry(3, 5000)->withHeaders([
                'Authorization'     => 'Bearer ' . $token,
                'X-Api-Timestamp'   => $timestamp,
                'X-Api-Signature'   => $signature,
                'X-Api-AppKey'      => $appKey
            ])->get($baseUrl, $params);

            if (!$response->successful()) {
                Log::error('Gagal mengambil data dari Accurate', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);
                $this->warn("❌ Gagal mengambil data dari API. Halaman {$params['sp.page']}");
                return Command::FAILURE;
            }

            $json = $response->json();

            if (!isset($json['d']) || !is_array($json['d'])) {
                Log::warning('Data item bukan array', ['response' => $json]);
                $this->warn("❌ Format data tidak valid. Dihentikan.");
                return Command::FAILURE;
            }

            foreach ($json['d'] as $transfer) {
                if (!is_array($transfer)) {
                    Log::warning("Ditemukan data non-array di halaman {$params['sp.page']}", ['transfer' => $transfer]);
                    continue;
                }

                $transferId = $transfer['id'];

                $charField3 = $transfer['charField3'] ?? null;
                $customerCode = null;

                if (!empty($charField3)) {
                    $customerCode = DB::connection('pgsql')
                        ->table('accurate_customers')
                        ->where('name', 'ILIKE', '%' . $charField3 . '%')
                        ->value('customer_no');
                }

                $charField1 = $transfer['charField1'] ?? null;
                $customerCodeSub = null;

                if (!empty($charField1)) {
                    $customerCodeSub = DB::connection('pgsql')
                        ->table('contact_group_customer')
                        ->where('customer_name', 'ILIKE', '%' . $charField1 . '%')
                        ->value('customer_no');
                }

                DB::connection('pgsql')->table('accurate_item_transfer')->updateOrInsert(
                    ['id' => $transferId],
                    [
                        'accurate_id'                => $transferId,
                        'number'                     => $transfer['number'] ?? null,
                        'description'                => $transfer['description'] ?? null,
                        'char_field3'                => $transfer['charField3'] ?? null,
                        'char_field1'                => $transfer['charField1'] ?? null,
                        'char_field6'                => $transfer['charField6'] ?? null,
                        'approval_status'           => $transfer['approvalStatus'] ?? null,
                        'trans_date'                => isset($transfer['transDate']) ? date('Y-m-d', strtotime(str_replace('/', '-', $transfer['transDate']))) : null,
                        'warehouse_id'              => $transfer['warehouse']['id'] ?? null,
                        'warehouse_name'            => $transfer['warehouse']['name'] ?? null,
                        'reference_warehouse_id'    => $transfer['referenceWarehouse']['id'] ?? null,
                        'reference_warehouse_name'  => $transfer['referenceWarehouse']['name'] ?? null,
                        'customer_code'             => $customerCode,
                        'customer_code_sub'         => $customerCodeSub,
                        'updated_at'                => now(),
                    ]
                );

                // Ambil detail
                $timestamp = (string) round(microtime(true) * 1000);
                $signature = hash_hmac('sha256', $timestamp, $secret);

                $detailResp = Http::timeout(60)->retry(3, 5000)->withHeaders([
                    'Authorization'     => 'Bearer ' . $token,
                    'X-Api-Timestamp'   => $timestamp,
                    'X-Api-Signature'   => $signature,
                    'X-Api-AppKey'      => $appKey,
                ])->get($detailUrl, ['id' => $transferId]);

                if (!$detailResp->successful()) {
                    Log::warning("Gagal ambil detail untuk ID {$transferId}", [
                        'status' => $detailResp->status(),
                        'body'   => $detailResp->body()
                    ]);
                    continue;
                }

                $data = $detailResp->json('d');
                $details = $data['detailItem'] ?? [];

                foreach ($details as $detail) {
                    DB::connection('pgsql')->table('accurate_item_transfer_details')->updateOrInsert(
                        ['accurate_detail_id' => $detail['id']],
                        [
                            'transfer_id'   => $transferId,
                            'transfer_no'   => $data['number'] ?? null,
                            'charField3'    => $data['charField3'] ?? null,
                            'status_name'   => $data['statusName'] ?? null,
                            'created_by'    => $data['createdBy'] ?? null,
                            'item_no'       => $detail['item']['no'] ?? null,
                            'item_name'     => $detail['item']['name'] ?? null,
                            'item_type'     => $detail['item']['itemType'] ?? null,
                            'item_code'     => $detail['item']['charField1'] ?? null,
                            'unit_name'     => $detail['itemUnit']['name'] ?? null,
                            'quantity'      => $detail['quantity'] ?? 0,
                            'tipe_proses'   => $data['itemTransferType'] ?? null,
                            'updated_at'    => now(),
                        ]
                    );
                }

                $importedCount++;
                $pageImportedCount++;
            }

            $this->line("✅ Selesai halaman {$params['sp.page']} - data diimpor: {$pageImportedCount}");
            $this->sendSlackNotification("🚀 *Selesai sync data transfer accurate *{$pageImportedCount}*.");

            $params['sp.page']++;

            $hasMorePages = isset($json['sp']['pageCount']) && $params['sp.page'] <= $json['sp']['pageCount'];
        } while ($hasMorePages);

        $this->info("🎉 Total data berhasil diimpor: {$importedCount}");

        DB::connection('pgsql')->update("
            UPDATE accurate_item_transfer AS ait
            SET tipe_proses = cgc.tipe_proses
            FROM accurate_item_transfer_details AS cgc
            WHERE cgc.transfer_id = ait.accurate_id;
        ");

        DB::connection('pgsql')->update("
            UPDATE accurate_item_transfer aso
            SET customer_code_sub = cgc.customer_no
            FROM contact_group_customer cgc
            WHERE (aso.char_field1 = cgc.customer_name or aso.char_field6 = cgc.customer_name)
            AND aso.customer_code_sub IS NULL;
        ");

        $this->info("🔄 customer_no_sub berhasil diperbarui berdasarkan contact_group_customer");

        DB::connection('pgsql')->table('accurate_item_transfer_summary')->truncate();

        DB::connection('pgsql')->insert("
            INSERT INTO accurate_item_transfer_summary (
                customer_no,
                customer_name,
                tipe_proses,
                total_qty,
                created_at,
                updated_at
            )
            SELECT
                o.customer_code,
                o.char_field3 AS customer_name,
                d.tipe_proses,
                SUM(d.quantity) AS total_qty,
                NOW(),
                NOW()
            FROM
                accurate_item_transfer_details d
            JOIN
                accurate_item_transfer o ON o.id = d.transfer_id
            WHERE
                d.tipe_proses = 'TRANSFER_OUT'
            GROUP BY
                o.customer_code,
                o.char_field3,
                d.tipe_proses
        ");

        $this->info("📊 Tabel summary berhasil diisi ulang.");
        $this->sendSlackNotification("🚀 *Selesai sync data transfer accurate all page.");
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
