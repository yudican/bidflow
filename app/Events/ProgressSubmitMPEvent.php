<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgressSubmitMPEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $progress;

    public function __construct($progress)
    {
        $total = getSetting('SUBMIT_TOTAL_MP');
        $this->progress = [
            'progress' => $progress,
            'total' => $total,
            'percentage' => getPercentage($total, $progress)
        ];
    }

    public function broadcastOn()
    {
        return new Channel('bidflow-crm-development');
    }

    public function broadcastAs()
    {
        return 'progress-submit-mp';
    }
}
