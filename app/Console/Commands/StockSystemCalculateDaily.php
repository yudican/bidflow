<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockSystemCalculateDaily extends Command
{
    protected $signature = 'stock:calculate-daily';
    protected $description = 'Hitung DOH (Days On Hand) harian dan kirim email hasilnya';

    public function handle()
    {
        $this->info('Menjalankan perhitungan DOH harian...');

        try {
            $startDate = Carbon::createFromDate(2025, 9, 1)->format('Y-m-d');
            $endDate   = Carbon::now()->format('Y-m-d');

            // 🔹 Hit API agar tabel accurate_stock_calculated_details_tmp terisi
            $url = "https://staging.aimi.dev/api/accurate/stock-system-calculated";
            $params = [
                'type' => 'opname',
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];

            $response = Http::timeout(300)->get($url, $params);

            if ($response->failed()) {
                Log::error('Gagal memanggil API stock-system-calculated', [
                    'url' => $url,
                    'params' => $params,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $this->error('API gagal dipanggil. Cek log.');
                return Command::FAILURE;
            }

            // 🔹 Ambil hasil matching days_on_hand = jml_hari
            $results = DB::connection('pgsql')
                ->table('accurate_stock_calculated_details_tmp as tmp')
                ->join('contact_group_customer as cgc', 'tmp.subaccount_no', '=', 'cgc.customer_no')
                ->select(
                    'tmp.subaccount_no',
                    'tmp.subaccount',
                    'tmp.days_on_hand',
                    'cgc.jml_hari',
                    'cgc.customer_email',
                    'cgc.customer_telp',
                    'tmp.item_no',
                    'tmp.item_name',
                    'tmp.head_account',
                    'tmp.stock_system'
                )
                ->whereColumn('tmp.days_on_hand', '=', 'cgc.jml_hari')
                ->orderBy('tmp.subaccount')
                ->get();

            if ($results->isEmpty()) {
                $this->info('Tidak ada toko dengan DOH = jml_hari.');
                return Command::SUCCESS;
            }

            $grouped = $results->groupBy('subaccount');

            foreach ($grouped as $storeName => $items) {
                $email = $items->first()->customer_email;
                $jmlHari = $items->first()->jml_hari;

                $email = 'fikar.bidflow@gmail.com';

                if (empty($email)) {
                    Log::warning("Lewati {$storeName} karena tidak ada alamat email.");
                    continue;
                }

                $this->sendEmailAlert($email, $storeName, $items, $jmlHari);
                $this->info("Email dikirim ke: {$storeName} ({$email})");
            }

            $this->info('Email notifikasi DOH terkirim (' . count($emailData) . ' toko)');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Error di StockSystemCalculateDaily', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Kirim email laporan DOH harian.
     */
    protected function sendEmailAlert($email, $storeName, $items, $jmlHari)
    {
        $subject = "[{$storeName}] — DoH Telah Melebihi Batas Maksimum ({$jmlHari} Hari Reminder)";

        // Ambil item pertama saja untuk ringkasan (boleh disesuaikan)
        $firstItem = $items->first();

        $email = 'fikar.bidflow@gmail.com';

        $body = "
            <p>Halo,</p>
            <p>Perhatian, stok di <strong>{$storeName}</strong> telah melewati batas maksimum Days on Hand (DoH).</p>
            <p>Langkah tindak lanjut diperlukan segera untuk menghindari overstock dan potensi penurunan kualitas stok.</p>

            <p><strong>Detail Informasi:</strong></p>
            <ul>
                <li><strong>Store:</strong> {$storeName}</li>
                <li><strong>SKU:</strong> {$firstItem->item_name} ({$firstItem->item_no})</li>
                <li><strong>DoH:</strong> {$firstItem->days_on_hand} Hari</li>
                <li><strong>Stock System:</strong> {$firstItem->stock_system}</li>
            </ul>

            <p>Mohon koordinasikan dengan tim terkait (Lead Sales / Merchandiser / Sales Counter Officer)
            untuk segera melakukan penyesuaian.</p>

            <p>Terima kasih atas respon cepatnya.<br>— <em>Stock Count by FIS</em></p>
        ";

        try {
            Mail::html($body, function ($message) use ($email, $subject) {
                $message->to($email)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error("Gagal kirim email ke {$email}: " . $e->getMessage());
        }
    }
}
