<?php

namespace App\Jobs\Exports;

use App\Exports\OrderManualExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ExportSalesOrderQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $userId;
    protected $totalData;
    protected $currentIndex;
    protected $fileName;

    public function __construct($data, $userId, $totalData, $currentIndex, $fileName)
    {
        $this->data = $data;
        $this->userId = $userId;
        $this->totalData = $totalData;
        $this->currentIndex = $currentIndex;
        $this->fileName = $fileName;
    }

    public function handle()
    {
        try {
            // Inisialisasi Pusher
            $pusher = new Pusher(
                'f01866680101044abb79',
                '4327409f9d87bdc35960',
                '1887006',
                [
                    'cluster' => 'ap1',
                    'useTLS' => true
                ]
            );

            // Key untuk progress
            $key = 'export-progress-' . $this->userId;

            // Proses export data
            $currentProgress = $this->currentIndex + 1;

            // Simpan data ke file excel
            Excel::store(
                new OrderManualExport($this->data),
                $this->fileName,
                's3',
                null,
                [
                    'visibility' => 'public'
                ]
            );

            // Hitung persentase
            $percentage = round(($currentProgress / $this->totalData) * 100);

            // Kirim progress ke client
            if ($currentProgress >= $this->totalData) {
                // Jika sudah selesai
                $pusher->trigger('bidflow', $key, [
                    'progress' => $currentProgress,
                    'total' => $this->totalData,
                    'percentage' => 100,
                    'refresh' => true,
                    'file_url' => Storage::disk('s3')->url($this->fileName)
                ]);

                // Hapus key progress
                removeSetting($key);
            } else {
                // Jika masih proses
                $pusher->trigger('bidflow', $key, [
                    'progress' => $currentProgress,
                    'total' => $this->totalData,
                    'percentage' => $percentage,
                    'refresh' => false
                ]);
            }
        } catch (\Throwable $th) {
            // Handle error
            $pusher->trigger('bidflow', $key, [
                'error' => true,
                'message' => $th->getMessage()
            ]);
        }
    }
}
