<?php

namespace App\Events;

use App\Http\Controllers\HealthChecker\BuilderController;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendDataEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $dataResources;

    /**
     * Create a new event instance.
     */
    public function __construct()
    {
        $this->dataResources = (new BuilderController())->getAnalyticStatistics();
    }


    public function broadcastOn()
    {
        return new Channel('public');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['resources' => $this->dataResources];
    }

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public function broadcastQueue(): string
    {
        return 'default';
    }
}
