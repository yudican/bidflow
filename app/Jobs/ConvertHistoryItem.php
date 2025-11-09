<?php

namespace App\Jobs;

use App\Models\ProductConvertDetail;
use App\Models\ProductConvertHistory;
use App\Models\SkuMaster;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConvertHistoryItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user_id)
    {
        $this->data = $data;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        ProductConvertDetail::create($data);
        $success = getSetting('product_convert_success_' . $this->user_id) ?? 0;
        setSetting('product_convert_success_' . $this->user_id, $success + 1);
        convertProgress($this->user_id, $success + 1, 'Convert', $data['product_convert_id']);
    }
}
