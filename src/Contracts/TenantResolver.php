<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Contracts;

use Rinvex\Tenants\Models\Tenant;

interface TenantResolver
{
    /**
     * Resolve tenant using current request.
     *
     * @throws \Throwable|\Rinvex\Tenants\Exceptions\AbstractTenantException
     *
     * @return \Rinvex\Tenants\Models\Tenant
     */
    public static function resolve(): Tenant;
}
