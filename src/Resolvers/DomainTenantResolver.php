<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Resolvers;

use Rinvex\Tenants\Models\Tenant;
use Rinvex\Tenants\Contracts\TenantResolver;
use Rinvex\Tenants\Exceptions\InvalidDomainTenantException;

class DomainTenantResolver implements TenantResolver
{
    /**
     * Resolve tenant using current request.
     *
     * @return \Rinvex\Tenants\Models\Tenant
     *
     * @throws \Throwable|\Rinvex\Tenants\Exceptions\InvalidDomainTenantException
     */
    public static function resolve(): Tenant
    {
        $tenant = app('rinvex.tenants.tenant')->where('is_active', true)->where('domain', $host = request()->getHost())->first();

        throw_unless($tenant, InvalidDomainTenantException::class, $host);

        return $tenant;
    }
}
