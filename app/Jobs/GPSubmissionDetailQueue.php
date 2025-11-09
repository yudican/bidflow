<?php

namespace App\Jobs;

use App\Models\GpSusmissionLogError;
use App\Models\ListOrderGpDetail;
use App\Models\OrderListByGenie;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class GPSubmissionDetailQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data = [];
    protected $progress = 0;
    protected $total_data = 0;
    protected $key;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $progress, $total_data, $key)
    {
        $this->data = $data;
        $this->progress = $progress;
        $this->total_data = $total_data;
        $this->key = $key;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $total_progress = $this->progress;
        $totalData = $this->total_data;
        $key = $this->key;

        // pusher
        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher(
            'eafb4c1c4f906c90399e',
            '01d9b57c3818c1644cb0',
            '1472093',
            $options
        );

        // $gp = ListOrderGpDetail::create($data);
        $percentage = 0;
        if ($totalData == $total_progress) {
            $percentage = getPercentage($total_progress, $totalData);
            $pusher->trigger('aimi', 'progressGp', ['total' => $totalData, 'success' => $total_progress, 'progress' => false, 'percentage' => 100]);
            removeSetting($key);
        } else {
            $percentage = getPercentage($total_progress, $totalData);
            $pusher->trigger('aimi', 'progressGp', ['total' => $totalData, 'success' => $total_progress, 'progress' => true, 'percentage' => $percentage]);
        }
    }
}
