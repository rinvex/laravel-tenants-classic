<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Events;

use Rinvex\Tenants\Models\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TenantCreated implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;
    use Dispatchable;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue = 'events';

    /**
     * The model instance passed to this event.
     *
     * @var \Rinvex\Tenants\Models\Tenant
     */
    public Tenant $model;

    /**
     * Create a new event instance.
     *
     * @param \Rinvex\Tenants\Models\Tenant $tenant
     */
    public function __construct(Tenant $tenant)
    {
        $this->model = $tenant;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('rinvex.tenants.tenants.index'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'tenant.created';
    }
}
