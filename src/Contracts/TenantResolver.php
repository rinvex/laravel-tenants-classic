<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Contracts;

use Rinvex\Tenants\Models\Tenant;

interface TenantResolver
{
    /**
     * Resolve tenant using current request.
     *
     * @return \Rinvex\Tenants\Models\Tenant
     *
     * @throws \Throwable|\Rinvex\Tenants\Exceptions\AbstractTenantException
     */
    public static function resolve(): Tenant;
}
