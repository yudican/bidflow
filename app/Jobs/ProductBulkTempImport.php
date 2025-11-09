<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProductBulkTempImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    protected $user;
    protected $success_total;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user, $success_total)
    {
        $this->data = $data;
        $this->user = $user;
        $this->success_total = $success_total;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $datas = $this->data;
        $user = $this->user;
        $no = $this->success_total;
        $datas->map(function ($data) use ($user, $no) {
            $data['user_id'] = $user->id;
            ProductTempImport::dispatch($data)->onQueue('queue-log');
            $no++;
        });
    }
}
