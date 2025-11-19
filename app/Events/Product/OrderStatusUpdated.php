<?php

namespace App\Events\Product;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $notificationTitle;

    public function __construct($data, $notificationTitle)
    {
        $this->data = $data;
        $this->notificationTitle = $notificationTitle;
    }

    public function broadcastWith(): array
    {
        return ['data' => $this->data, 'title' => $this->notificationTitle];
    }

    public function broadcastOn(): array
    {
        $customerId = $this->data['user_id'];
        return [
            new PrivateChannel("order.customer.{$customerId}"),
        ];
    }
}
