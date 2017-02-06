<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class Obyx
{
    use InteractsWithSockets, SerializesModels;

    public $reason;
    public $user_id;

    /**
     * Create a new event instance.
     */
    public function __construct($reason, $user_id)
    {
        $this->reason = $reason;
        $this->user_id = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
