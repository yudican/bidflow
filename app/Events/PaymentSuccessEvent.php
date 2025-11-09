<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data = [];

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return ['aimigroup-stagging'];
    }

    public function broadcastAs()
    {
        return 'payment-success';
    }
}
