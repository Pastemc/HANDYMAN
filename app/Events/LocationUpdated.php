<?php

namespace App\Events;

use App\Models\Location;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function broadcastOn()
    {
        $channels = [
            new Channel('locations'),
        ];

        if ($this->location->service_request_id) {
            $channels[] = new Channel('service-request.' . $this->location->service_request_id);
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->location->id,
            'user_id' => $this->location->user_id,
            'service_request_id' => $this->location->service_request_id,
            'latitude' => $this->location->latitude,
            'longitude' => $this->location->longitude,
            'accuracy' => $this->location->accuracy,
            'address' => $this->location->address,
            'user' => [
                'id' => $this->location->user->id,
                'name' => $this->location->user->name,
                'email' => $this->location->user->email,
            ],
            'google_maps_url' => $this->location->google_maps_url,
            'last_updated_at' => $this->location->last_updated_at->toIso8601String(),
        ];
    }

    public function broadcastAs()
    {
        return 'location.updated';
    }
}