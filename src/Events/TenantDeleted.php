<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Events;

use Rinvex\Tenants\Models\Tenant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TenantDeleted implements ShouldBroadcast
{
    use SerializesModels;

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
}
