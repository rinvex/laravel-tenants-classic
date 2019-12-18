<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Events;

use Rinvex\Tenants\Models\Tenant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TenantDeleted implements ShouldBroadcast
{
    use SerializesModels;
    use InteractsWithSockets;

    public $tenant;

    /**
     * Create a new event instance.
     *
     * @param \Rinvex\Tenants\Models\Tenant $tenant
     */
    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel($this->formatChannelName());
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'rinvex.tenants.deleted';
    }

    /**
     * Format channel name.
     *
     * @return string
     */
    protected function formatChannelName(): string
    {
        return 'rinvex.tenants.list';
    }
}
