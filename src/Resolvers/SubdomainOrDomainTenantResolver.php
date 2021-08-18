<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Resolvers;

use Illuminate\Support\Str;
use Rinvex\Tenants\Models\Tenant;
use Rinvex\Tenants\Contracts\TenantResolver;

class SubdomainOrDomainTenantResolver implements TenantResolver
{
    /**
     * Resolve tenant using current request.
     *
     * @return \Rinvex\Tenants\Models\Tenant
     *
     * @throws \Throwable|\Rinvex\Tenants\Exceptions\AbstractTenantException
     */
    public static function resolve(): Tenant
    {
        if (Str::endsWith(request()->getHost(), central_domains())) {
            return SubdomainTenantResolver::resolve();
        }

        return DomainTenantResolver::resolve();
    }
}
