<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PublicMessageEvent implements ShouldBroadcast
{
    public $channelName;
    public $message;

    use Dispatchable, InteractsWithSockets;

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

    /**
     * Get the channels the event should broadcast on.
     * https://laravel.com/docs/broadcasting#model-broadcasting-channel-conventions
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn() : array
    {

        \Log::info('public');

        return ['public'];
    }

    public function broadcastAs() : string
    {
        \Log::info('public-message');

        return 'public-message';
    }
}
