<?php

declare(strict_types=1);

namespace Rinvex\Tenants\Resolvers;

use Illuminate\Support\Str;
use Rinvex\Tenants\Models\Tenant;
use Rinvex\Tenants\Contracts\TenantResolver;
use Rinvex\Tenants\Exceptions\InvalidDomainTenantException;
use Rinvex\Tenants\Exceptions\InvalidSubdomainTenantException;

class SubdomainTenantResolver implements TenantResolver
{
    /**
     * Resolve tenant using current request.
     *
     * @throws \Throwable|\Rinvex\Tenants\Exceptions\InvalidDomainTenantException|\Rinvex\Tenants\Exceptions\InvalidSubdomainTenantException
     *
     * @return \Rinvex\Tenants\Models\Tenant
     */
    public static function resolve(): Tenant
    {
        $segments = explode('.', $host = request()->getHost());

        // Check host if it matches criteria
        $isLocalhost = count($segments) === 1;
        $isCentralDomain = in_array($host, central_domains(), true);
        $isNotCentralSubdomain = ! Str::endsWith($host, central_domains());
        $isIpAddress = count(array_filter($segments, 'is_numeric')) === count($segments);

        // Throw an exception if the host is not a valid subdomain of central domains
        throw_if($isCentralDomain || $isLocalhost || $isIpAddress || $isNotCentralSubdomain, InvalidSubdomainTenantException::class, $host);

        $tenant = app('rinvex.tenants.tenant')->where('is_active', true)->where('slug', $tenantSlug = $segments[0])->first();

        // Throw an exception if tenant not found
        throw_unless($tenant, InvalidDomainTenantException::class, $tenantSlug);

        return $tenant;
    }
}
