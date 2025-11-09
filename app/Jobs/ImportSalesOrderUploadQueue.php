<?php

namespace App\Jobs;

use App\Models\OrderSubmitLogDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportSalesOrderUploadQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $submitLog_id;
    protected $type;
    protected $file;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($submitLog_id, $type, $file)
    {
        $this->submitLog_id = $submitLog_id;
        $this->type = $type;
        $this->file = $file;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file = $this->file;
        $submitLog_id = $this->submitLog_id;
        try {
            DB::beginTransaction();
            DB::table('order_submit_logs')->where('id', $submitLog_id)->update(['body' => getImage($file)]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $submitLog_id
            ], [
                'order_submit_log_id' => $submitLog_id,
                'order_id' => $submitLog_id,
                'status' => 'failed',
                'error_message' => $th->getMessage()
            ]);
        }
    }
}
