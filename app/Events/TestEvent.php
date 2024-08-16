<?php

namespace App\Events;

use App\Models\Portal\User\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestEvent implements ShouldBroadcast
{
    use SerializesModels;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($channelName, $message)
    {

        $this->channelName = $channelName;
        $this->message = $message;
    }


    public function broadcastOn() : PrivateChannel
    {

        return new PrivateChannel($this->channelName);
    }
}
