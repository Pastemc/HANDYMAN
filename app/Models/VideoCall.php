<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $type;
    public $data;
    public $receiverId;

    public function __construct($type, $data, $receiverId)
    {
        $this->type = $type;
        $this->data = $data;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('video-call.' . $this->receiverId);
    }

    public function broadcastWith()
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
        ];
    }

    public function broadcastAs()
    {
        return 'video.call';
    }
}